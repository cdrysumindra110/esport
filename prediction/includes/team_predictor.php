<?php
// includes/team_predictor.php
// Team-based Predictor with OCR Integration

namespace InfiKnight\Predictor;

// require_once __DIR__ . '/ocr_processor.php';
require_once '../prediction/includes/ocr_processor.php';

use InfiKnight\OCR\OCRProcessor;

class TeamPredictor {
    
    private $ocrProcessor;
    private $matchType = 'solo';
    private $players = [];
    private $teams = [];
    private $predictionId;
    private $sessionId;
    
    // Synergy bonuses
    const SYNERGY = [
        'solo' => 1.0,
        'duo' => 1.15,
        'squad' => 1.25
    ];
    
    public function __construct($debug = false) {
        $this->ocrProcessor = new OCRProcessor($debug);
        $this->predictionId = uniqid('pred_');
        $this->sessionId = uniqid('session_');
    }
    
    /**
     * PROCESS SOLO MATCH - Each image = 1 player
     */
    public function processSoloMatch($imageFiles, $playerNames = []) {
        $this->matchType = 'solo';
        $this->players = [];
        
        $results = [
            'match_type' => 'solo',
            'total_players' => count($imageFiles),
            'processed' => 0,
            'failed' => 0,
            'players' => []
        ];
        
        foreach ($imageFiles as $index => $imageFile) {
            $playerName = $playerNames[$index] ?? "Player " . ($index + 1);
            
            // Process OCR for this player
            $ocrResult = $this->ocrProcessor->processImage($imageFile);
            
            if ($ocrResult['success']) {
                // Extract stats
                $stats = $this->ocrProcessor->extractStats($ocrResult['text']);
                $powerScore = $this->ocrProcessor->calculatePowerScore($stats);
                $validation = $this->ocrProcessor->validateStats($stats);
                
                // Store player data
                $player = [
                    'id' => uniqid('p_'),
                    'name' => $playerName,
                    'image' => basename($imageFile),
                    'stats' => $stats,
                    'power_score' => $powerScore,
                    'ocr_confidence' => $ocrResult['confidence'],
                    'validation' => $validation,
                    'extracted_fields' => $stats['extracted_fields'] ?? [],
                    'ocr_text_preview' => substr($ocrResult['text'], 0, 200)
                ];
                
                $this->players[] = $player;
                $results['players'][] = $player;
                $results['processed']++;
                
            } else {
                $results['failed']++;
                $results['players'][] = [
                    'name' => $playerName,
                    'image' => basename($imageFile),
                    'error' => $ocrResult['error'],
                    'success' => false
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * PROCESS DUO/SQUAD MATCH - Each image = 1 player, then group into teams
     */
    public function processTeamMatch($imageFiles, $playerNames = [], $matchType = 'duo') {
        $this->matchType = $matchType;
        $this->players = [];
        
        $playersPerTeam = $this->getPlayersPerTeam();
        
        // First: Process each player individually
        $processedPlayers = [];
        
        foreach ($imageFiles as $index => $imageFile) {
            $playerName = $playerNames[$index] ?? "Player " . ($index + 1);
            
            $ocrResult = $this->ocrProcessor->processImage($imageFile);
            
            if ($ocrResult['success']) {
                $stats = $this->ocrProcessor->extractStats($ocrResult['text']);
                $powerScore = $this->ocrProcessor->calculatePowerScore($stats);
                
                $processedPlayers[] = [
                    'id' => uniqid('p_'),
                    'name' => $playerName,
                    'image' => basename($imageFile),
                    'stats' => $stats,
                    'power_score' => $powerScore,
                    'ocr_confidence' => $ocrResult['confidence']
                ];
            }
        }
        
        $this->players = $processedPlayers;
        
        // Second: Auto-assign to teams
        $teams = $this->autoAssignTeams($processedPlayers, $playersPerTeam);
        $this->teams = $teams;
        
        return [
            'match_type' => $matchType,
            'players_per_team' => $playersPerTeam,
            'total_players' => count($processedPlayers),
            'total_teams' => count($teams),
            'players' => $processedPlayers,
            'teams' => $teams
        ];
    }
    
    /**
     * AUTO-ASSIGN PLAYERS TO BALANCED TEAMS
     */
    private function autoAssignTeams($players, $playersPerTeam) {
        $numTeams = ceil(count($players) / $playersPerTeam);
        $teams = [];
        
        // Sort by power score (highest first)
        usort($players, function($a, $b) {
            return $b['power_score'] <=> $a['power_score'];
        });
        
        // Snake draft for balanced teams
        for ($i = 0; $i < $numTeams; $i++) {
            $teamPlayers = [];
            
            for ($j = 0; $j < $playersPerTeam; $j++) {
                $index = ($j % 2 == 0) ? 
                    $i + ($j * $numTeams) : 
                    (($numTeams - 1 - $i) + ($j * $numTeams));
                
                if ($index < count($players)) {
                    $teamPlayers[] = $players[$index];
                }
            }
            
            if (!empty($teamPlayers)) {
                $teamName = "Team " . ($i + 1);
                $teamScore = $this->calculateTeamScore($teamPlayers);
                $synergy = $this->calculateSynergy($teamPlayers);
                
                $teams[] = [
                    'id' => uniqid('team_'),
                    'name' => $teamName,
                    'players' => $teamPlayers,
                    'power_score' => $teamScore,
                    'synergy' => $synergy,
                    'player_count' => count($teamPlayers)
                ];
            }
        }
        
        return $teams;
    }
    
    /**
     * CALCULATE TEAM POWER SCORE with synergy
     */
    public function calculateTeamScore($teamPlayers) {
        $avgScore = array_sum(array_column($teamPlayers, 'power_score')) / count($teamPlayers);
        $synergyBonus = self::SYNERGY[$this->matchType] ?? 1.0;
        
        return round($avgScore * $synergyBonus, 1);
    }
    
    /**
     * CALCULATE TEAM SYNERGY
     */
    private function calculateSynergy($players) {
        if (count($players) <= 1) {
            return 1.0;
        }
        
        $baseBonus = self::SYNERGY[$this->matchType] ?? 1.0;
        
        // Calculate score variance
        $scores = array_column($players, 'power_score');
        $avg = array_sum($scores) / count($scores);
        $variance = 0;
        
        foreach ($scores as $score) {
            $variance += pow($score - $avg, 2);
        }
        $variance = sqrt($variance / count($scores));
        
        // Optimal variance = 15 (not too similar, not too different)
        $synergyModifier = 1.0 - (abs($variance - 15) / 100);
        
        return round($baseBonus * max(0.8, min(1.2, $synergyModifier)), 2);
    }
    
    /**
     * PREDICT WINNER based on formed teams
     */
    public function predictWinner($teams = null) {
        $teams = $teams ?? $this->teams;
        
        if (count($teams) < 2) {
            throw new \Exception("Need at least 2 teams to predict winner");
        }
        
        // Calculate win probabilities
        $totalScore = array_sum(array_column($teams, 'power_score'));
        $results = [];
        
        foreach ($teams as $team) {
            $winProb = $totalScore > 0 ? round(($team['power_score'] / $totalScore) * 100, 1) : 0;
            
            $results[] = [
                'team_id' => $team['id'],
                'team_name' => $team['name'],
                'power_score' => $team['power_score'],
                'win_probability' => $winProb,
                'synergy' => $team['synergy'],
                'players' => array_map(function($p) {
                    return [
                        'name' => $p['name'],
                        'power_score' => $p['power_score'],
                        'kd' => $p['stats']['kd_ratio'] ?? 'N/A',
                        'damage' => $p['stats']['avg_damage'] ?? 'N/A'
                    ];
                }, $team['players'])
            ];
        }
        
        // Sort by power score
        usort($results, function($a, $b) {
            return $b['power_score'] <=> $a['power_score'];
        });
        
        $winner = $results[0];
        $confidence = $this->calculateConfidence($results);
        
        return [
            'prediction_id' => $this->predictionId,
            'timestamp' => date('Y-m-d H:i:s'),
            'match_type' => $this->matchType,
            'total_teams' => count($results),
            'winner' => $winner,
            'all_teams' => $results,
            'confidence' => $confidence
        ];
    }
    
    /**
     * Calculate prediction confidence
     */
    private function calculateConfidence($results) {
        if (count($results) < 2) {
            return 100;
        }
        
        $scores = array_column($results, 'power_score');
        $gap = $scores[0] - $scores[1];
        $maxScore = max($scores);
        
        // Confidence formula: 70% base + gap contribution
        $confidence = 70 + ($gap / $maxScore) * 30;
        
        return round(min(99, $confidence), 1);
    }
    
    /**
     * Get players per team based on match type
     */
    public function getPlayersPerTeam() {
        return [
            'solo' => 1,
            'duo' => 2,
            'squad' => 4
        ][$this->matchType] ?? 1;
    }
    
    /**
     * Set match type
     */
    public function setMatchType($type) {
        $validTypes = ['solo', 'duo', 'squad'];
        if (in_array($type, $validTypes)) {
            $this->matchType = $type;
        }
        return $this;
    }
    
    /**
     * Get all players
     */
    public function getPlayers() {
        return $this->players;
    }
    
    /**
     * Get all teams
     */
    public function getTeams() {
        return $this->teams;
    }
    
    /**
     * Reset predictor
     */
    public function reset() {
        $this->players = [];
        $this->teams = [];
        $this->predictionId = uniqid('pred_');
        $this->sessionId = uniqid('session_');
    }
}
?>
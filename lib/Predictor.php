<?php

class InfiKnightPredictor {
    private const WEIGHTS = [
        'kd' => 0.35,
        'win_ratio' => 0.25,
        'top10_rate' => 0.15,
        'avg_damage' => 0.15,
        'headshot_rate' => 0.05,
        'accuracy' => 0.05
    ];

    private const BENCHMARKS = [
        'kd' => ['min' => 0.5, 'max' => 10.0],
        'win_ratio' => ['min' => 0.01, 'max' => 0.50],
        'top10_rate' => ['min' => 0.05, 'max' => 0.80],
        'avg_damage' => ['min' => 100, 'max' => 1000],
        'headshot_rate' => ['min' => 0.10, 'max' => 0.70],
        'accuracy' => ['min' => 0.10, 'max' => 0.50]
    ];

    private const SYNERGY = [
        'solo' => 1.0,
        'duo' => 1.15,
        'squad' => 1.25
    ];

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Extract player stats from OCR text
     * @param string $ocrText Raw OCR text output
     * @return array Extracted stats or empty array
     */
    public function extractStats($ocrText) {
        $stats = [];
        
        // K/D ratio
        if (preg_match('/K\/D\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['kd'] = floatval($matches[1]);
        }
        
        // Win rate/ratio
        if (preg_match('/Win\s*Rate\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['win_ratio'] = floatval($matches[1]) / 100;
        } elseif (preg_match('/Win\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['win_ratio'] = floatval($matches[1]);
        }
        
        // Top 10 rate
        if (preg_match('/Top\s*10\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['top10_rate'] = floatval($matches[1]) / 100;
        }
        
        // Average damage
        if (preg_match('/Avg[.\s]*Damage\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['avg_damage'] = floatval($matches[1]);
        } elseif (preg_match('/Damage\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['avg_damage'] = floatval($matches[1]);
        }
        
        // Headshot rate
        if (preg_match('/Headshot\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['headshot_rate'] = floatval($matches[1]) / 100;
        }
        
        // Accuracy
        if (preg_match('/Accuracy\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['accuracy'] = floatval($matches[1]) / 100;
        }
        
        return $stats;
    }

    /**
     * Normalize a stat value to 0-1 scale using benchmarks
     * @param float $value Raw stat value
     * @param string $statKey Stat key (kd, win_ratio, etc.)
     * @return float Normalized value between 0-1
     */
    private function normalizeValue($value, $statKey) {
        if (!isset(self::BENCHMARKS[$statKey])) {
            return 0;
        }
        
        $min = self::BENCHMARKS[$statKey]['min'];
        $max = self::BENCHMARKS[$statKey]['max'];
        
        // Clamp value between min and max
        $clamped = max($min, min($max, $value));
        
        // Normalize to 0-1
        return ($clamped - $min) / ($max - $min);
    }

    /**
     * Calculate player score based on stats
     * @param array $stats Player stats
     * @return float Player score (0-100)
     */
    public function calculatePlayerScore($stats) {
        $totalScore = 0;
        $totalWeight = 0;
        
        foreach (self::WEIGHTS as $key => $weight) {
            if (isset($stats[$key])) {
                $normalized = $this->normalizeValue($stats[$key], $key);
                $totalScore += $normalized * $weight;
                $totalWeight += $weight;
            }
        }
        
        // Return score out of 100
        return $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;
    }

    /**
     * Calculate team score with synergy
     * @param array $team Array of player stats
     * @param string $matchType Match type (solo, duo, squad)
     * @return float Team score
     */
    public function calculateTeamScore($team, $matchType) {
        $teamScore = 0;
        $playerCount = count($team);
        
        foreach ($team as $player) {
            $teamScore += $this->calculatePlayerScore($player);
        }
        
        // Divide by player count for average
        if ($playerCount > 0) {
            $teamScore /= $playerCount;
        }
        
        // Apply synergy bonus
        $synergy = self::SYNERGY[$matchType] ?? 1.0;
        $teamScore *= $synergy;
        
        return $teamScore;
    }

    /**
     * Predict winner from teams
     * @param array $teams Array of teams
     * @param string $matchType Match type (solo, duo, squad)
     * @return array Prediction result
     */
    public function predictWinner($teams, $matchType = 'solo') {
        $teamScores = [];
        $winnerIndex = 0;
        $maxScore = 0;
        
        foreach ($teams as $index => $team) {
            $score = $this->calculateTeamScore($team, $matchType);
            $teamScores[$index] = $score;
            
            if ($score > $maxScore) {
                $maxScore = $score;
                $winnerIndex = $index;
            }
        }
        
        // Calculate confidence based on score difference
        $scores = array_values($teamScores);
        sort($scores, SORT_NUMERIC);
        $secondHighest = count($scores) > 1 ? $scores[count($scores) - 2] : 0;
        
        $scoreDiff = $maxScore - $secondHighest;
        $confidence = min(100, 50 + ($scoreDiff * 2)); // 50-100% range
        
        return [
            'winner' => $winnerIndex,
            'teams' => $teamScores,
            'confidence' => round($confidence, 2),
            'match_type' => $matchType
        ];
    }

    /**
     * Backward compatibility: predict solo winner
     * @param array $player1 Player 1 stats
     * @param array $player2 Player 2 stats
     * @return array Prediction result
     */
    public function predictSoloWinner($player1, $player2) {
        return $this->predictWinner([[$player1], [$player2]], 'solo');
    }

    /**
     * Backward compatibility: predict team winner
     * @param array $team1 Team 1 players
     * @param array $team2 Team 2 players
     * @param string $matchType Match type
     * @return array Prediction result
     */
    public function predictTeamWinner($team1, $team2, $matchType = 'duo') {
        return $this->predictWinner([$team1, $team2], $matchType);
    }
}

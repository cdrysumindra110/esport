<?php
// lib/Predictor.php - Complete AI Prediction Algorithm
class InfiknightPredictor {
    private $db;
    private $mlEnabled;
    
    public function __construct($database) {
        $this->db = $database;
        $this->mlEnabled = ML_ENHANCED;
    }
    
    // Complete Player Rating Calculation with Assist Score
    public function calculatePlayerRating($player) {
        // Validate player data
        $player = $this->validatePlayerData($player);
        
        // Calculate individual scores with caps
        $killScore = min(10, $player['kills'] * 0.7);
        $damageScore = min(10, $player['damage'] / 80);
        $survivalScore = min(10, $player['survival'] / 120);
        $headshotScore = min(5, ($player['headshots'] ?? 0) * 0.6);
        $assistScore = min(5, ($player['assists'] ?? 0) * 0.5); // New assist score
        
        // Base rating calculation
        $baseRating = (
            $killScore * 0.35 + 
            $damageScore * 0.25 + 
            $survivalScore * 0.20 + 
            $headshotScore * 0.10 +
            $assistScore * 0.10  // 10% weight for assists
        );
        
        // Apply ML enhancement if enabled
        if ($this->mlEnabled) {
            $mlScore = $this->applyLogisticRegression($player, $baseRating);
            $historicalAvg = $this->getHistoricalAverage($player['name']);
            $recentTrend = $this->getRecentTrend($player['name']);
            
            // Ensemble prediction
            $finalRating = (
                $baseRating * 0.6 + 
                $mlScore * 0.3 + 
                $recentTrend * 0.1
            );
            
            return round(max(0, min(10, $finalRating)), 2);
        }
        
        return round(max(0, min(10, $baseRating)), 2);
    }
    
    // Logistic Regression Implementation
    private function applyLogisticRegression($player, $baseRating) {
        // Coefficients (would be trained from historical data)
        $beta0 = -1.5;
        $beta1 = 0.8;
        $beta2 = 0.3;
        
        // Calculate team score factor
        $teamScore = $this->calculateTeamScoreFactor($player);
        
        // Calculate performance metrics
        $performanceScore = (
            ($player['kills'] / 15) * 0.4 +
            ($player['damage'] / 1000) * 0.3 +
            ($player['survival'] / 1200) * 0.3
        );
        
        // Logistic function: P(win) = 1 / (1 + e^(-β0 - β1×TS - β2×PS))
        $probability = 1 / (1 + exp(
            -$beta0 - 
            ($beta1 * $teamScore) - 
            ($beta2 * $performanceScore)
        ));
        
        // Convert probability to 0-10 scale
        return $probability * 10;
    }
    
    private function calculateTeamScoreFactor($player) {
        // This would normally come from team context
        // For now, calculate based on individual performance
        $killFactor = min(1, $player['kills'] / 15);
        $survivalFactor = min(1, $player['survival'] / 1200);
        $damageFactor = min(1, $player['damage'] / 1000);
        
        return ($killFactor * 0.4) + ($survivalFactor * 0.4) + ($damageFactor * 0.2);
    }
    
    // Complete Team Calculations
    public function calculateTeamRating($team) {
        if (!isset($team['players']) || !is_array($team['players'])) {
            return 0;
        }
        
        $totalRating = 0;
        $playerRatings = [];
        
        // Calculate individual player ratings
        foreach ($team['players'] as $player) {
            $rating = $this->calculatePlayerRating($player);
            $totalRating += $rating;
            $playerRatings[] = $rating;
        }
        
        $avgRating = $totalRating / count($team['players']);
        
        // Calculate synergy bonus
        $synergyBonus = $this->calculateSynergyBonus($team);
        
        // Final team score
        $teamScore = ($avgRating * 0.7) + ($synergyBonus * 0.3);
        
        return round(max(0, min(10, $teamScore)), 2);
    }
    
    // Synergy Bonus Calculation
    public function calculateSynergyBonus($team) {
        if (count($team['players']) < 2) {
            return 5; // Default for solo/insufficient players
        }
        
        $kills = array_column($team['players'], 'kills');
        $damages = array_column($team['players'], 'damage');
        
        $killStdDev = $this->calculateStandardDeviation($kills);
        $damageStdDev = $this->calculateStandardDeviation($damages);
        
        // SynergyBonus = 10 - [(σ_kills × 1.5) + (σ_damage ÷ 150)]
        $synergy = 10 - (($killStdDev * 1.5) + ($damageStdDev / 150));
        
        return max(0, min(10, round($synergy, 2)));
    }
    
    private function calculateStandardDeviation($array) {
        $n = count($array);
        if ($n < 2) return 0;
        
        $mean = array_sum($array) / $n;
        $carry = 0.0;
        
        foreach ($array as $val) {
            $d = ((double) $val) - $mean;
            $carry += $d * $d;
        }
        
        return sqrt($carry / $n);
    }
    
    // Historical Data Methods
    private function getHistoricalAverage($playerName) {
        try {
            $result = $this->db->getPlayerStats($playerName);
            return $result['avg_rating'] ?? 5.0;
        } catch (Exception $e) {
            error_log("Failed to get historical data: " . $e->getMessage());
            return 5.0; // Default average
        }
    }
    
    private function getRecentTrend($playerName) {
        try {
            $result = $this->db->getRecentPerformance($playerName);
            return $result['trend'] ?? 5.0;
        } catch (Exception $e) {
            return 5.0;
        }
    }
    
    // Prediction Methods
    public function predictSoloWinner($players) {
        $ratedPlayers = [];
        
        foreach ($players as $player) {
            $player['rating'] = $this->calculatePlayerRating($player);
            $ratedPlayers[] = $player;
        }
        
        // Sort by rating
        usort($ratedPlayers, function($a, $b) {
            return $b['rating'] <=> $a['rating'];
        });
        
        $winner = $ratedPlayers[0] ?? null;
        $confidence = $this->calculateConfidence($ratedPlayers);
        
        return [
            'winner' => $winner,
            'confidence' => $confidence,
            'topPerformer' => $ratedPlayers[0] ?? null,
            'weakLink' => end($ratedPlayers) ?: null,
            'allPlayers' => $ratedPlayers,
            'insights' => $this->generateInsights($ratedPlayers),
            'predictionType' => 'solo',
            'algorithmVersion' => ALGORITHM_VERSION,
            'mlEnhanced' => $this->mlEnabled
        ];
    }
    
    public function predictTeamWinner($teams, $matchType) {
        $ratedTeams = [];
        
        foreach ($teams as $team) {
            $team['teamScore'] = $this->calculateTeamRating($team);
            $team['synergyBonus'] = $this->calculateSynergyBonus($team);
            $team['totalKills'] = array_sum(array_column($team['players'], 'kills'));
            $team['totalDamage'] = array_sum(array_column($team['players'], 'damage'));
            $team['avgSurvival'] = array_sum(array_column($team['players'], 'survival')) / count($team['players']);
            
            $ratedTeams[] = $team;
        }
        
        // Sort by team score
        usort($ratedTeams, function($a, $b) {
            return $b['teamScore'] <=> $a['teamScore'];
        });
        
        $winningTeam = $ratedTeams[0] ?? null;
        $confidence = $this->calculateTeamConfidence($ratedTeams);
        
        return [
            'winningTeam' => $winningTeam,
            'confidence' => $confidence,
            'mvp' => $this->findMVP($winningTeam),
            'teamWeakLink' => $this->findTeamWeakLink($winningTeam),
            'allTeams' => $ratedTeams,
            'insights' => $this->generateTeamInsights($ratedTeams, $matchType),
            'predictionType' => $matchType,
            'algorithmVersion' => ALGORITHM_VERSION,
            'mlEnhanced' => $this->mlEnabled
        ];
    }
    
    // Utility Methods
    private function validatePlayerData($player) {
        $defaults = [
            'kills' => 0,
            'damage' => 0,
            'survival' => 0,
            'headshots' => 0,
            'assists' => 0,
            'name' => 'Unknown'
        ];
        
        return array_merge($defaults, $player);
    }
    
    private function calculateConfidence($players) {
        if (count($players) < 2) return 100;
        
        $topRating = $players[0]['rating'];
        $secondRating = $players[1]['rating'] ?? 0;
        $gap = $topRating - $secondRating;
        
        $confidence = 60 + ($gap * 20);
        return min(95, max(50, round($confidence, 1)));
    }
    
    private function calculateTeamConfidence($teams) {
        if (count($teams) < 2) return 100;
        
        $topScore = $teams[0]['teamScore'];
        $secondScore = $teams[1]['teamScore'] ?? 0;
        $gap = $topScore - $secondScore;
        
        $confidence = 60 + ($gap * 15);
        return min(95, max(50, round($confidence, 1)));
    }
    
    private function findMVP($team) {
        if (!isset($team['players'])) return null;
        
        $mvp = $team['players'][0];
        foreach ($team['players'] as $player) {
            $playerRating = $this->calculatePlayerRating($player);
            if ($playerRating > $this->calculatePlayerRating($mvp)) {
                $mvp = $player;
            }
        }
        
        $mvp['rating'] = $this->calculatePlayerRating($mvp);
        return $mvp;
    }
    
    private function findTeamWeakLink($team) {
        if (!isset($team['players'])) return null;
        
        $weakLink = $team['players'][0];
        foreach ($team['players'] as $player) {
            $playerRating = $this->calculatePlayerRating($player);
            if ($playerRating < $this->calculatePlayerRating($weakLink)) {
                $weakLink = $player;
            }
        }
        
        $weakLink['rating'] = $this->calculatePlayerRating($weakLink);
        return $weakLink;
    }
    
    private function generateInsights($players) {
        $insights = [];
        
        if (count($players) > 0) {
            $winner = $players[0];
            $avgKills = array_sum(array_column($players, 'kills')) / count($players);
            $avgDamage = array_sum(array_column($players, 'damage')) / count($players);
            
            $insights[] = "Top performer has " . round($winner['rating'], 1) . "/10 rating";
            $insights[] = "Average kills: " . round($avgKills, 1);
            $insights[] = "Average damage: " . round($avgDamage);
            
            if ($winner['kills'] > $avgKills * 1.5) {
                $insights[] = "Winner excels in aggressive playstyle";
            }
            
            if ($winner['survival'] > 600) {
                $insights[] = "Winner shows excellent survival skills";
            }
        }
        
        return array_slice($insights, 0, 3);
    }
    
    private function generateTeamInsights($teams, $matchType) {
        $insights = [];
        
        if (count($teams) > 0) {
            $winner = $teams[0];
            $synergy = $winner['synergyBonus'] ?? 0;
            
            $insights[] = "Team synergy score: " . round($synergy, 1) . "/10";
            
            if ($synergy > 7) {
                $insights[] = "Excellent team coordination";
            } elseif ($synergy > 4) {
                $insights[] = "Good team balance";
            } else {
                $insights[] = "Team coordination needs improvement";
            }
            
            $killSpread = $this->calculateKillSpread($winner);
            if ($killSpread > 0.7) {
                $insights[] = "Balanced kill distribution";
            } else {
                $insights[] = "Relies heavily on top fragger";
            }
        }
        
        return array_slice($insights, 0, 3);
    }
    
    private function calculateKillSpread($team) {
        if (!isset($team['players'])) return 0;
        
        $kills = array_column($team['players'], 'kills');
        if (count($kills) < 2) return 1;
        
        $maxKills = max($kills);
        $totalKills = array_sum($kills);
        
        return $totalKills > 0 ? ($maxKills / $totalKills) : 0;
    }
}
?>
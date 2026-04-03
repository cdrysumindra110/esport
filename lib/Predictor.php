<?php

class InfiKnightPredictor {
    private const WEIGHTS = [
        'kd' => 0.35,
        'win_ratio' => 0.25,
        'top10_rate' => 0.15,
        'avg_damage' => 0.12,
        'headshot_rate' => 0.08,
        'accuracy' => 0.05
    ];

    private const BENCHMARKS = [
        'kd' => ['min' => 0.5, 'max' => 3.5],
        'win_ratio' => ['min' => 2.0, 'max' => 25.0],
        'top10_rate' => ['min' => 30.0, 'max' => 80.0],
        'avg_damage' => ['min' => 150.0, 'max' => 450.0],
        'headshot_rate' => ['min' => 5.0, 'max' => 30.0],
        'accuracy' => ['min' => 5.0, 'max' => 25.0]
    ];

    private const SYNERGY = [
        'solo' => 1.0,
        'duo' => 1.15,
        'squad' => 1.25
    ];

    private const TEAM_SIZE_FACTOR = [
        'solo' => 1,
        'duo' => 2,
        'squad' => 4
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
            $stats['win_ratio'] = floatval($matches[1]);
        } elseif (preg_match('/Win\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['win_ratio'] = floatval($matches[1]);
        }
        
        // Top 10 rate
        if (preg_match('/Top\s*10\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['top10_rate'] = floatval($matches[1]);
        }
        
        // Average damage
        if (preg_match('/Avg[.\s]*Damage\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['avg_damage'] = floatval($matches[1]);
        } elseif (preg_match('/Damage\s*[:\-]?\s*([\d.]+)/i', $ocrText, $matches)) {
            $stats['avg_damage'] = floatval($matches[1]);
        }
        
        // Headshot rate
        if (preg_match('/Headshot\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['headshot_rate'] = floatval($matches[1]);
        }
        
        // Accuracy
        if (preg_match('/Accuracy\s*[:\-]?\s*([\d.]+)%?/i', $ocrText, $matches)) {
            $stats['accuracy'] = floatval($matches[1]);
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
     * Convert legacy ratio inputs (0-1) into percentage inputs (0-100)
     * for rate-based fields used by the reference algorithm.
     */
    private function normalizeInputStats(array $stats): array {
        foreach (['win_ratio', 'top10_rate', 'headshot_rate', 'accuracy'] as $rateKey) {
            if (isset($stats[$rateKey])) {
                $val = floatval($stats[$rateKey]);
                if ($val > 0 && $val <= 1) {
                    $stats[$rateKey] = $val * 100;
                }
            }
        }

        return $stats;
    }

    /**
     * Optional logistic refinement (Step 7 in reference docs).
     * p = 1 / (1 + exp(-(beta0 + beta1 * teamScore)))
     */
    private function calculateLogisticProbabilities(array $teamScores, float $beta0 = -4.0, float $beta1 = 0.08): array {
        $raw = [];
        $sum = 0.0;

        foreach ($teamScores as $index => $score) {
            $p = 1 / (1 + exp(-($beta0 + ($beta1 * $score))));
            $raw[$index] = $p;
            $sum += $p;
        }

        $normalized = [];
        if ($sum > 0) {
            foreach ($raw as $index => $p) {
                $normalized[$index] = round(($p / $sum) * 100, 2);
            }
        }

        return $normalized;
    }

    /**
     * Calculate player score based on stats
     * @param array $stats Player stats
     * @return float Player score (0-100)
     */
    public function calculatePlayerScore($stats) {
        $stats = $this->normalizeInputStats($stats);

        $totalScore = 0;
        $totalWeight = 0;
        
        foreach (self::WEIGHTS as $key => $weight) {
            if (isset($stats[$key])) {
                $normalized = $this->normalizeValue($stats[$key], $key);
                $totalScore += $normalized * $weight;
                $totalWeight += $weight;
            }
        }
        
        $basePlayerScore = $totalWeight > 0 ? ($totalScore / $totalWeight) * 100 : 0;

        // Optional ML blend: FinalScore = 0.6*PlayerScore + 0.3*HistoricalAvg + 0.1*RecentTrend
        $historicalAvg = isset($stats['historical_avg']) ? floatval($stats['historical_avg']) : $basePlayerScore;
        $recentTrend = isset($stats['recent_trend']) ? floatval($stats['recent_trend']) : $basePlayerScore;

        $finalScore = (0.6 * $basePlayerScore) + (0.3 * $historicalAvg) + (0.1 * $recentTrend);

        return round(max(0.0, min(100.0, $finalScore)), 2);
    }

    /**
     * Calculate team score with synergy
     * @param array $team Array of player stats
     * @param string $matchType Match type (solo, duo, squad)
     * @return float Team score
     */
    public function calculateTeamScore($team, $matchType) {
        $rawTeamScore = 0.0;
        
        foreach ($team as $player) {
            $rawTeamScore += $this->calculatePlayerScore($player);
        }

        $synergy = self::SYNERGY[$matchType] ?? 1.0;
        $sizeFactor = self::TEAM_SIZE_FACTOR[$matchType] ?? 1;

        // Reference formula:
        // AdjustedTeamScore = (sum(PlayerScores) * SynergyBonus) / TeamSizeFactor
        $teamScore = ($rawTeamScore * $synergy) / max(1, $sizeFactor);
        
        return round($teamScore, 2);
    }

    /**
     * Predict winner from teams
     * @param array $teams Array of teams
     * @param string $matchType Match type (solo, duo, squad)
     * @return array Prediction result
     */
    public function predictWinner($teams, $matchType = 'solo') {
        $teamScores = [];
        $winProbabilities = [];
        $winnerIndex = 0;
        $maxScore = 0.0;
        
        foreach ($teams as $index => $team) {
            $score = $this->calculateTeamScore($team, $matchType);
            $teamScores[$index] = $score;
            
            if ($score > $maxScore) {
                $maxScore = $score;
                $winnerIndex = $index;
            }
        }

        $totalScore = array_sum($teamScores);
        if ($totalScore > 0) {
            foreach ($teamScores as $index => $score) {
                $winProbabilities[$index] = round(($score / $totalScore) * 100, 2);
            }
        } else {
            $equal = count($teamScores) > 0 ? round(100 / count($teamScores), 2) : 0;
            foreach ($teamScores as $index => $score) {
                $winProbabilities[$index] = $equal;
            }
        }

        $logisticWinProbabilities = $this->calculateLogisticProbabilities($teamScores);
        
        // Confidence = min(99, 70 + ((TopScore - SecondScore) / max(1, TopScore)) * 30)
        $scores = array_values($teamScores);
        sort($scores, SORT_NUMERIC);
        $secondHighest = count($scores) > 1 ? $scores[count($scores) - 2] : 0;

        $confidence = min(99, 70 + (($maxScore - $secondHighest) / max(1, $maxScore)) * 30);
        
        return [
            'winner' => $winnerIndex,
            'teams' => $teamScores,
            'win_probabilities' => $winProbabilities,
            'logistic_win_probabilities' => $logisticWinProbabilities,
            'top_score' => round($maxScore, 2),
            'second_score' => round($secondHighest, 2),
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

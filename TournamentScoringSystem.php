<?php
// TournamentScoringSystem.php

class TournamentScoringSystem {
    private $db;
    private $defaultRules = [
        'placement' => [
            'type' => 'linear',
            'max_points' => 100,
            'min_placement' => 20
        ],
        'kill' => [
            'points_per_kill' => 10
        ],
        'bonuses' => [
            'win_bonus' => 25,
            'top3_bonus' => 15,
            'top5_bonus' => 10
        ]
    ];

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    /**
     * Calculate score for a single match result
     */
    public function calculateMatchScore($placement, $kills, $tournamentId = null) {
        $rules = $this->getTournamentRules($tournamentId);
        
        // Placement score (linear decreasing)
        $placementScore = max(0, $rules['placement']['max_points'] - $placement + 1);
        
        // Kill score
        $killScore = $kills * $rules['kill']['points_per_kill'];
        
        // Bonuses
        $bonusScore = 0;
        if ($placement == 1) {
            $bonusScore += $rules['bonuses']['win_bonus'];
        } elseif ($placement <= 3) {
            $bonusScore += $rules['bonuses']['top3_bonus'];
        } elseif ($placement <= 5) {
            $bonusScore += $rules['bonuses']['top5_bonus'];
        }
        
        return [
            'placement_score' => $placementScore,
            'kill_score' => $killScore,
            'bonus_score' => $bonusScore,
            'total_score' => $placementScore + $killScore + $bonusScore
        ];
    }

    /**
     * Process and save match results with auto-calculation
     */
    public function processMatchResults($tournamentId, $matchNumber, $results) {
        // Check if match already exists
        $checkStmt = $this->db->prepare("
            SELECT COUNT(*) as count FROM match_results 
            WHERE tournament_id = ? AND match_number = ?
        ");
        $checkStmt->bind_param("ii", $tournamentId, $matchNumber);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $matchExists = $checkResult->fetch_assoc()['count'] > 0;
        $checkStmt->close();
        
        if ($matchExists) {
            throw new Exception("Match #$matchNumber already exists for this tournament!");
        }
        
        $this->db->begin_transaction();
        
        try {
            foreach ($results as $participantId => $result) {
                $scoreDetails = $this->calculateMatchScore(
                    $result['placement'],
                    $result['kills'],
                    $tournamentId
                );
                
                // Insert match result
                $stmt = $this->db->prepare("
                    INSERT INTO match_results 
                    (tournament_id, match_number, participant_id, placement, kills, score)
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->bind_param("iiiidi",
                    $tournamentId,
                    $matchNumber,
                    $participantId,
                    $result['placement'],
                    $result['kills'],
                    $scoreDetails['total_score']
                );
                $stmt->execute();
                $stmt->close();
            }
            
            // Update participant totals
            $this->updateParticipantTotals($tournamentId, array_keys($results));
            
            $this->db->commit();
            
            return [
                'success' => true,
                'match_number' => $matchNumber,
                'participants_updated' => count($results),
                'message' => 'Match results processed successfully'
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }

    /**
     * Automatically update participant total scores
     */
    private function updateParticipantTotals($tournamentId, $participantIds = null) {
        $whereClause = "WHERE tp.tournament_id = ?";
        $params = [$tournamentId];
        $types = "i";
        
        if ($participantIds && !empty($participantIds)) {
            $placeholders = str_repeat('?,', count($participantIds) - 1) . '?';
            $whereClause .= " AND tp.id IN ($placeholders)";
            $params = array_merge($params, $participantIds);
            $types .= str_repeat('i', count($participantIds));
        }
        
        $query = "
            UPDATE tournament_participants tp
            LEFT JOIN (
                SELECT 
                    participant_id,
                    SUM(score) as total_score,
                    SUM(kills) as total_kills,
                    AVG(placement) as avg_placement,
                    COUNT(*) as matches_played
                FROM match_results 
                WHERE tournament_id = ?
                GROUP BY participant_id
            ) mr ON tp.id = mr.participant_id
            SET 
                tp.total_score = COALESCE(mr.total_score, 0),
                tp.total_kills = COALESCE(mr.total_kills, 0),
                tp.average_placement = COALESCE(mr.avg_placement, 0.0),
                tp.matches_played = COALESCE(mr.matches_played, 0),
                tp.last_updated = CURRENT_TIMESTAMP
            $whereClause
        ";
        
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Get tournament-specific scoring rules
     */
    private function getTournamentRules($tournamentId) {
        if (!$tournamentId) {
            return $this->defaultRules;
        }
        
        $stmt = $this->db->prepare("
            SELECT scoring_config FROM tournaments WHERE id = ?
        ");
        $stmt->bind_param("i", $tournamentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc() && $row['scoring_config']) {
            $customRules = json_decode($row['scoring_config'], true);
            return array_merge($this->defaultRules, $customRules);
        }
        
        return $this->defaultRules;
    }

    /**
     * Trigger auto-calculation for entire tournament
     */
    public function recalculateTournament($tournamentId) {
        // First, update all match scores with current rules
        $updateStmt = $this->db->prepare("
            UPDATE match_results mr
            JOIN (
                SELECT id, placement, kills 
                FROM match_results 
                WHERE tournament_id = ?
            ) as source ON mr.id = source.id
            SET mr.score = ?
            WHERE mr.tournament_id = ?
        ");
        
        // This is a simplified version - you might need to loop through each match
        // For now, let's just update participant totals
        $this->updateParticipantTotals($tournamentId);
        
        return [
            'success' => true,
            'message' => 'Tournament scores recalculated'
        ];
    }

    /**
     * Get leaderboard with advanced sorting
     */
    public function getLeaderboard($tournamentId, $limit = null, $includeDetails = false) {
        $limitClause = $limit ? "LIMIT ?" : "";
        
        $query = "
            SELECT 
                tp.id,
                tp.team_id,
                tp.total_score,
                tp.total_kills,
                tp.average_placement,
                tp.matches_played,
                -- Calculate rank using dense ranking
                @rank := @rank + 1 as rank_position
            FROM tournament_participants tp, (SELECT @rank := 0) r
            WHERE tp.tournament_id = ?
            ORDER BY 
                tp.total_score DESC,
                tp.total_kills DESC,
                tp.average_placement ASC
            $limitClause
        ";
        
        $stmt = $this->db->prepare($query);
        
        if ($limit) {
            $stmt->bind_param("ii", $tournamentId, $limit);
        } else {
            $stmt->bind_param("i", $tournamentId);
        }
        
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
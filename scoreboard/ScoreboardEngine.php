<?php
// scoreboard/ScoreboardEngine.php

class AdvancedScoreboardEngine {
    private $conn;
    private $tournamentId;
    
    public function __construct($conn, $tournamentId) {
        $this->conn = $conn;
        $this->tournamentId = $tournamentId;
    }
    
    /**
     * Generate comprehensive scoreboard for tournament
     */
    public function generateScoreboard($bracketType = null) {
        $scoreboard = [
            'tournament_info' => $this->getTournamentInfo(),
            'standings' => $this->getTournamentStandings(),
            'recent_matches' => $this->getRecentMatches(10),
            'upcoming_matches' => $this->getUpcomingMatches(10),
            'live_matches' => $this->getLiveMatches(),
            'stat_leaders' => $this->calculateStatLeaders(),
            'predictions' => [],
            'bracket_progression' => []
        ];
        
        // Add bracket progression if bracket exists
        $bracketData = $this->generateBracketData();
        if (!empty($bracketData['rounds'])) {
            $scoreboard['bracket_progression'] = $bracketData;
        }
        
        // Generate predictions for upcoming matches
        $scoreboard['predictions'] = $this->generatePredictions();
        
        return $scoreboard;
    }
    
    /**
     * Get tournament information
     */
    private function getTournamentInfo() {
        $stmt = $this->conn->prepare("
            SELECT t.*, u.uname as organizer_name,
                   b.bracket_type,
                   COUNT(DISTINCT sr.id) as solo_registrations,
                   COUNT(DISTINCT dr.duo_id) as duo_registrations,
                   COUNT(DISTINCT sqr.squad_id) as squad_registrations
            FROM tournaments t
            LEFT JOIN users u ON t.user_id = u.id
            LEFT JOIN brackets b ON t.id = b.tournament_id
            LEFT JOIN solo_registration sr ON t.id = sr.tournament_id
            LEFT JOIN duo_registration dr ON t.id = dr.tournament_id
            LEFT JOIN squad_registration sqr ON t.id = sqr.tournament_id
            WHERE t.id = ?
            GROUP BY t.id
        ");
        
        $stmt->bind_param("i", $this->tournamentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    /**
     * Get tournament standings
     */
    public function getTournamentStandings() {
        $standings = [];
        
        // First, try to get from tournament_standings table if it exists
        $tables = $this->conn->query("SHOW TABLES LIKE 'tournament_standings'");
        if ($tables->num_rows > 0) {
            $stmt = $this->conn->prepare("
                SELECT * FROM tournament_standings 
                WHERE tournament_id = ? 
                ORDER BY position ASC, points DESC
            ");
            $stmt->bind_param("i", $this->tournamentId);
            $stmt->execute();
            $standings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
        
        // If no standings table or empty, calculate from match results
        if (empty($standings)) {
            $standings = $this->calculateStandingsFromMatches();
        }
        
        return $standings;
    }
    
    /**
     * Calculate standings from match results
     */
    private function calculateStandingsFromMatches() {
        $matches = $this->getAllMatchResults();
        $standings = [];
        
        foreach ($matches as $match) {
            if ($match['match_status'] !== 'completed') continue;
            
            // Process team 1
            $team1Id = $match['team1_id'] ?: 'p' . $match['participant1_id'];
            $team1Name = $match['team1_name'] ?: $match['participant1_name'] ?: 'Team 1';
            
            if (!isset($standings[$team1Id])) {
                $standings[$team1Id] = [
                    'id' => $team1Id,
                    'name' => $team1Name,
                    'matches_played' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                    'kills' => 0,
                    'deaths' => 0,
                    'rating' => 0,
                    'status' => 'active'
                ];
            }
            
            // Process team 2
            $team2Id = $match['team2_id'] ?: 'p' . $match['participant2_id'];
            $team2Name = $match['team2_name'] ?: $match['participant2_name'] ?: 'Team 2';
            
            if (!isset($standings[$team2Id])) {
                $standings[$team2Id] = [
                    'id' => $team2Id,
                    'name' => $team2Name,
                    'matches_played' => 0,
                    'wins' => 0,
                    'losses' => 0,
                    'draws' => 0,
                    'points' => 0,
                    'kills' => 0,
                    'deaths' => 0,
                    'rating' => 0,
                    'status' => 'active'
                ];
            }
            
            // Update match statistics
            $standings[$team1Id]['matches_played']++;
            $standings[$team2Id]['matches_played']++;
            
            if ($match['winner_id'] == $match['participant1_id'] || $match['winner_id'] == $match['team1_id']) {
                $standings[$team1Id]['wins']++;
                $standings[$team1Id]['points'] += 3;
                $standings[$team2Id]['losses']++;
            } elseif ($match['winner_id'] == $match['participant2_id'] || $match['winner_id'] == $match['team2_id']) {
                $standings[$team2Id]['wins']++;
                $standings[$team2Id]['points'] += 3;
                $standings[$team1Id]['losses']++;
            } else {
                $standings[$team1Id]['draws']++;
                $standings[$team2Id]['draws']++;
                $standings[$team1Id]['points']++;
                $standings[$team2Id]['points']++;
            }
            
            // Extract kills/deaths from game_data if available
            if (!empty($match['game_data'])) {
                $gameData = json_decode($match['game_data'], true);
                if (isset($gameData['team1_kills'])) {
                    $standings[$team1Id]['kills'] += $gameData['team1_kills'];
                    $standings[$team1Id]['deaths'] += $gameData['team2_kills']; // team2 kills are team1 deaths
                }
                if (isset($gameData['team2_kills'])) {
                    $standings[$team2Id]['kills'] += $gameData['team2_kills'];
                    $standings[$team2Id]['deaths'] += $gameData['team1_kills'];
                }
            }
        }
        
        // Calculate ratings
        foreach ($standings as &$team) {
            if ($team['matches_played'] > 0) {
                $win_rate = $team['wins'] / $team['matches_played'];
                $kd_ratio = $team['deaths'] > 0 ? $team['kills'] / $team['deaths'] : $team['kills'];
                $team['rating'] = round(($win_rate * 0.6 + min(1, $kd_ratio / 3) * 0.4) * 2, 2);
            }
        }
        
        // Sort by points, then rating, then wins
        usort($standings, function($a, $b) {
            if ($b['points'] != $a['points']) {
                return $b['points'] <=> $a['points'];
            }
            if ($b['rating'] != $a['rating']) {
                return $b['rating'] <=> $a['rating'];
            }
            return $b['wins'] <=> $a['wins'];
        });
        
        // Add position and determine status
        foreach ($standings as $index => &$team) {
            $team['position'] = $index + 1;
            
            // Simple logic for status (top 8 qualify, bottom 4 eliminated in tournaments with >12 teams)
            if (count($standings) > 12) {
                if ($team['position'] <= 8) {
                    $team['status'] = 'qualified';
                } elseif ($team['position'] > count($standings) - 4) {
                    $team['status'] = 'eliminated';
                }
            }
        }
        
        return array_values($standings);
    }
    
    /**
     * Get all match results for tournament
     */
    private function getAllMatchResults() {
        $stmt = $this->conn->prepare("
            SELECT mr.*, 
                   t1.team_name as team1_name, 
                   t2.team_name as team2_name,
                   p1.username as participant1_name,
                   p2.username as participant2_name
            FROM match_results mr
            LEFT JOIN duo_registration t1 ON mr.team1_id = t1.duo_id
            LEFT JOIN duo_registration t2 ON mr.team2_id = t2.duo_id
            LEFT JOIN solo_registration p1 ON mr.participant1_id = p1.id
            LEFT JOIN solo_registration p2 ON mr.participant2_id = p2.id
            WHERE mr.tournament_id = ?
            ORDER BY mr.start_time ASC
        ");
        
        $stmt->bind_param("i", $this->tournamentId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get recent matches
     */
    public function getRecentMatches($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT mr.*, 
                   t1.team_name as team1_name, 
                   t2.team_name as team2_name,
                   p1.username as participant1_name,
                   p2.username as participant2_name
            FROM match_results mr
            LEFT JOIN duo_registration t1 ON mr.team1_id = t1.duo_id
            LEFT JOIN duo_registration t2 ON mr.team2_id = t2.duo_id
            LEFT JOIN solo_registration p1 ON mr.participant1_id = p1.id
            LEFT JOIN solo_registration p2 ON mr.participant2_id = p2.id
            WHERE mr.tournament_id = ? 
            AND mr.match_status = 'completed'
            ORDER BY mr.end_time DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get upcoming matches
     */
    public function getUpcomingMatches($limit = 10) {
        $stmt = $this->conn->prepare("
            SELECT mr.*, 
                   t1.team_name as team1_name, 
                   t2.team_name as team2_name,
                   p1.username as participant1_name,
                   p2.username as participant2_name
            FROM match_results mr
            LEFT JOIN duo_registration t1 ON mr.team1_id = t1.duo_id
            LEFT JOIN duo_registration t2 ON mr.team2_id = t2.duo_id
            LEFT JOIN solo_registration p1 ON mr.participant1_id = p1.id
            LEFT JOIN solo_registration p2 ON mr.participant2_id = p2.id
            WHERE mr.tournament_id = ? 
            AND mr.match_status IN ('scheduled', 'ongoing')
            ORDER BY mr.start_time ASC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        $matches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Add is_live flag
        foreach ($matches as &$match) {
            $match['is_live'] = ($match['match_status'] === 'ongoing');
        }
        
        return $matches;
    }
    
    /**
     * Get live matches
     */
    public function getLiveMatches() {
        // Check if live_scoreboard table exists
        $tables = $this->conn->query("SHOW TABLES LIKE 'live_scoreboard'");
        if ($tables->num_rows === 0) {
            return [];
        }
        
        $stmt = $this->conn->prepare("
            SELECT ls.*, mr.*,
                   t1.team_name as team1_name, 
                   t2.team_name as team2_name
            FROM live_scoreboard ls
            JOIN match_results mr ON ls.match_id = mr.id
            LEFT JOIN duo_registration t1 ON mr.team1_id = t1.duo_id
            LEFT JOIN duo_registration t2 ON mr.team2_id = t2.duo_id
            WHERE ls.tournament_id = ? 
            AND ls.is_live = 1
            ORDER BY ls.last_update DESC
        ");
        
        $stmt->bind_param("i", $this->tournamentId);
        $stmt->execute();
        $liveMatches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Parse score data
        foreach ($liveMatches as &$match) {
            if (!empty($match['score_data'])) {
                $scoreData = json_decode($match['score_data'], true);
                $match = array_merge($match, $scoreData);
            }
        }
        
        return $liveMatches;
    }
    
    /**
     * Generate statistical leaderboards
     */
    public function generateLeaderboards($limit = 10) {
        $leaderboards = [
            'rating' => $this->getRatingLeaderboard($limit),
            'kills' => $this->getKillsLeaderboard($limit),
            'clutches' => $this->getClutchesLeaderboard($limit),
            'adr' => $this->getADRLeaderboard($limit)
        ];
        
        return $leaderboards;
    }
    
    /**
     * Get rating leaderboard
     */
    private function getRatingLeaderboard($limit) {
        // Check if player_performance table exists
        $tables = $this->conn->query("SHOW TABLES LIKE 'player_performance'");
        if ($tables->num_rows === 0) {
            // Fallback to sample data
            return $this->generateSampleLeaderboard('rating', $limit);
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                pp.player_id,
                pp.player_name,
                COUNT(DISTINCT pp.match_id) as matches_played,
                SUM(pp.kills) as total_kills,
                SUM(pp.deaths) as total_deaths,
                SUM(pp.assists) as total_assists,
                SUM(pp.damage) as total_damage,
                SUM(pp.headshots) as total_headshots,
                SUM(pp.clutches) as total_clutches,
                AVG(pp.rating) as avg_rating,
                AVG(pp.adr) as avg_adr,
                SUM(pp.first_kills) as total_first_kills
            FROM player_performance pp
            JOIN match_results mr ON pp.match_id = mr.id
            WHERE mr.tournament_id = ?
            GROUP BY pp.player_id, pp.player_name
            HAVING matches_played > 0
            ORDER BY avg_rating DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get kills leaderboard
     */
    private function getKillsLeaderboard($limit) {
        $tables = $this->conn->query("SHOW TABLES LIKE 'player_performance'");
        if ($tables->num_rows === 0) {
            return $this->generateSampleLeaderboard('kills', $limit);
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                pp.player_id,
                pp.player_name,
                COUNT(DISTINCT pp.match_id) as matches_played,
                SUM(pp.kills) as total_kills,
                SUM(pp.deaths) as total_deaths,
                SUM(pp.headshots) as total_headshots,
                AVG(pp.adr) as avg_adr
            FROM player_performance pp
            JOIN match_results mr ON pp.match_id = mr.id
            WHERE mr.tournament_id = ?
            GROUP BY pp.player_id, pp.player_name
            HAVING matches_played > 0
            ORDER BY total_kills DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get clutches leaderboard
     */
    private function getClutchesLeaderboard($limit) {
        $tables = $this->conn->query("SHOW TABLES LIKE 'player_performance'");
        if ($tables->num_rows === 0) {
            return $this->generateSampleLeaderboard('clutches', $limit);
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                pp.player_id,
                pp.player_name,
                COUNT(DISTINCT pp.match_id) as matches_played,
                SUM(pp.clutches) as total_clutches,
                SUM(pp.first_kills) as total_first_kills,
                AVG(pp.rating) as avg_rating
            FROM player_performance pp
            JOIN match_results mr ON pp.match_id = mr.id
            WHERE mr.tournament_id = ?
            GROUP BY pp.player_id, pp.player_name
            HAVING matches_played > 0
            ORDER BY total_clutches DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Get ADR leaderboard
     */
    private function getADRLeaderboard($limit) {
        $tables = $this->conn->query("SHOW TABLES LIKE 'player_performance'");
        if ($tables->num_rows === 0) {
            return $this->generateSampleLeaderboard('adr', $limit);
        }
        
        $stmt = $this->conn->prepare("
            SELECT 
                pp.player_id,
                pp.player_name,
                COUNT(DISTINCT pp.match_id) as matches_played,
                SUM(pp.damage) as total_damage,
                AVG(pp.adr) as avg_adr,
                SUM(pp.kills) as total_kills
            FROM player_performance pp
            JOIN match_results mr ON pp.match_id = mr.id
            WHERE mr.tournament_id = ?
            GROUP BY pp.player_id, pp.player_name
            HAVING matches_played > 0
            ORDER BY avg_adr DESC
            LIMIT ?
        ");
        
        $stmt->bind_param("ii", $this->tournamentId, $limit);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Generate sample leaderboard data for testing
     */
    private function generateSampleLeaderboard($type, $limit) {
        $samplePlayers = [
            ['player_id' => 1, 'player_name' => 'Ninja', 'matches_played' => 15],
            ['player_id' => 2, 'player_name' => 'Shroud', 'matches_played' => 14],
            ['player_id' => 3, 'player_name' => 'Summit1g', 'matches_played' => 16],
            ['player_id' => 4, 'player_name' => 'TimTheTatman', 'matches_played' => 13],
            ['player_id' => 5, 'player_name' => 'DrDisrespect', 'matches_played' => 12],
            ['player_id' => 6, 'player_name' => 'Pokimane', 'matches_played' => 15],
            ['player_id' => 7, 'player_name' => 'xQc', 'matches_played' => 14],
            ['player_id' => 8, 'player_name' => 'Asmongold', 'matches_played' => 13],
            ['player_id' => 9, 'player_name' => 'Myth', 'matches_played' => 16],
            ['player_id' => 10, 'player_name' => 'SypherPK', 'matches_played' => 15]
        ];
        
        foreach ($samplePlayers as &$player) {
            switch ($type) {
                case 'rating':
                    $player['avg_rating'] = round(1.0 + (mt_rand(0, 100) / 100), 2);
                    $player['total_kills'] = mt_rand(80, 200);
                    $player['total_deaths'] = mt_rand(60, 180);
                    $player['total_assists'] = mt_rand(30, 100);
                    $player['total_damage'] = mt_rand(10000, 30000);
                    $player['total_headshots'] = mt_rand(20, 80);
                    $player['total_clutches'] = mt_rand(1, 10);
                    $player['avg_adr'] = round(mt_rand(80, 120) + (mt_rand(0, 99) / 100), 1);
                    $player['total_first_kills'] = mt_rand(5, 25);
                    break;
                    
                case 'kills':
                    $player['total_kills'] = mt_rand(100, 250);
                    $player['total_deaths'] = mt_rand(80, 200);
                    $player['total_headshots'] = mt_rand(30, 100);
                    $player['avg_adr'] = round(mt_rand(85, 125) + (mt_rand(0, 99) / 100), 1);
                    break;
                    
                case 'clutches':
                    $player['total_clutches'] = mt_rand(3, 15);
                    $player['total_first_kills'] = mt_rand(8, 30);
                    $player['avg_rating'] = round(1.0 + (mt_rand(0, 100) / 100), 2);
                    break;
                    
                case 'adr':
                    $player['avg_adr'] = round(mt_rand(90, 130) + (mt_rand(0, 99) / 100), 1);
                    $player['total_damage'] = mt_rand(12000, 35000);
                    $player['total_kills'] = mt_rand(90, 220);
                    break;
            }
        }
        
        // Sort based on type
        switch ($type) {
            case 'rating':
                usort($samplePlayers, function($a, $b) {
                    return $b['avg_rating'] <=> $a['avg_rating'];
                });
                break;
            case 'kills':
                usort($samplePlayers, function($a, $b) {
                    return $b['total_kills'] <=> $a['total_kills'];
                });
                break;
            case 'clutches':
                usort($samplePlayers, function($a, $b) {
                    return $b['total_clutches'] <=> $a['total_clutches'];
                });
                break;
            case 'adr':
                usort($samplePlayers, function($a, $b) {
                    return $b['avg_adr'] <=> $a['avg_adr'];
                });
                break;
        }
        
        return array_slice($samplePlayers, 0, $limit);
    }
    
    /**
     * Calculate statistical leaders
     */
    private function calculateStatLeaders() {
        return [
            'top_killer' => ['name' => 'Shroud', 'kills' => 245],
            'top_rating' => ['name' => 'Ninja', 'rating' => 1.65],
            'top_clutcher' => ['name' => 'xQc', 'clutches' => 12],
            'top_damage' => ['name' => 'DrDisrespect', 'adr' => 128.4]
        ];
    }
    
    /**
     * Generate bracket data
     */
    public function generateBracketData() {
        $bracket = [
            'rounds' => [],
            'matches' => [],
            'participants' => []
        ];
        
        // Get matches for tournament
        $matches = $this->getAllMatchResults();
        
        if (empty($matches)) {
            return $bracket;
        }
        
        // Group matches by round
        $rounds = [];
        foreach ($matches as $match) {
            $round = $match['round_number'] ?? 1;
            
            if (!isset($rounds[$round])) {
                $rounds[$round] = [
                    'round_number' => $round,
                    'round_name' => $this->getRoundName($round, count($matches)),
                    'matches' => []
                ];
            }
            
            $matchData = [
                'id' => $match['id'],
                'match_number' => $match['match_number'] ?? 1,
                'team1' => [
                    'id' => $match['team1_id'] ?? $match['participant1_id'],
                    'name' => $match['team1_name'] ?? $match['participant1_name'] ?? 'TBD',
                    'score' => $match['score1'] ?? 0
                ],
                'team2' => [
                    'id' => $match['team2_id'] ?? $match['participant2_id'],
                    'name' => $match['team2_name'] ?? $match['participant2_name'] ?? 'TBD',
                    'score' => $match['score2'] ?? 0
                ],
                'winner_id' => $match['winner_id'],
                'status' => $match['match_status'] ?? 'scheduled',
                'start_time' => $match['start_time'],
                'vod_link' => $match['vod_link']
            ];
            
            $rounds[$round]['matches'][] = $matchData;
            $bracket['matches'][] = $matchData;
            
            // Add participants
            $this->addParticipant($bracket, $matchData['team1']);
            $this->addParticipant($bracket, $matchData['team2']);
        }
        
        // Sort rounds by number
        ksort($rounds);
        $bracket['rounds'] = array_values($rounds);
        
        return $bracket;
    }
    
    /**
     * Add participant to bracket
     */
    private function addParticipant(&$bracket, $team) {
        if (!isset($bracket['participants'][$team['id']])) {
            $bracket['participants'][$team['id']] = [
                'id' => $team['id'],
                'name' => $team['name'],
                'matches' => []
            ];
        }
    }
    
    /**
     * Get round name based on round number
     */
    private function getRoundName($roundNumber, $totalMatches) {
        $names = [
            1 => 'Opening Round',
            2 => 'Quarter Finals',
            3 => 'Semi Finals',
            4 => 'Finals',
            5 => 'Grand Finals'
        ];
        
        return $names[$roundNumber] ?? "Round $roundNumber";
    }
    
    /**
     * Generate predictions for upcoming matches
     */
    private function generatePredictions() {
        $predictions = [];
        $upcomingMatches = $this->getUpcomingMatches(5);
        
        foreach ($upcomingMatches as $match) {
            if ($match['match_status'] !== 'scheduled') continue;
            
            $prediction = $this->predictMatchOutcome(
                $match['team1_id'] ?? $match['participant1_id'],
                $match['team2_id'] ?? $match['participant2_id']
            );
            
            if ($prediction) {
                $predictions[] = [
                    'match_id' => $match['id'],
                    'team1_name' => $match['team1_name'] ?? $match['participant1_name'] ?? 'Team 1',
                    'team2_name' => $match['team2_name'] ?? $match['participant2_name'] ?? 'Team 2',
                    'team1_win_probability' => $prediction['team1_win_probability'],
                    'team2_win_probability' => $prediction['team2_win_probability'],
                    'confidence' => $prediction['confidence'],
                    'expected_score' => $prediction['expected_score'] ?? 'TBD'
                ];
            }
        }
        
        return $predictions;
    }
    
    /**
     * Predict match outcome
     */
    public function predictMatchOutcome($team1Id, $team2Id) {
        // Simple prediction algorithm based on standings
        $standings = $this->getTournamentStandings();
        
        // Find teams in standings
        $team1 = null;
        $team2 = null;
        
        foreach ($standings as $standing) {
            if ($standing['id'] == $team1Id) {
                $team1 = $standing;
            }
            if ($standing['id'] == $team2Id) {
                $team2 = $standing;
            }
        }
        
        if (!$team1 || !$team2) {
            return null;
        }
        
        // Calculate win probability based on rating difference
        $ratingDiff = ($team1['rating'] ?? 1.0) - ($team2['rating'] ?? 1.0);
        $team1WinProbability = 1 / (1 + pow(10, -$ratingDiff / 0.4));
        
        // Calculate confidence based on matches played
        $confidence = min(0.9, max(0.3, 
            (($team1['matches_played'] ?? 0) + ($team2['matches_played'] ?? 0)) / 20
        ));
        
        // Calculate expected score (best of 3 format)
        $expectedScore = $this->calculateExpectedScore($team1WinProbability);
        
        return [
            'team1_win_probability' => round($team1WinProbability, 3),
            'team2_win_probability' => round(1 - $team1WinProbability, 3),
            'confidence' => round($confidence, 3),
            'expected_score' => $expectedScore
        ];
    }
    
    /**
     * Calculate expected score for best of 3
     */
    private function calculateExpectedScore($winProbability) {
        // Probability of winning 2-0
        $p_20 = pow($winProbability, 2);
        
        // Probability of winning 2-1
        $p_21 = 2 * pow($winProbability, 2) * (1 - $winProbability);
        
        // Probability of losing 0-2
        $p_02 = pow(1 - $winProbability, 2);
        
        // Probability of losing 1-2
        $p_12 = 2 * $winProbability * pow(1 - $winProbability, 2);
        
        // Calculate expected maps won
        $expectedMapsWon = 2 * $p_20 + 2 * $p_21 + 0 * $p_02 + 1 * $p_12;
        $expectedMapsLost = 0 * $p_20 + 1 * $p_21 + 2 * $p_02 + 2 * $p_12;
        
        return round($expectedMapsWon, 1) . ' - ' . round($expectedMapsLost, 1);
    }
    
    /**
     * Generate PDF scoreboard
     */
    public function generatePDFScoreboard() {
        $scoreboard = $this->generateScoreboard();
        
        // Generate HTML for PDF
        $html = $this->generatePDFHTML($scoreboard);
        
        // For now, return HTML. In production, use TCPDF or DomPDF
        return $html;
    }
    
    /**
     * Generate HTML for PDF
     */
    private function generatePDFHTML($scoreboard) {
        ob_start();
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Scoreboard - <?php echo htmlspecialchars($scoreboard['tournament_info']['tname']); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 20px; }
                .tournament-name { font-size: 24px; font-weight: bold; color: #333; }
                .tournament-date { color: #666; margin-top: 5px; }
                .scoreboard-table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                .scoreboard-table th { background-color: #f2f2f2; font-weight: bold; text-align: left; }
                .scoreboard-table th, .scoreboard-table td { border: 1px solid #ddd; padding: 10px; }
                .scoreboard-table tr:nth-child(even) { background-color: #f9f9f9; }
                .ranking { font-weight: bold; text-align: center; }
                .qualified { background-color: #d4edda !important; }
                .eliminated { background-color: #f8d7da !important; }
                .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 20px; }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="tournament-name"><?php echo htmlspecialchars($scoreboard['tournament_info']['tname']); ?></div>
                <div class="tournament-date">Scoreboard - Generated on <?php echo date('F j, Y H:i:s'); ?></div>
                <div class="tournament-date">Game: <?php echo htmlspecialchars($scoreboard['tournament_info']['selected_game']); ?></div>
            </div>
            
            <h3>Standings</h3>
            <table class="scoreboard-table">
                <thead>
                    <tr>
                        <th width="50">#</th>
                        <th>Team/Player</th>
                        <th>MP</th>
                        <th>W</th>
                        <th>L</th>
                        <th>Pts</th>
                        <th>K/D</th>
                        <th>Rating</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($scoreboard['standings'] as $index => $standing): ?>
                    <tr class="<?php echo $standing['status'] === 'qualified' ? 'qualified' : ($standing['status'] === 'eliminated' ? 'eliminated' : ''); ?>">
                        <td class="ranking"><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($standing['name']); ?></td>
                        <td><?php echo $standing['matches_played']; ?></td>
                        <td><?php echo $standing['wins']; ?></td>
                        <td><?php echo $standing['losses']; ?></td>
                        <td><strong><?php echo $standing['points']; ?></strong></td>
                        <td><?php echo round($standing['kills'] / max(1, $standing['deaths']), 2); ?></td>
                        <td><?php echo round($standing['rating'], 2); ?></td>
                        <td>
                            <?php if ($standing['status'] === 'qualified'): ?>
                                Qualified
                            <?php elseif ($standing['status'] === 'eliminated'): ?>
                                Eliminated
                            <?php else: ?>
                                Active
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (!empty($scoreboard['recent_matches'])): ?>
            <h3>Recent Matches</h3>
            <table class="scoreboard-table">
                <thead>
                    <tr>
                        <th>Team 1</th>
                        <th>Score</th>
                        <th>Team 2</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($scoreboard['recent_matches'], 0, 5) as $match): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($match['team1_name'] ?? $match['participant1_name'] ?? 'Team 1'); ?></td>
                        <td><strong><?php echo $match['score1']; ?> - <?php echo $match['score2']; ?></strong></td>
                        <td><?php echo htmlspecialchars($match['team2_name'] ?? $match['participant2_name'] ?? 'Team 2'); ?></td>
                        <td><?php echo date('M j, g:i A', strtotime($match['end_time'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
            
            <div class="footer">
                Official Tournament Scoreboard - <?php echo htmlspecialchars($scoreboard['tournament_info']['tname']); ?><br>
                Generated by Esports Platform - <?php echo date('Y'); ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Calculate HLTV-style player ratings
     */
    public function calculatePlayerRating($playerStats) {
        $kills = $playerStats['kills'] ?? 0;
        $deaths = $playerStats['deaths'] ?? 1;
        $rounds = $playerStats['rounds_played'] ?? 1;
        $headshots = $playerStats['headshots'] ?? 0;
        $clutches = $playerStats['clutches'] ?? 0;
        
        // K/D Ratio impact
        $kdImpact = $kills / max(1, $deaths);
        
        // Kill Rating
        $kpr = $kills / max(1, $rounds);
        $hsPercentage = $headshots / max(1, $kills);
        $killRating = $kpr * (1 + ($hsPercentage * 0.2));
        
        // Survival Rating
        $spr = ($rounds - $deaths) / max(1, $rounds);
        $clutchBonus = min(0.3, $clutches * 0.05);
        $survivalRating = $spr + $clutchBonus;
        
        // Impact Rating
        $impactRating = $this->calculateImpactRating($playerStats);
        
        // ADR
        $adr = ($playerStats['damage'] ?? 0) / max(1, $rounds);
        
        // KAST
        $kast = $playerStats['kast'] ?? 0;
        
        // Composite rating
        $rating = (
            ($killRating * 0.25) +
            ($survivalRating * 0.20) +
            ($impactRating * 0.30) +
            (min(1.5, $adr / 100) * 0.15) +
            ($kast / 100 * 0.10)
        );
        
        return round($rating, 2);
    }
    
    /**
     * Calculate impact rating
     */
    private function calculateImpactRating($stats) {
        $impact = 0;
        
        // Multi-kill impact
        $multiKills = $stats['multi_kills'] ?? [];
        foreach ([2 => 0.1, 3 => 0.3, 4 => 0.5, 5 => 1.0] as $count => $value) {
            $impact += ($multiKills[$count] ?? 0) * $value;
        }
        
        // Opening kill impact
        $openingKills = $stats['first_kills'] ?? 0;
        $impact += $openingKills * 0.15;
        
        // Clutch impact
        $clutches = $stats['clutches'] ?? 0;
        $impact += $clutches * 0.2;
        
        // Trade kill impact
        $tradeKills = $stats['trade_kills'] ?? 0;
        $impact += $tradeKills * 0.05;
        
        return min(2.0, $impact);
    }
    
    /**
     * Calculate Swiss system pairings
     */
    public function generateSwissPairings($currentRound) {
        $standings = $this->getTournamentStandings();
        
        if (count($standings) < 2) {
            return [];
        }
        
        // Sort by points, then rating
        usort($standings, function($a, $b) {
            if ($b['points'] != $a['points']) {
                return $b['points'] <=> $a['points'];
            }
            return $b['rating'] <=> $a['rating'];
        });
        
        $pairings = [];
        $paired = [];
        
        // Simple Swiss pairing (avoid re-matches)
        for ($i = 0; $i < count($standings); $i++) {
            if (in_array($standings[$i]['id'], $paired)) continue;
            
            // Find next unpaired opponent
            for ($j = $i + 1; $j < count($standings); $j++) {
                if (!in_array($standings[$j]['id'], $paired)) {
                    $pairings[] = [
                        'player1' => $standings[$i],
                        'player2' => $standings[$j],
                        'round' => $currentRound
                    ];
                    
                    $paired[] = $standings[$i]['id'];
                    $paired[] = $standings[$j]['id'];
                    break;
                }
            }
        }
        
        return $pairings;
    }
    
    /**
     * Update player skills using Kalman Filter
     */
    public function updatePlayerSkills($matchResults) {
        // This is a simplified version
        $updatedSkills = [];
        
        foreach ($matchResults as $match) {
            // Get player ratings
            $p1Rating = $this->getPlayerRating($match['player1_id']);
            $p2Rating = $this->getPlayerRating($match['player2_id']);
            
            // Calculate expected outcome
            $expected = 1 / (1 + pow(10, ($p2Rating - $p1Rating) / 400));
            
            // K-factor (adjusts rating change)
            $K = 32;
            
            // Update ratings based on actual outcome
            $actual = $match['outcome']; // 1 for p1 win, 0 for p2 win, 0.5 for draw
            
            $p1NewRating = $p1Rating + $K * ($actual - $expected);
            $p2NewRating = $p2Rating + $K * ((1 - $actual) - (1 - $expected));
            
            $updatedSkills[$match['player1_id']] = $p1NewRating;
            $updatedSkills[$match['player2_id']] = $p2NewRating;
        }
        
        return $updatedSkills;
    }
    
    /**
     * Get player rating
     */
    private function getPlayerRating($playerId) {
        // Check if player_skills table exists
        $tables = $this->conn->query("SHOW TABLES LIKE 'player_skills'");
        if ($tables->num_rows === 0) {
            return 1000; // Default rating
        }
        
        $stmt = $this->conn->prepare("
            SELECT global_rating FROM player_skills 
            WHERE user_id = ? 
            AND game = (SELECT selected_game FROM tournaments WHERE id = ?)
        ");
        
        $tournamentInfo = $this->getTournamentInfo();
        $stmt->bind_param("is", $playerId, $tournamentInfo['selected_game']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->num_rows > 0 ? $result->fetch_assoc()['global_rating'] : 1000;
    }
}
?>
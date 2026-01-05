<?php
// prediction_uploads.php
header('Content-Type: application/json');
require_once 'config.php'; // Your existing config.php

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $matchType = $_POST['matchType'] ?? 'solo';
        $playerData = $_POST['playerData'] ?? null;
        
        // Validate match type
        $validMatchTypes = ['solo', 'duo', 'squad'];
        if (!in_array($matchType, $validMatchTypes)) {
            throw new Exception('Invalid match type.');
        }
        
        $filename = null;
        $extractedData = null;
        
        // Check if we have direct player data or file upload
        if ($playerData) {
            // Process direct player/team data
            $extractedData = simulateDataExtraction(null, $matchType, $playerData);
            $filename = 'manual_input_' . time() . '.json';
        } elseif (isset($_FILES['statsFile']) && $_FILES['statsFile']['error'] === UPLOAD_ERR_OK) {
            // Process file upload
            $file = $_FILES['statsFile'];
            
            // Validate file type
            $allowedTypes = ['image/png'];
            if (!in_array($file['type'], $allowedTypes)) {
                throw new Exception('Invalid file type. Only PNG files are allowed.');
            }

            // Validate file size (5MB max)
            $maxSize = 5 * 1024 * 1024;
            if ($file['size'] > $maxSize) {
                throw new Exception('File size exceeds 5MB limit.');
            }

            // Create uploads directory if it doesn't exist
            if (!file_exists('uploads')) {
                mkdir('uploads', 0777, true);
            }

            // Generate unique filename
            $filename = uniqid('stats_', true) . '.png';
            $filepath = 'uploads/' . $filename;

            // Move uploaded file
            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                throw new Exception('Failed to save uploaded file.');
            }

            // Simulate OCR/AI data extraction
            $extractedData = simulateDataExtraction($filepath, $matchType);
        } else {
            throw new Exception('No data provided. Please upload a file or enter player data.');
        }
        
        // Store in database
        $stmt = $conn->prepare("INSERT INTO predictions (filename, match_type, extracted_data, upload_time) VALUES (?, ?, ?, NOW())");
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $extractedDataJson = json_encode($extractedData);
        $stmt->bind_param("sss", $filename, $matchType, $extractedDataJson);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save prediction data: ' . $stmt->error);
        }
        
        $predictionId = $stmt->insert_id;
        $stmt->close();

        // Get AI prediction
        $predictionResult = getAIPrediction($extractedData, $matchType);

        // Store prediction result
        $stmt = $conn->prepare("UPDATE predictions SET prediction_result = ? WHERE id = ?");
        if (!$stmt) {
            throw new Exception('Database error: ' . $conn->error);
        }
        
        $predictionResultJson = json_encode($predictionResult);
        $stmt->bind_param("si", $predictionResultJson, $predictionId);
        
        if (!$stmt->execute()) {
            // Log error but don't fail the request
            error_log("Failed to update prediction result for ID: $predictionId - " . $stmt->error);
        }
        $stmt->close();

        echo json_encode([
            'success' => true,
            'message' => 'Data processed successfully',
            'predictionId' => $predictionId,
            'extractedData' => $extractedData,
            'prediction' => $predictionResult
        ]);

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

// Data extraction functions
function simulateDataExtraction($filepath, $matchType, $playerData = null) {
    // If player data is provided via form, use it
    if ($playerData) {
        $data = json_decode($playerData, true);
        
        if ($matchType === 'solo') {
            return [
                'matchType' => $matchType,
                'players' => $data['players'],
                'isTeamBased' => false
            ];
        } else {
            // Ensure teams data exists
            if (isset($data['teams'])) {
                return [
                    'matchType' => $matchType,
                    'teams' => $data['teams'],
                    'isTeamBased' => true
                ];
            } else {
                // Fallback: Convert players to teams
                return createTeamsFromPlayers($data['players'], $matchType);
            }
        }
    }
    
    // Fallback to simulated data from file
    $players = [];
    $playerCount = $matchType === 'solo' ? 10 : ($matchType === 'duo' ? 10 : 16);
    
    for ($i = 1; $i <= $playerCount; $i++) {
        $players[] = [
            'name' => 'Player_' . $i . '_' . uniqid(),
            'kills' => rand(2, 15),
            'damage' => rand(150, 800),
            'survival' => rand(120, 1200),
            'headshots' => rand(0, 8),
            'assists' => rand(0, 6),
            'revives' => $matchType !== 'solo' ? rand(0, 3) : 0
        ];
    }
    
    if ($matchType === 'solo') {
        return [
            'matchType' => $matchType,
            'players' => $players,
            'isTeamBased' => false
        ];
    } else {
        return createTeamsFromPlayers($players, $matchType);
    }
}

function createTeamsFromPlayers($players, $matchType) {
    $playersPerTeam = $matchType === 'duo' ? 2 : 4;
    $teamCount = floor(count($players) / $playersPerTeam);
    $teams = [];
    
    shuffle($players); // Randomly assign players to teams
    
    for ($i = 0; $i < $teamCount; $i++) {
        $teamPlayers = array_slice($players, $i * $playersPerTeam, $playersPerTeam);
        $team = [
            'name' => 'Team ' . ($i + 1),
            'players' => $teamPlayers
        ];
        
        // Calculate team stats
        $team['totalKills'] = array_sum(array_column($teamPlayers, 'kills'));
        $team['totalDamage'] = array_sum(array_column($teamPlayers, 'damage'));
        $team['avgSurvival'] = array_sum(array_column($teamPlayers, 'survival')) / count($teamPlayers);
        
        $teams[] = $team;
    }
    
    return [
        'matchType' => $matchType,
        'teams' => $teams,
        'isTeamBased' => true
    ];
}

function getAIPrediction($data, $matchType) {
    if ($matchType === 'solo') {
        return predictSoloWinner($data['players']);
    } else {
        return predictTeamWinner($data['teams'], $matchType);
    }
}

function predictSoloWinner($players) {
    // Calculate rating for each player
    foreach ($players as &$player) {
        $player['rating'] = calculatePlayerRating($player);
    }
    
    // Sort by rating
    usort($players, function($a, $b) {
        return $b['rating'] <=> $a['rating'];
    });
    
    $winner = $players[0];
    $confidence = min(95, 70 + ($winner['rating'] * 3));
    
    // Find top performer and weak link
    $topPerformer = $players[0];
    $weakLink = end($players);
    
    // Generate insights
    $insights = [
        "Player rating: " . round($winner['rating'], 1) . "/10",
        "Kills per match: " . $winner['kills'],
        "Average damage: " . $winner['damage'],
        "Survival time: " . floor($winner['survival'] / 60) . " minutes"
    ];
    
    return [
        'winner' => $winner,
        'confidence' => round($confidence, 1),
        'topPerformer' => $topPerformer,
        'weakLink' => $weakLink,
        'allPlayers' => $players,
        'insights' => $insights,
        'predictionType' => 'solo'
    ];
}

function predictTeamWinner($teams, $matchType) {
    // Calculate team ratings
    foreach ($teams as &$team) {
        // Calculate player ratings
        $totalRating = 0;
        foreach ($team['players'] as &$player) {
            $player['rating'] = calculatePlayerRating($player);
            $totalRating += $player['rating'];
        }
        
        $team['avgRating'] = $totalRating / count($team['players']);
        
        // Team synergy bonus
        $team['synergyBonus'] = calculateSynergyBonus($team);
        
        // Final team score
        $team['teamScore'] = ($team['avgRating'] * 0.7) + ($team['synergyBonus'] * 0.3);
    }
    
    // Sort teams by score
    usort($teams, function($a, $b) {
        return $b['teamScore'] <=> $a['teamScore'];
    });
    
    $winningTeam = $teams[0];
    
    // Calculate confidence based on score gap
    $scoreGap = isset($teams[1]) ? $winningTeam['teamScore'] - $teams[1]['teamScore'] : 0;
    $confidence = min(95, 60 + ($scoreGap * 20));
    
    // Find MVP (best player in winning team)
    $mvp = $winningTeam['players'][0];
    foreach ($winningTeam['players'] as $player) {
        if ($player['rating'] > $mvp['rating']) {
            $mvp = $player;
        }
    }
    
    // Find weak link in winning team
    $teamWeakLink = $winningTeam['players'][0];
    foreach ($winningTeam['players'] as $player) {
        if ($player['rating'] < $teamWeakLink['rating']) {
            $teamWeakLink = $player;
        }
    }
    
    // Generate team-specific insights
    $insights = [
        "Team synergy score: " . round($winningTeam['synergyBonus'], 1) . "/10",
        "Strongest area: " . identifyTeamStrength($winningTeam),
        "Area for improvement: " . identifyTeamWeakness($winningTeam),
        "Team coordination level: " . getCoordinationLevel($winningTeam)
    ];
    
    return [
        'winningTeam' => $winningTeam,
        'confidence' => round($confidence, 1),
        'mvp' => $mvp,
        'teamWeakLink' => $teamWeakLink,
        'allTeams' => $teams,
        'insights' => $insights,
        'predictionType' => $matchType
    ];
}

function calculatePlayerRating($player) {
    // Calculate player rating based on multiple factors
    $killScore = min(10, $player['kills'] * 0.7);
    $damageScore = min(10, $player['damage'] / 80);
    $survivalScore = min(10, $player['survival'] / 120);
    $headshotScore = isset($player['headshots']) ? min(5, $player['headshots'] * 0.6) : 0;
    $assistScore = isset($player['assists']) ? min(5, $player['assists'] * 0.5) : 0;
    
    // Weighted average
    $rating = ($killScore * 0.35) + ($damageScore * 0.25) + 
              ($survivalScore * 0.20) + ($headshotScore * 0.10) +
              ($assistScore * 0.10);
    
    return round($rating, 1);
}

function calculateSynergyBonus($team) {
    // Calculate how well team members complement each other
    $kills = array_column($team['players'], 'kills');
    $damages = array_column($team['players'], 'damage');
    
    // Teams with balanced contributions get higher synergy
    $killStdDev = calculateStandardDeviation($kills);
    $damageStdDev = calculateStandardDeviation($damages);
    
    // Lower standard deviation = better synergy
    $synergy = 10 - (($killStdDev * 1.5) + ($damageStdDev / 150));
    return max(0, min(10, $synergy));
}

function calculateStandardDeviation($array) {
    if (count($array) < 2) return 0;
    
    $mean = array_sum($array) / count($array);
    $variance = 0.0;
    
    foreach ($array as $value) {
        $variance += pow($value - $mean, 2);
    }
    
    return sqrt($variance / count($array));
}

function identifyTeamStrength($team) {
    $avgKills = $team['totalKills'] / count($team['players']);
    $avgDamage = $team['totalDamage'] / count($team['players']);
    
    if ($avgKills > 8) return "Aggressive Playstyle";
    if ($avgDamage > 500) return "High Damage Output";
    if ($team['avgSurvival'] > 600) return "Excellent Survival Skills";
    return "Balanced Performance";
}

function identifyTeamWeakness($team) {
    $survivals = array_column($team['players'], 'survival');
    $minSurvival = min($survivals);
    
    if ($minSurvival < 300) return "Early Elimination Risk";
    if ($team['synergyBonus'] < 4) return "Poor Team Coordination";
    return "Mid-game Positioning";
}

function getCoordinationLevel($team) {
    $synergy = $team['synergyBonus'];
    
    if ($synergy > 8) return "Excellent";
    if ($synergy > 6) return "Good";
    if ($synergy > 4) return "Average";
    return "Needs Improvement";
}

// Handle invalid requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed'
    ]);
}
?>
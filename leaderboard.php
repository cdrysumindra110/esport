<?php
include('header.php');

if (!isset($_GET['tournament_id']) || !is_numeric($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}

$tournament_id = intval($_GET['tournament_id']);
include('config.php');

// ==================== VALID ACADEMIC SCORING ALGORITHM ====================
/**
 * Academic Tournament Scoring Algorithm
 * Formula: Total Score = Placement Points + Kill Points
 * 
 * Placement Points (Power Law Distribution):
 * 1st: 100 points
 * 2nd: 80 points
 * 3rd: 65 points
 * 4th: 55 points
 * 5th: 50 points
 * 6th: 45 points
 * 7th: 40 points
 * 8th: 35 points
 * 9th: 30 points
 * 10th: 25 points
 * 11th-15th: 15 points
 * 16th-20th: 10 points
 * 21st+: 5 points
 * 
 * Kill Points: 10 points per kill
 */
function calculatePlacementPoints($placement) {
    // Power law distribution - top placements get exponentially more points
    switch($placement) {
        case 1: return 100;
        case 2: return 80;
        case 3: return 65;
        case 4: return 55;
        case 5: return 50;
        case 6: return 45;
        case 7: return 40;
        case 8: return 35;
        case 9: return 30;
        case 10: return 25;
        case 11: case 12: case 13: case 14: case 15: return 15;
        case 16: case 17: case 18: case 19: case 20: return 10;
        default: return 5;
    }
}

function calculateTotalScore($placement, $kills) {
    $placementPoints = calculatePlacementPoints($placement);
    $killPoints = $kills * 10;
    return $placementPoints + $killPoints;
}

// ==================== FETCH TOURNAMENT INFO ====================
$tournament_name = "Tournament #$tournament_id";
$stmt = $conn->prepare("SELECT tname FROM tournaments WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $row = $res->fetch_assoc();
        $tournament_name = $row['tname'] ?? $tournament_name;
    }
    $stmt->close();
}

// Get match type
$match_type = 'solo';
$stmt = $conn->prepare("SELECT match_type FROM brackets WHERE tournament_id = ? LIMIT 1");
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows) {
        $bracket_data = $res->fetch_assoc();
        $match_type = $bracket_data['match_type'] ?? 'solo';
    }
    $stmt->close();
}

// Get current match number
$current_match_number = 1;
$check_column = $conn->query("SHOW COLUMNS FROM brackets LIKE 'match_number'");
if ($check_column && $check_column->num_rows > 0) {
    $stmt = $conn->prepare("SELECT match_number FROM brackets WHERE tournament_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows) {
            $row = $res->fetch_assoc();
            $current_match_number = $row['match_number'] ?? 1;
        }
        $stmt->close();
    }
}

// ==================== FETCH PARTICIPANTS WITH IMPROVED TEAM NAME FETCHING ====================
$participants = [];
$match_history = [];

// ==================== HELPER FUNCTION TO GET TEAM NAME WITH LOGO ====================
function getTeamInfo($conn, $team_id, $tournament_id, $match_type) {
    $team_info = [
        'name' => 'Team ' . $team_id,
        'logo' => 'img/default-logo.png' // Default logo path
    ];
    
    // Get team name and logo based on match type
    if ($match_type == 'solo') {
        // Fixed: Only select player_name, not tname
        $name_sql = "SELECT player_name, logo_path FROM solo_registration WHERE solo_id = ? AND tournament_id = ?";
        $name_stmt = $conn->prepare($name_sql);
        $name_stmt->bind_param("ii", $team_id, $tournament_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        if ($name_row = $name_result->fetch_assoc()) {
            $team_info['name'] = $name_row['player_name'] ?? 'Player ' . $team_id;
            
            // Handle BLOB logo_path - check if it's a valid image path or BLOB
            $logo_path = $name_row['logo_path'] ?? '';
            if (!empty($logo_path)) {
                // If it's a BLOB (binary data), we need to handle it differently
                // Check if it starts with typical image file extensions
                if (is_string($logo_path) && strlen($logo_path) < 255 && 
                    (strpos($logo_path, '.png') !== false || 
                     strpos($logo_path, '.jpg') !== false || 
                     strpos($logo_path, '.jpeg') !== false || 
                     strpos($logo_path, '.gif') !== false)) {
                    $team_info['logo'] = $logo_path;
                } else {
                    // It's likely a BLOB, use default logo
                    $team_info['logo'] = 'img/default-logo.png';
                }
            }
        }
        $name_stmt->close();
    } 
    elseif ($match_type == 'duo') {
        $name_sql = "SELECT team_name, logo_path FROM duo_registration WHERE duo_id = ? AND tournament_id = ?";
        $name_stmt = $conn->prepare($name_sql);
        $name_stmt->bind_param("ii", $team_id, $tournament_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        if ($name_row = $name_result->fetch_assoc()) {
            $team_info['name'] = $name_row['team_name'] ?? 'Duo ' . $team_id;
            
            $logo_path = $name_row['logo_path'] ?? '';
            if (!empty($logo_path) && is_string($logo_path) && strlen($logo_path) < 255) {
                $team_info['logo'] = $logo_path;
            }
        }
        $name_stmt->close();
    } 
    elseif ($match_type == 'squad') {
        $name_sql = "SELECT team_name, logo_path FROM squad_registration WHERE squad_id = ? AND tournament_id = ?";
        $name_stmt = $conn->prepare($name_sql);
        $name_stmt->bind_param("ii", $team_id, $tournament_id);
        $name_stmt->execute();
        $name_result = $name_stmt->get_result();
        if ($name_row = $name_result->fetch_assoc()) {
            $team_info['name'] = $name_row['team_name'] ?? 'Squad ' . $team_id;
            
            $logo_path = $name_row['logo_path'] ?? '';
            if (!empty($logo_path) && is_string($logo_path) && strlen($logo_path) < 255) {
                $team_info['logo'] = $logo_path;
            }
        }
        $name_stmt->close();
    }
    
    return $team_info;
}

// METHOD 1: Check tournament_participants table (New System)
$check_participants_table = $conn->query("SHOW TABLES LIKE 'tournament_participants'");
if ($check_participants_table && $check_participants_table->num_rows > 0) {
    // Check if table has data
    $check_data = $conn->prepare("SELECT COUNT(*) as count FROM tournament_participants WHERE tournament_id = ?");
    $check_data->bind_param("i", $tournament_id);
    $check_data->execute();
    $data_result = $check_data->get_result();
    $data_row = $data_result->fetch_assoc();
    
    if ($data_row['count'] > 0) {
        // Get participants from tournament_participants
        $sql = "SELECT 
                    tp.id,
                    tp.team_id,
                    tp.total_score,
                    tp.total_kills,
                    tp.average_placement,
                    tp.matches_played
                FROM tournament_participants tp
                WHERE tp.tournament_id = ? 
                ORDER BY tp.total_score DESC";
        
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("i", $tournament_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            while ($row = $result->fetch_assoc()) {
                $team_id = $row['team_id'];
                
                // Get team info with logo
                $team_info = getTeamInfo($conn, $team_id, $tournament_id, $match_type);
                
                $row['team_name'] = $team_info['name'];
                $row['team_logo'] = $team_info['logo'];
                $row['source'] = 'tournament_participants';
                $participants[] = $row;
            }
            $stmt->close();
        }
    }
    $check_data->close();
}

// METHOD 2: Check brackets table (with proper column checking)
if (empty($participants)) {
    // First check what columns exist in brackets table
    $columns_result = $conn->query("SHOW COLUMNS FROM brackets");
    $bracket_columns = [];
    while ($col = $columns_result->fetch_assoc()) {
        $bracket_columns[] = $col['Field'];
    }
    
    // Build query based on available columns
    $select_fields = [];
    if (in_array('tname', $bracket_columns)) {
        $select_fields[] = "tname as team_name";
    }
    if (in_array('team_name', $bracket_columns)) {
        $select_fields[] = "team_name";
    }
    if (in_array('team_id', $bracket_columns)) {
        $select_fields[] = "team_id";
    }
    if (in_array('player_id', $bracket_columns)) {
        $select_fields[] = "player_id";
    }
    if (in_array('solo_id', $bracket_columns)) {
        $select_fields[] = "solo_id";
    }
    if (in_array('duo_id', $bracket_columns)) {
        $select_fields[] = "duo_id";
    }
    if (in_array('squad_id', $bracket_columns)) {
        $select_fields[] = "squad_id";
    }
    
    if (!empty($select_fields)) {
        $field_list = implode(', ', $select_fields);
        $bracket_sql = "SELECT DISTINCT $field_list FROM brackets WHERE tournament_id = ?";
        
        $bracket_stmt = $conn->prepare($bracket_sql);
        if ($bracket_stmt) {
            $bracket_stmt->bind_param("i", $tournament_id);
            $bracket_stmt->execute();
            $bracket_result = $bracket_stmt->get_result();
            
            $counter = 1;
            while ($row = $bracket_result->fetch_assoc()) {
                // Determine team_id from available columns
                $team_id = 0;
                
                if ($match_type == 'solo') {
                    if (isset($row['solo_id'])) $team_id = $row['solo_id'];
                    elseif (isset($row['player_id'])) $team_id = $row['player_id'];
                    elseif (isset($row['team_id'])) $team_id = $row['team_id'];
                } 
                elseif ($match_type == 'duo') {
                    if (isset($row['duo_id'])) $team_id = $row['duo_id'];
                    elseif (isset($row['team_id'])) $team_id = $row['team_id'];
                }
                elseif ($match_type == 'squad') {
                    if (isset($row['squad_id'])) $team_id = $row['squad_id'];
                    elseif (isset($row['team_id'])) $team_id = $row['team_id'];
                }
                else {
                    // Default fallback
                    if (isset($row['team_id'])) $team_id = $row['team_id'];
                    elseif (isset($row['player_id'])) $team_id = $row['player_id'];
                    elseif (isset($row['solo_id'])) $team_id = $row['solo_id'];
                    elseif (isset($row['duo_id'])) $team_id = $row['duo_id'];
                    elseif (isset($row['squad_id'])) $team_id = $row['squad_id'];
                    else $team_id = $counter;
                }
                
                // Get team info with logo
                $team_info = getTeamInfo($conn, $team_id, $tournament_id, $match_type);
                
                $team_name = '';
                if (isset($row['team_name'])) $team_name = $row['team_name'];
                elseif (isset($row['tname'])) $team_name = $row['tname'];
                else $team_name = $team_info['name']; // Use from team info
                
                $participants[] = [
                    'id' => $counter,
                    'team_id' => $team_id,
                    'team_name' => $team_name,
                    'team_logo' => $team_info['logo'],
                    'total_score' => 0,
                    'total_kills' => 0,
                    'average_placement' => 0,
                    'matches_played' => 0,
                    'source' => 'brackets'
                ];
                $counter++;
            }
            $bracket_stmt->close();
        }
    }
}

// METHOD 3: Check leaderboard table - FIXED TO USE CORRECT COLUMN NAMES
if (empty($participants)) {
    $leaderboard_check = $conn->query("SHOW TABLES LIKE 'leaderboard'");
    if ($leaderboard_check && $leaderboard_check->num_rows > 0) {
        // Try to join with registration tables based on match_type
        if ($match_type == 'solo') {
            $lb_sql = "SELECT lb.id, lb.player_id as team_id, lb.kills, lb.placement, 
                              sr.player_name as team_name
                       FROM leaderboard lb
                       LEFT JOIN solo_registration sr ON lb.player_id = sr.solo_id
                       WHERE lb.tournament_id = ?";
        } elseif ($match_type == 'duo') {
            $lb_sql = "SELECT lb.id, lb.team_id, lb.kills, lb.placement,
                              dr.team_name
                       FROM leaderboard lb
                       LEFT JOIN duo_registration dr ON lb.team_id = dr.duo_id
                       WHERE lb.tournament_id = ?";
        } elseif ($match_type == 'squad') {
            $lb_sql = "SELECT lb.id, lb.team_id, lb.kills, lb.placement,
                              sq.team_name
                       FROM leaderboard lb
                       LEFT JOIN squad_registration sq ON lb.team_id = sq.squad_id
                       WHERE lb.tournament_id = ?";
        } else {
            $lb_sql = "SELECT lb.id, lb.player_id as team_id, lb.kills, lb.placement,
                              'Player ' || lb.player_id as team_name
                       FROM leaderboard lb
                       WHERE lb.tournament_id = ?";
        }
        
        $lb_stmt = $conn->prepare($lb_sql);
        if ($lb_stmt) {
            $lb_stmt->bind_param("i", $tournament_id);
            $lb_stmt->execute();
            $lb_result = $lb_stmt->get_result();
            
            while ($row = $lb_result->fetch_assoc()) {
                $team_id = $row['team_id'] ?? 0;
                $team_name = $row['team_name'] ?? 'Team ' . $team_id;
                
                // Get logo from registration table
                $team_info = getTeamInfo($conn, $team_id, $tournament_id, $match_type);
                $logo_path = $team_info['logo'];
                
                // Calculate score
                $placement = $row['placement'] ?? 0;
                $kills = $row['kills'] ?? 0;
                $total_score = calculateTotalScore($placement, $kills);
                
                $participants[] = [
                    'id' => $row['id'],
                    'team_id' => $team_id,
                    'team_name' => $team_name,
                    'team_logo' => $logo_path,
                    'total_score' => $total_score,
                    'total_kills' => $kills,
                    'average_placement' => $placement,
                    'matches_played' => 1,
                    'source' => 'leaderboard'
                ];
            }
            $lb_stmt->close();
        }
    }
}

// METHOD 4: Check registration tables - DIRECT FETCH FROM SOLO REGISTRATION
if (empty($participants)) {
    if ($match_type == 'solo') {
        $reg_sql = "SELECT solo_id as team_id, player_name as team_name FROM solo_registration WHERE tournament_id = ?";
    } elseif ($match_type == 'duo') {
        $reg_sql = "SELECT duo_id as team_id, team_name FROM duo_registration WHERE tournament_id = ?";
    } elseif ($match_type == 'squad') {
        $reg_sql = "SELECT squad_id as team_id, team_name FROM squad_registration WHERE tournament_id = ?";
    } else {
        $reg_sql = "SELECT solo_id as team_id, player_name as team_name FROM solo_registration WHERE tournament_id = ?";
    }
    
    $reg_stmt = $conn->prepare($reg_sql);
    if ($reg_stmt) {
        $reg_stmt->bind_param("i", $tournament_id);
        $reg_stmt->execute();
        $reg_result = $reg_stmt->get_result();
        
        $counter = 1;
        while ($row = $reg_result->fetch_assoc()) {
            $team_id = $row['team_id'];
            $team_name = $row['team_name'] ?? 'Player ' . $team_id;
            
            // Get team info with logo
            $team_info = getTeamInfo($conn, $team_id, $tournament_id, $match_type);
            
            $participants[] = [
                'id' => $counter,
                'team_id' => $team_id,
                'team_name' => $team_name,
                'team_logo' => $team_info['logo'],
                'total_score' => 0,
                'total_kills' => 0,
                'average_placement' => 0,
                'matches_played' => 0,
                'source' => 'registration'
            ];
            $counter++;
        }
        $reg_stmt->close();
    }
}

// Get match history from match_results table
$check_match_results = $conn->query("SHOW TABLES LIKE 'match_results'");
if ($check_match_results && $check_match_results->num_rows > 0) {
    $history_stmt = $conn->prepare("
        SELECT DISTINCT match_number 
        FROM match_results 
        WHERE tournament_id = ? 
        ORDER BY match_number
    ");
    if ($history_stmt) {
        $history_stmt->bind_param("i", $tournament_id);
        $history_stmt->execute();
        $history_result = $history_stmt->get_result();
        while ($row = $history_result->fetch_assoc()) {
            $match_history[] = $row['match_number'];
        }
        $history_stmt->close();
    }
}

// Calculate ranks
usort($participants, function($a, $b) {
    return $b['total_score'] - $a['total_score'];
});

$rank = 1;
$prev_score = null;
$prev_rank = 0;
foreach ($participants as &$participant) {
    if ($participant['total_score'] !== $prev_score) {
        $participant['rank'] = $rank;
        $prev_rank = $rank;
    } else {
        $participant['rank'] = $prev_rank; // Tie handling
    }
    $prev_score = $participant['total_score'];
    $rank++;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Scoreboard - <?php echo htmlspecialchars($tournament_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
/* ===== Container ===== */
.scoreboard-container { 
    max-width: 100%; 
    margin: 20px auto; 
    background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
    border-radius: 12px; 
    box-shadow: 0 8px 30px rgba(0,0,0,0.6); 
    padding: 30px; 
    color: #000000ff; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    border: 1px solid rgba(255,255,255,0.1);
}

/* ===== Header ===== */
.scoreboard-header { 
    text-align: center; 
    margin-bottom: 30px; 
    padding: 20px;
    border-bottom: 3px solid #ff7f50;
    background: rgba(0,0,0,0.2);
    border-radius: 10px;
}
.scoreboard-header h2 { 
    font-size: 2.4rem; 
    margin-bottom: 10px; 
    color: #ff7f50; 
    text-shadow: 2px 2px 5px rgba(0,0,0,0.7);
    font-weight: 700;
}

/* ===== Tournament Info ===== */
.tournament-info {
    color: white;
    background: linear-gradient(to right, #34495e, #5f6a7d);
    padding: 15px;
    border-radius: 12px;
    margin-bottom: 20px;
    border: 1px solid #708090;
}

/* ===== Scoreboard Table ===== */
.scoreboard-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 25px; 
    border-radius: 10px; 
    overflow: hidden; 
}
.scoreboard-table thead { 
    background: linear-gradient(to right, #1abc9c, #16a085);
    color: #fff; 
}
.scoreboard-table th, .scoreboard-table td { 
    padding: 12px 10px; 
    text-align: center; 
    vertical-align: middle; 
    border-bottom: 1px solid rgba(255,255,255,0.1); 
    vertical-align: middle;
}
.scoreboard-table tbody tr:nth-child(even) { 
    background: rgba(255,255,255,0.05); 
}
.scoreboard-table tbody tr:hover { 
    background: rgba(255, 127, 80, 0.15); 
}

/* ===== Rank Badges ===== */
.rank-badge {
    display: inline-block;
    width: 36px;
    height: 36px;
    line-height: 36px;
    border-radius: 50%;
    color: white;
    font-weight: bold;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
}
.rank-1 { background: linear-gradient(135deg, #ffd700, #ffae00); color: #000; }
.rank-2 { background: linear-gradient(135deg, #c0c0c0, #a0a0a0); color: #000; }
.rank-3 { background: linear-gradient(135deg, #cd7f32, #b56927); color: #000; }
.rank-4-plus { background: linear-gradient(135deg, #3498db, #2980b9); color: white; }

/* ===== Stats Badges ===== */
.stats-badge, 
.kills-badge,
.avg-badge,
.matches-badge,
.score-badge {
    display: inline-flex;     /* inline-flex keeps it centered inside <td> */
    align-items: center;
    justify-content: center;
    width: 60px;              /* background width */
    height: 35px;             /* background height */
    border-radius: 25px;
    font-size: 0.85rem;       /* keep text same */
    font-weight: 600;
    text-align: center;
    color: #fff;
    text-shadow: 0 1px 3px rgba(0,0,0,0.4);
    box-shadow: 0 0 8px rgba(0,0,0,0.2);
    margin: 0 auto;           /* removes extra margin shift */
    transition: all 0.2s ease-in-out;
}
/* ===== Badge Colors (Professional & Game-themed) ===== */
.kills-badge { 
    background: linear-gradient(135deg, #ff1900ff, #ff1900ff); /* clean red */
    color: #fff;
    box-shadow: 0 0 8px rgba(231,76,60,0.5);
}

.avg-badge { 
    background: linear-gradient(135deg, #8e44ad, #71368a); /* deep purple */
    color: #fff;
    box-shadow: 0 0 8px rgba(142,68,173,0.5);
}

.matches-badge { 
    background: linear-gradient(135deg, #f39c12, #d35400); /* orange */
    color: #fff;
    box-shadow: 0 0 8px rgba(243,156,18,0.5);
}

.score-badge { 
    background: linear-gradient(135deg, #27ae60, #1e8449); /* green */
    color: #fff;
    box-shadow: 0 0 8px rgba(39,174,96,0.5);
}

/* ===== Hover effect ===== */
.stats-badge:hover {
    transform: scale(1.05);
    box-shadow: 0 0 12px rgba(0,0,0,0.3);
}

/* ===== Match History ===== */
.match-history {
    background: linear-gradient(to right, #34495e, #4b6584);
    padding: 15px;
    border-radius: 12px;
    margin-top: 20px;
    border: 1px solid #4a6572;
}

/* ===== Scoring Info ===== */
.scoring-info {
    background: linear-gradient(to right, #16a085, #1abc9c);
    color: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
}

/* ===== Data Source Badge ===== */
.data-source-badge {
    font-size: 0.7rem;
    padding: 3px 6px;
    border-radius: 3px;
    background: #555;
    color: #fff;
}

/* ===== Team Info ===== */
.team-logo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ff7f50;
    margin-right: 10px;
    vertical-align: middle;
}
.team-info {
    display: flex;
    align-items: center;
    justify-content: flex-start;
}
.team-name {
    font-weight: bold;
    font-size: 1.1rem;
    color: #000000ff;
}
.team-name small {
    color: #ccc;
    font-size: 0.8rem;
}
</style>

</head>
<body>
    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Tournament Scoreboard</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>

<div class="container-fluid py-4">
    <div class="scoreboard-container">
        <div class="scoreboard-header">
            <h2><i class="bi bi-trophy-fill"></i> <?php echo htmlspecialchars($tournament_name); ?></h2>
            <p>
                <span class="badge bg-dark me-2">ID: #<?php echo $tournament_id; ?></span>
                <span class="badge bg-warning me-2"><?php echo strtoupper($match_type); ?></span>
                <span class="badge bg-info">Match #<?php echo $current_match_number; ?></span>
            </p>
        </div>

        <!-- Scoring System Info -->
        <div class="scoring-info">
            <h5><i class="bi bi-calculator"></i> Scoring System</h5>
            <div class="row">
                 <div class="col-md-4">
                    <small style="display: block; text-align: left;">Kills: 10 points each</small>
                </div>
                <div class="col-md-8">
                    <small style="display: block; text-align: right;">1st:100&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;2nd:80&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;3rd:65&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;4th:55&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;5th:50&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;6th-10th:45-25&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;11th+:15-5</small> 
                </div>

            </div>
        </div>

        <!-- Tournament Info -->
        <div class="tournament-info">
            <div class="row text-center">
                <div class="col-md-3 mb-2">
                    <strong>Participants:</strong> <?php echo count($participants); ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>Matches:</strong> <?php echo count($match_history); ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>Next Match:</strong> #<?php echo $current_match_number; ?>
                </div>
                <div class="col-md-3 mb-2">
                    <strong>Status:</strong> <span class="badge bg-success"></span>
<button type="button" class="btn btn-sm btn-danger ms-2" onclick="openLiveStreamPopup()">
    <i class="bi bi-play-circle"></i> Watch Live
</button>

<!-- Hidden container for stream content -->
<div id="streamContent" style="display: none;">
    <?php
    if (!empty($tournaments)) {
        foreach ($tournaments as $tournament) {
            $tournamentName = htmlspecialchars($tournament['tname']);
            $channelName = htmlspecialchars($tournament['channel_name']);
            $startDate = $tournament['sdate']; 
            $startTime = $tournament['stime']; 
            $tournamentDateTime = $startDate . ' ' . $startTime;
            $tournamentTimestamp = strtotime($tournamentDateTime);
            $currentTimestamp = time();

            if ($currentTimestamp >= $tournamentTimestamp) {
    ?>
    <div class="stream-popup">
        <div class="stream-header">
            <h3><?php echo $tournamentName; ?></h3>
            <span class="live-indicator">
                <span class="pulse"></span> LIVE
            </span>
        </div>
        <div class="stream-frame">
            <iframe 
                src="https://player.twitch.tv/?channel=<?php echo urlencode($channelName); ?>&parent=localhost&autoplay=true" 
                frameborder="0" 
                allowfullscreen="true" 
                scrolling="no" 
                height="540" 
                width="960"
                id="twitchStream">
            </iframe>
        </div>
        <div class="stream-controls">
            <button onclick="fullscreenStream()" class="btn-fullscreen">
                <i class="bi bi-arrows-fullscreen"></i> Fullscreen
            </button>
            <a href="https://www.twitch.tv/<?php echo urlencode($channelName); ?>" 
               target="_blank" 
               class="btn-twitch">
                <i class="bi bi-twitch"></i> Open in Twitch
            </a>
            <button onclick="closeStreamPopup()" class="btn-close">
                <i class="bi bi-x-circle"></i> Close
            </button>
        </div>
    </div>
    <?php
            }
        }
    }
    ?>
</div>

                </div>
            </div>
        </div>

        <!-- Leaderboard -->
        <h4 class="mb-3" style="color: #ff6f61;">
            <i class="bi bi-list-ol"></i> Tournament Standings
        </h4>
        
        <?php if (empty($participants)): ?>
        <div class="alert alert-warning text-center">
            <i class="bi bi-exclamation-triangle"></i> No participants found for this tournament.
            <div class="mt-2">
                <a href="tournaments.php" class="btn btn-sm btn-secondary">Browse Tournaments</a>
            </div>
        </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="scoreboard-table">
                <thead>
                    <tr>
                        <th width="10%">Rank</th>
                        <th width="35%">Team / Player</th>
                        <th width="15%">Matches</th>
                        <th width="15%">Kills</th>
                        <th width="15%">Avg. Place</th>
                        <th width="15%">Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($participants as $p): ?>
                    <?php
                    $rank_class = 'rank-4-plus';
                    if ($p['rank'] == 1) $rank_class = 'rank-1';
                    elseif ($p['rank'] == 2) $rank_class = 'rank-2';
                    elseif ($p['rank'] == 3) $rank_class = 'rank-3';
                    
                    // Ensure logo path is correct
                    $team_logo = isset($p['team_logo']) && !empty($p['team_logo']) ? $p['team_logo'] : 'img/default-logo.png';
                    if (!file_exists($team_logo) && strpos($team_logo, 'http') !== 0 && strpos($team_logo, '//') !== 0) {
                        $team_logo = 'img/default-logo.png';
                    }
                    ?>
                    <tr>
                        <td>
                            <span class="rank-badge <?php echo $rank_class; ?>">
                                <?php echo $p['rank']; ?>
                            </span>
                        </td>
                        <td style="text-align: left;">
                            <div class="team-info">
                                <img src="<?php echo htmlspecialchars($team_logo); ?>" alt="<?php echo htmlspecialchars($p['team_name']); ?>" class="team-logo" 
                                     onerror="this.onerror=null; this.src='img/default-logo.png';">
                                <div>
                                    <div class="team-name"><?php echo htmlspecialchars($p['team_name']); ?></div>
                                    <?php if (isset($p['source'])): ?>
                                    <small class="data-source-badge"><?php echo $p['source']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="matches-badge">
                                <?php echo $p['matches_played']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="kills-badge">
                                <?php echo $p['total_kills']; ?>
                            </span>
                        </td>
                        <td>
                            <span class="avg-badge">
                                <?php echo number_format($p['average_placement'], 1); ?>
                            </span>
                        </td>
                        <td>
                            <span class="score-badge">
                                <?php echo $p['total_score']; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="text-center text-muted mt-3">
            <i class="bi bi-clock"></i> Updated: <?php echo date('g:i a'); ?> | 
            Auto-refresh in <span id="refresh-timer">30</span>s
        </div>
        <?php endif; ?>

        <!-- Match History -->
        <?php if (!empty($match_history)): ?>
        <div class="match-history">
            <h5><i class="bi bi-clock-history"></i> Match History</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($match_history as $match): ?>
                <span class="badge bg-info" style="font-size: 0.9rem;">
                    Match #<?php echo $match; ?>
                </span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="d-flex justify-content-between mt-4 pt-3 border-top border-secondary">
            <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                <i class="bi bi-arrow-left"></i> Back
            </button>
            
            <div>
                <?php if (isset($_SESSION['isSignin']) && $_SESSION['isSignin']): ?>
                <a href="update_br_leaderboard.php?tournament_id=<?php echo $tournament_id; ?>" class="btn btn-warning">
                    <i class="bi bi-pencil-square"></i> Admin Panel
                </a>
                <?php endif; ?>
                
                <button type="button" class="btn btn-success ms-2" onclick="window.location.reload()">
                    <i class="bi bi-arrow-clockwise"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-refresh timer
let timer = 30;
const timerElement = document.getElementById('refresh-timer');

function updateTimer() {
    if (timerElement) {
        timer--;
        timerElement.textContent = timer;
        
        if (timer <= 0) {
            window.location.reload();
        } else {
            setTimeout(updateTimer, 1000);
        }
    }
}

// Start timer if timer element exists
if (timerElement) {
    setTimeout(updateTimer, 1000);
}

// Handle image loading errors
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.team-logo');
    images.forEach(img => {
        img.onerror = function() {
            this.src = 'img/default-logo.png';
        };
    });
});
</script>
<script>
function openLiveStreamPopup() {
    // Create popup window
    const streamWindow = window.open('', 'LiveStreamPopup', 
        'width=1000,height=700,scrollbars=no,resizable=yes,toolbar=no,menubar=no,location=no');
    
    // Get stream content
    const streamContent = document.getElementById('streamContent').innerHTML;
    
    // Write content to popup
    streamWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Live Stream - Tournament</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
            <style>
                body { margin: 0; padding: 0; background: #000; }
                .stream-popup { 
                    background: #1a1a1a; 
                    color: white; 
                    font-family: Arial, sans-serif;
                }
                .stream-header { 
                    background: #9146ff; 
                    padding: 15px 20px; 
                    display: flex; 
                    justify-content: space-between;
                    align-items: center;
                }
                .live-indicator { 
                    background: red; 
                    padding: 5px 10px; 
                    border-radius: 5px;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 8px;
                }
                .pulse {
                    width: 10px;
                    height: 10px;
                    background: white;
                    border-radius: 50%;
                    animation: pulse 1.5s infinite;
                }
                @keyframes pulse {
                    0% { opacity: 1; }
                    50% { opacity: 0.5; }
                    100% { opacity: 1; }
                }
                .stream-frame {
                    padding: 20px;
                    background: #000;
                }
                .stream-controls {
                    padding: 15px 20px;
                    background: #2a2a2a;
                    display: flex;
                    gap: 10px;
                    justify-content: center;
                }
                .btn-fullscreen, .btn-twitch, .btn-close {
                    padding: 8px 15px;
                    border: none;
                    border-radius: 5px;
                    cursor: pointer;
                    font-weight: bold;
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
                .btn-fullscreen { background: #9146ff; color: white; }
                .btn-twitch { background: #6441a5; color: white; text-decoration: none; }
                .btn-close { background: #dc3545; color: white; }
                iframe { border: 2px solid #9146ff; border-radius: 5px; }
            </style>
        </head>
        <body>
            ${streamContent}
            <script>
                function fullscreenStream() {
                    const iframe = document.getElementById('twitchStream');
                    if (iframe.requestFullscreen) {
                        iframe.requestFullscreen();
                    } else if (iframe.webkitRequestFullscreen) {
                        iframe.webkitRequestFullscreen();
                    } else if (iframe.msRequestFullscreen) {
                        iframe.msRequestFullscreen();
                    }
                }
                
                function closeStreamPopup() {
                    window.close();
                }
                
                // Auto-close when browser/tab is closed
                window.addEventListener('beforeunload', function() {
                    const iframe = document.getElementById('twitchStream');
                    if (iframe) {
                        iframe.src = ''; // Stop the stream
                    }
                });
            <\/script>
        </body>
        </html>
    `);
    
    streamWindow.document.close();
}
</script>
</body>
</html>

<?php include('footer.php'); ?>
<?php
include('header.php');

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_GET['tournament_id']) || !is_numeric($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}

$tournament_id = intval($_GET['tournament_id']);
include('config.php');

// Check if TournamentScoringSystem class exists
$scoringSystem = null;
if (file_exists('TournamentScoringSystem.php')) {
    require_once('TournamentScoringSystem.php');
    $scoringSystem = new TournamentScoringSystem($conn);
}

// ==================== DATABASE SETUP ====================
function ensureDatabaseTables($conn, $tournament_id, $match_type) {
    // Check/create tournaments table
    $conn->query("CREATE TABLE IF NOT EXISTS tournaments (
        id INT PRIMARY KEY,
        tname VARCHAR(255) DEFAULT 'Tournament',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Check/create tournament_participants table
    $conn->query("CREATE TABLE IF NOT EXISTS tournament_participants (
        id INT PRIMARY KEY AUTO_INCREMENT,
        tournament_id INT NOT NULL,
        team_id INT NOT NULL,
        total_score INT DEFAULT 0,
        total_kills INT DEFAULT 0,
        average_placement DECIMAL(5,2) DEFAULT 0.0,
        matches_played INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_participation (tournament_id, team_id)
    )");
    
    // Check/create match_results table
    $conn->query("CREATE TABLE IF NOT EXISTS match_results (
        id INT PRIMARY KEY AUTO_INCREMENT,
        tournament_id INT NOT NULL,
        match_number INT NOT NULL,
        participant_id INT NOT NULL,
        placement INT NOT NULL,
        kills INT DEFAULT 0,
        score INT DEFAULT 0,
        calculated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_match_result (tournament_id, match_number, participant_id)
    )");
    
    // Ensure tournament exists
    $check = $conn->prepare("SELECT id FROM tournaments WHERE id = ?");
    $check->bind_param("i", $tournament_id);
    $check->execute();
    $check->store_result();
    
    if ($check->num_rows == 0) {
        $insert = $conn->prepare("INSERT INTO tournaments (id, tname) VALUES (?, ?)");
        $tournament_name = "Tournament $tournament_id";
        $insert->bind_param("is", $tournament_id, $tournament_name);
        $insert->execute();
        $insert->close();
    }
    $check->close();
    
    return true;
}

// ==================== MAIN CODE ====================

// Get tournament name
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

// Get match type from brackets
$match_type = 'solo';
$current_match_number = 1;

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

// Check for match_number column
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

// Setup database
ensureDatabaseTables($conn, $tournament_id, $match_type);

// ==================== FORM HANDLING ====================

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_match'])) {
    $placements = $_POST['placement'] ?? [];
    $kills = $_POST['kills'] ?? [];
    $participant_ids = $_POST['participant_id'] ?? [];
    
    // Validate placements
    $placement_values = array_values($placements);
    if (count($placement_values) !== count(array_unique($placement_values))) {
        $_SESSION['error'] = "Duplicate placements detected!";
    } else {
        try {
            // Start transaction
            $conn->begin_transaction();
            
            // TEMPORARILY DISABLE UNIQUE CHECKS
            $conn->query("SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0");
            $conn->query("SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0");
            
            // 1. First, delete ALL existing results for this match
            $delete_stmt = $conn->prepare("DELETE FROM match_results WHERE tournament_id = ? AND match_number = ?");
            $delete_stmt->bind_param("ii", $tournament_id, $current_match_number);
            $delete_stmt->execute();
            $deleted_count = $delete_stmt->affected_rows;
            $delete_stmt->close();
            
            // 2. Insert all new results
            $insert_success = true;
            $inserted_count = 0;
            
            foreach ($participant_ids as $index => $participant_id) {
                $placement = intval($placements[$index]);
                $kill_count = intval($kills[$index]);
                $score = ($kill_count * 10) + max(0, 100 - $placement);
                
                // Check if participant exists
                $check_participant = $conn->prepare("SELECT id FROM tournament_participants WHERE id = ?");
                $check_participant->bind_param("i", $participant_id);
                $check_participant->execute();
                $check_result = $check_participant->get_result();
                
                if ($check_result->num_rows > 0) {
                    // Use INSERT IGNORE to avoid duplicate errors
                    $stmt = $conn->prepare("
                        INSERT IGNORE INTO match_results 
                        (tournament_id, match_number, participant_id, placement, kills, score)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("iiiidi", 
                        $tournament_id, 
                        $current_match_number,
                        $participant_id,
                        $placement,
                        $kill_count,
                        $score
                    );
                    
                    if ($stmt->execute()) {
                        $inserted_count++;
                    } else {
                        // If INSERT IGNORE fails, try INSERT with ON DUPLICATE KEY UPDATE
                        $stmt2 = $conn->prepare("
                            INSERT INTO match_results 
                            (tournament_id, match_number, participant_id, placement, kills, score)
                            VALUES (?, ?, ?, ?, ?, ?)
                            ON DUPLICATE KEY UPDATE
                            placement = VALUES(placement),
                            kills = VALUES(kills),
                            score = VALUES(score)
                        ");
                        $stmt2->bind_param("iiiidi", 
                            $tournament_id, 
                            $current_match_number,
                            $participant_id,
                            $placement,
                            $kill_count,
                            $score
                        );
                        
                        if ($stmt2->execute()) {
                            $inserted_count++;
                        }
                        $stmt2->close();
                    }
                    $stmt->close();
                }
                $check_participant->close();
            }
            
            // 3. RE-ENABLE UNIQUE CHECKS
            $conn->query("SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");
            $conn->query("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
            
            // 4. Update participant totals
            $update_stmt = $conn->prepare("
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
                    tp.matches_played = COALESCE(mr.matches_played, 0)
                WHERE tp.tournament_id = ?
            ");
            $update_stmt->bind_param("ii", $tournament_id, $tournament_id);
            $update_stmt->execute();
            $update_stmt->close();
            
            // 5. Increment match number if this is a new match
            $check_existing = $conn->prepare("SELECT COUNT(*) as count FROM match_results WHERE tournament_id = ? AND match_number > ?");
            $check_existing->bind_param("ii", $tournament_id, $current_match_number);
            $check_existing->execute();
            $existing_result = $check_existing->get_result();
            $existing_row = $existing_result->fetch_assoc();
            $has_future_matches = $existing_row['count'] > 0;
            $check_existing->close();
            
            if (!$has_future_matches) {
                $next_match = $current_match_number + 1;
                $update_match = $conn->prepare("UPDATE brackets SET match_number = ? WHERE tournament_id = ?");
                if ($update_match) {
                    $update_match->bind_param("ii", $next_match, $tournament_id);
                    $update_match->execute();
                    $update_match->close();
                }
            }
            
            $conn->commit();
            
            $_SESSION['success'] = "Match #$current_match_number results saved successfully! (Inserted: $inserted_count records)";
            header("Location: update_br_leaderboard.php?tournament_id=$tournament_id");
            exit();
            
        } catch (Exception $e) {
            // RE-ENABLE UNIQUE CHECKS even if error occurs
            $conn->query("SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS");
            $conn->query("SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS");
            $conn->rollback();
            $_SESSION['error'] = "Error saving match results: " . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['recalculate_all'])) {
    try {
        $conn->begin_transaction();
        
        // First, clear all scores
        $clear_stmt = $conn->prepare("
            UPDATE tournament_participants 
            SET total_score = 0, 
                total_kills = 0, 
                average_placement = 0.0, 
                matches_played = 0 
            WHERE tournament_id = ?
        ");
        $clear_stmt->bind_param("i", $tournament_id);
        $clear_stmt->execute();
        $clear_stmt->close();
        
        // Then recalculate from match_results
        $update_stmt = $conn->prepare("
            UPDATE tournament_participants tp
            JOIN (
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
                tp.matches_played = COALESCE(mr.matches_played, 0)
            WHERE tp.tournament_id = ?
        ");
        $update_stmt->bind_param("ii", $tournament_id, $tournament_id);
        $update_stmt->execute();
        $update_stmt->close();
        
        $conn->commit();
        $_SESSION['success'] = "All scores recalculated successfully!";
        header("Location: update_br_leaderboard.php?tournament_id=$tournament_id");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = "Recalculation error: " . $e->getMessage();
    }
}

// ==================== FETCH PARTICIPANTS ====================

$participants = [];

// First try to get participants from tournament_participants
$check_table = $conn->query("SHOW TABLES LIKE 'tournament_participants'");
if ($check_table && $check_table->num_rows > 0) {
    $sql = "SELECT id, team_id, total_score, total_kills, average_placement, matches_played 
            FROM tournament_participants 
            WHERE tournament_id = ? 
            ORDER BY total_score DESC";
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $team_id = $row['team_id'];
            $team_name = '';
            
            // Get team/player name based on match type
            if ($match_type == 'solo') {
                $name_sql = "SELECT player_name FROM solo_registration WHERE solo_id = ? AND tournament_id = ?";
                $name_stmt = $conn->prepare($name_sql);
                $name_stmt->bind_param("ii", $team_id, $tournament_id);
                $name_stmt->execute();
                $name_result = $name_stmt->get_result();
                if ($name_row = $name_result->fetch_assoc()) {
                    $team_name = $name_row['player_name'];
                }
                $name_stmt->close();
            } elseif ($match_type == 'duo') {
                $name_sql = "SELECT team_name FROM duo_registration WHERE duo_id = ? AND tournament_id = ?";
                $name_stmt = $conn->prepare($name_sql);
                $name_stmt->bind_param("ii", $team_id, $tournament_id);
                $name_stmt->execute();
                $name_result = $name_stmt->get_result();
                if ($name_row = $name_result->fetch_assoc()) {
                    $team_name = $name_row['team_name'];
                }
                $name_stmt->close();
            } elseif ($match_type == 'squad') {
                $name_sql = "SELECT team_name FROM squad_registration WHERE squad_id = ? AND tournament_id = ?";
                $name_stmt = $conn->prepare($name_sql);
                $name_stmt->bind_param("ii", $team_id, $tournament_id);
                $name_stmt->execute();
                $name_result = $name_stmt->get_result();
                if ($name_row = $name_result->fetch_assoc()) {
                    $team_name = $name_row['team_name'];
                }
                $name_stmt->close();
            }
            
            $row['team_name'] = $team_name ?: 'Unknown';
            $participants[] = $row;
        }
        $stmt->close();
    }
}

// If no participants found, check old leaderboard
if (empty($participants)) {
    if ($match_type == 'solo') {
        $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, sr.player_name as team_name
                FROM leaderboard lb
                JOIN solo_registration sr ON lb.player_id = sr.solo_id
                WHERE lb.tournament_id = ?";
    } elseif ($match_type == 'duo') {
        $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, dr.team_name
                FROM leaderboard lb
                JOIN duo_registration dr ON lb.team_id = dr.duo_id
                WHERE lb.tournament_id = ?";
    } elseif ($match_type == 'squad') {
        $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, sq.team_name
                FROM leaderboard lb
                JOIN squad_registration sq ON lb.team_id = sq.squad_id
                WHERE lb.tournament_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $tournament_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $row['team_id'] = 0;
            $row['total_kills'] = $row['kills'];
            $row['average_placement'] = $row['placement'];
            $row['matches_played'] = 1;
            $row['team_name'] = $row['team_name'] ?? 'Unknown';
            $participants[] = $row;
        }
        $stmt->close();
    }
}

// Get match history
$match_history = [];
$history_stmt = $conn->prepare("SELECT DISTINCT match_number FROM match_results WHERE tournament_id = ? ORDER BY match_number");
if ($history_stmt) {
    $history_stmt->bind_param("i", $tournament_id);
    $history_stmt->execute();
    $history_result = $history_stmt->get_result();
    while ($row = $history_result->fetch_assoc()) {
        $match_history[] = $row['match_number'];
    }
    $history_stmt->close();
}

// Calculate ranks
$rank = 1;
$prev_score = null;
foreach ($participants as &$participant) {
    if ($participant['total_score'] !== $prev_score) {
        $participant['rank'] = $rank;
        $rank++;
    } else {
        $participant['rank'] = $rank - 1;
    }
    $prev_score = $participant['total_score'];
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Leaderboard - <?php echo htmlspecialchars($tournament_name); ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<style>
.lr-as-container { 
    max-width: 100%; 
    margin: 40px auto; 
    background: #1f1f2f; 
    border-radius: 12px; 
    box-shadow: 0 8px 20px rgba(0,0,0,0.3); 
    padding: 30px; 
    color: #0f0f0fff; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    font-size: 1.1rem; 
}
.lr-as-header { 
    text-align: center; 
    margin-bottom: 25px; 
    padding-bottom: 20px;
    border-bottom: 2px solid #ff6f61;
}
.lr-as-header h2 { 
    font-size: 2rem; 
    margin-bottom: 10px; 
    color: #ff6f61; 
}
.lr-as-header p { 
    font-size: 1rem; 
    color: #ccc; 
}
.match-info {
    background: #2c3e50;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}
.lr-as-table { 
    width: 100%; 
    border-collapse: collapse; 
    margin-bottom: 25px; 
    border-radius: 8px; 
    overflow: hidden; 
}
.lr-as-table thead { 
    background: #2c3e50; 
    color: #f4f4f9; 
}
.lr-as-table th, .lr-as-table td { 
    padding: 12px 15px; 
    text-align: center; 
    border-bottom: 1px solid #2c3e50; 
    vertical-align: middle;
}
.lr-as-table th { 
    font-weight: 600; 
    font-size: 0.95rem; 
}
.lr-as-table tbody tr:hover { 
    background: rgba(255, 111, 97, 0.1); 
}
.lr-as-table td input[type=number] { 
    width: 80px; 
    padding: 6px; 
    border-radius: 6px; 
    border: 1px solid #444; 
    text-align: center; 
    background: #2c3e50; 
    color: #f4f4f9; 
    font-weight: bold; 
}
.lr-as-table td input[type=number]:focus { 
    outline: none; 
    background: #34495e; 
    border-color: #ff6f61;
}
.placement-badge {
    background: #ff6f61;
    color: white;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.9rem;
}
.stats-badge {
    background: #3498db;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.8rem;
    margin: 0 2px;
}
.lr-as-submit { 
    text-align: center; 
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #2c3e50;
}
.lr-as-submit button { 
    background: #ff6f61; 
    color: #fff; 
    font-weight: bold; 
    padding: 12px 25px; 
    border-radius: 8px; 
    border: none; 
    cursor: pointer; 
    font-size: 1rem; 
    transition: all 0.3s;
    margin: 0 5px;
}
.lr-as-submit button:hover { 
    background: #e55a4c; 
    transform: translateY(-2px); 
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.btn-secondary {
    background: #6c757d;
}
.btn-secondary:hover {
    background: #5a6268;
}
.btn-warning {
    background: #ffc107;
    color: #000;
}
.btn-warning:hover {
    background: #e0a800;
}
</style>
<script>
function validatePlacements() {
    const placements = document.querySelectorAll('input[name^="placement"]');
    const values = Array.from(placements).map(input => parseInt(input.value) || 0);
    const uniqueValues = [...new Set(values)];
    
    if (values.length !== uniqueValues.length) {
        alert('Error: Duplicate placements detected!\nEach participant must have a unique placement.');
        return false;
    }
    
    // Check for valid range
    const max = placements.length;
    for (let value of values) {
        if (value < 1 || value > max) {
            alert(`Error: Placements must be between 1 and ${max}`);
            return false;
        }
    }
    
    return confirm(`Submit Match #<?php echo $current_match_number; ?> results?\nThis will update scores automatically.`);
}

function autoFillPlacements() {
    const placements = document.querySelectorAll('input[name^="placement"]');
    const participants = Array.from(placements).map((input, index) => ({
        index,
        value: parseInt(input.value) || 0,
        row: input.closest('tr')
    }));
    
    // Sort by current placement (or kills if tie)
    participants.sort((a, b) => {
        if (a.value === b.value) {
            const aKills = parseInt(a.row.querySelector('input[name^="kills"]').value) || 0;
            const bKills = parseInt(b.row.querySelector('input[name^="kills"]').value) || 0;
            return bKills - aKills; // Higher kills first
        }
        return a.value - b.value;
    });
    
    // Assign placements 1 to n
    participants.forEach((participant, idx) => {
        placements[participant.index].value = idx + 1;
    });
    
    updateAllEstimates();
    alert('Placements have been auto-filled based on current values!');
}

function calculateTotalScore(kills, placement) {
    return (kills * 10) + Math.max(0, 100 - placement);
}

function updateAllEstimates() {
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach((row, index) => {
        const kills = parseInt(row.querySelector('input[name^="kills"]').value) || 0;
        const placement = parseInt(row.querySelector('input[name^="placement"]').value) || 1;
        const score = calculateTotalScore(kills, placement);
        const scoreCell = row.querySelector('td:last-child');
        scoreCell.innerHTML = `<span class="badge ${score > 50 ? 'bg-success' : 'bg-secondary'}">${score}</span>`;
    });
}
</script>
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
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="lr-as-container">
        <div class="lr-as-header">
            <h2><i class="bi bi-trophy-fill"></i> <?php echo htmlspecialchars($tournament_name); ?></h2>
            <p>
                Tournament ID: <strong>#<?php echo $tournament_id; ?></strong> | 
                Match Type: <span class="badge bg-warning"><?php echo strtoupper($match_type); ?></span> | 
                Current Match: <span class="badge bg-info">#<?php echo $current_match_number; ?></span>
            </p>
        </div>

        <!-- Match Information -->
        <div class="match-info">
            <div class="row">
                <div class="col-md-3">
                    <strong>Total Participants:</strong> <?php echo count($participants); ?>
                </div>
                <div class="col-md-3">
                    <strong>Completed Matches:</strong> <?php echo count($match_history); ?>
                </div>
                <div class="col-md-3">
                    <strong>Next Match:</strong> #<?php echo $current_match_number; ?>
                </div>
                <div class="col-md-3">
                    <strong>Scoring System:</strong> <?php echo $scoringSystem ? 'Automated' : 'Manual'; ?>
                </div>
            </div>
        </div>

        <!-- Score Submission Form -->
        <form action="<?php echo $_SERVER['PHP_SELF'] . '?tournament_id=' . $tournament_id; ?>" method="POST" onsubmit="return validatePlacements();">
            <input type="hidden" name="submit_match" value="1">
            
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Submit Match #<?php echo $current_match_number; ?> Results</h4>
                <button type="button" class="btn btn-sm btn-secondary" onclick="autoFillPlacements()">
                    <i class="bi bi-sort-numeric-down"></i> Auto-fill Placements
                </button>
            </div>

            <table class="lr-as-table">
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Team / Player</th>
                        <th>Current Stats</th>
                        <th>Kills</th>
                        <th>Placement</th>
                        <th>Estimated Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($participants)): ?>
                    <tr>
                        <td colspan="6" class="text-center">
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle"></i> No participants found for this tournament.
                            </div>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $i=1; foreach($participants as $p): ?>
                    <tr>
                        <td>
                            <span class="placement-badge">#<?php echo $p['rank']; ?></span>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($p['team_name'] ?: 'Unknown'); ?></strong><br>
                        </td>
                        <td>
                            <span class="stats-badge"><?php echo $p['total_score']; ?> pts</span>
                            <span class="stats-badge" style="background:#2ecc71;"><?php echo $p['total_kills']; ?> kills</span><br>
                            <span class="stats-badge" style="background:#9b59b6;">Avg: <?php echo number_format($p['average_placement'], 1); ?></span>
                        </td>
                        <td>
                            <input type="number" 
                                   name="kills[]" 
                                   value="0" 
                                   min="0" 
                                   max="50"
                                   required
                                   oninput="updateAllEstimates()">
                        </td>
                        <td>
                            <input type="number" 
                                   name="placement[]" 
                                   value="<?php echo $i; ?>" 
                                   min="1" 
                                   max="<?php echo count($participants); ?>"
                                   required
                                   oninput="updateAllEstimates()">
                            <input type="hidden" name="participant_id[]" value="<?php echo $p['id'] ?? $i; ?>">
                        </td>
                        <td>
                            <span class="badge bg-secondary">0</span>
                        </td>
                    </tr>
                    <?php $i++; endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php if (!empty($participants)): ?>
            <div class="lr-as-submit">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-cloud-upload"></i> Submit Match Results
                </button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='leaderboard.php?tournament_id=<?php echo $tournament_id; ?>'">
                    <i class="bi bi-list-ol"></i> View Leaderboard
                </button>
                <button type="submit" name="recalculate_all" class="btn btn-warning" onclick="return confirm('Recalculate all scores from match history?');">
                    <i class="bi bi-arrow-clockwise"></i> Recalculate Scores
                </button>
            </div>
            <?php endif; ?>
        </form>

        <!-- Match History -->
        <?php if (!empty($match_history)): ?>
        <div class="match-info mt-4">
            <h5><i class="bi bi-clock-history"></i> Match History</h5>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($match_history as $match): ?>
                <span class="badge bg-info">Match #<?php echo $match; ?></span>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Initialize estimates on page load
document.addEventListener('DOMContentLoaded', function() {
    updateAllEstimates();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php include('footer.php'); ?>
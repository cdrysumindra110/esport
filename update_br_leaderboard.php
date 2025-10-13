<?php
// update_br_leaderboard.php
include_once('config.php');
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Fetch tournament matches
$tournament_id = 1; // change dynamically as needed

$matches_sql = "SELECT * FROM matches WHERE tournament_id = ? AND status = 'completed'";
$stmt = $conn->prepare($matches_sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$matches_result = $stmt->get_result();

// Initialize teams array
$teams = [];
$leaderboard_sql = "SELECT * FROM leaderboard WHERE tournament_id = ?";
$stmt2 = $conn->prepare($leaderboard_sql);
$stmt2->bind_param("i", $tournament_id);
$stmt2->execute();
$leaderboard_result = $stmt2->get_result();

while($row = $leaderboard_result->fetch_assoc()){
    $id = $row['team_id'] ?? $row['player_id']; // solo/team depending on match type
    if(!isset($teams[$id])){
        $teams[$id] = [
            'kills'=>0,
            'placement'=>0,
            'total_score'=>0,
            'matches_played'=>0,
        ];
    }
    $teams[$id]['kills'] += $row['kills'];
    $teams[$id]['placement'] += $row['placement'];
    $teams[$id]['total_score'] += $row['total_score'];
    $teams[$id]['matches_played']++;
}

// Optional: Sort teams by total_score descending
uasort($teams, function($a, $b){
    return $b['total_score'] <=> $a['total_score'];
});

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>BR Leaderboard</title>
<link rel="stylesheet" href="br_style.css">
<style>
/* Minimal inline style in case br_style.css is not loaded */
body{background:#12121f;color:#f4f4f9;font-family:sans-serif;padding:20px;}
.br-container{max-width:1200px;margin:0 auto;}
.br-header{text-align:center;margin-bottom:20px;}
.br-teams-table{width:100%;border-collapse:collapse;}
.br-teams-table th, .br-teams-table td{padding:10px;border-bottom:1px solid #333;}
.br-team-name{display:flex;align-items:center;gap:10px;}
.br-qualified{background-color:rgba(46,204,113,0.1);}
.br-eliminated{opacity:0.5;}
.br-position{text-align:center;font-weight:bold;}
.br-points{font-weight:bold;color:#ff6f61;}
</style>
</head>
<body>
<div class="br-container">
<header class="br-header">
    <h1>Battle Royale Leaderboard</h1>
    <p>Tournament ID: <?php echo $tournament_id; ?></p>
</header>

<table class="br-teams-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Team / Player</th>
            <th>Matches</th>
            <th>Kills</th>
            <th>Placement</th>
            <th>Total Score</th>
        </tr>
    </thead>
    <tbody>
        <?php $pos = 1; foreach($teams as $id => $team): 
            $cls = ($pos <= 3) ? 'br-qualified' : 'br-eliminated';
        ?>
        <tr class="<?php echo $cls; ?>">
            <td class="br-position"><?php echo $pos; ?></td>
            <td class="br-team-name"><?php echo "Team/Player #".$id; ?></td>
            <td><?php echo $team['matches_played']; ?></td>
            <td><?php echo $team['kills']; ?></td>
            <td><?php echo $team['placement']; ?></td>
            <td class="br-points"><?php echo $team['total_score']; ?></td>
        </tr>
        <?php $pos++; endforeach; ?>
    </tbody>
</table>
</div>
</body>
</html>

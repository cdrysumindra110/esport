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
include('config.php'); // DB connection

// Fetch match type from brackets
$match_type = 'solo';
$stmt = $conn->prepare("SELECT match_type FROM brackets WHERE tournament_id=? LIMIT 1");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows) {
    $match_type = $res->fetch_assoc()['match_type'];
}
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['kills'] as $participant_id => $kills) {
        $kills = intval($kills);
        $placement = intval($_POST['placement'][$participant_id]);
        $total_score = $kills * 10 + max(0, 100 - $placement); // Example scoring formula

        $sql = "UPDATE leaderboard SET kills=?, placement=?, total_score=? WHERE tournament_id=? AND id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iiiii", $kills, $placement, $total_score, $tournament_id, $participant_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . $_SERVER['PHP_SELF'] . "?tournament_id=" . $tournament_id);
    exit();
}

// Step 1: Insert missing leaderboard rows for all participants
$participants_to_insert = [];
if ($match_type == 'solo') {
    $sql = "SELECT solo_id, player_name FROM solo_registration WHERE tournament_id=?";
} elseif ($match_type == 'duo') {
    $sql = "SELECT duo_id, team_name FROM duo_registration WHERE tournament_id=?";
} elseif ($match_type == 'squad') {
    $sql = "SELECT squad_id, team_name FROM squad_registration WHERE tournament_id=?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $participants_to_insert[] = $row;
}
$stmt->close();

// Insert missing rows
foreach ($participants_to_insert as $p) {
    if ($match_type == 'solo') {
        $check_sql = "SELECT id FROM leaderboard WHERE tournament_id=? AND player_id=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $tournament_id, $p['solo_id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $insert_sql = "INSERT INTO leaderboard (tournament_id, match_type, player_id, kills, placement, total_score) VALUES (?, ?, ?, 0, 0, 0)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("isi", $tournament_id, $match_type, $p['solo_id']);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    } else { // duo or squad
        $team_id = $match_type == 'duo' ? $p['duo_id'] : $p['squad_id'];
        $check_sql = "SELECT id FROM leaderboard WHERE tournament_id=? AND team_id=?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("ii", $tournament_id, $team_id);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            $insert_sql = "INSERT INTO leaderboard (tournament_id, match_type, team_id, kills, placement, total_score) VALUES (?, ?, ?, 0, 0, 0)";
            $stmt_insert = $conn->prepare($insert_sql);
            $stmt_insert->bind_param("isi", $tournament_id, $match_type, $team_id);
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

// Step 2: Fetch leaderboard data to display
$participants = [];
if ($match_type == 'solo') {
    $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, sr.player_name AS team_name
            FROM leaderboard lb
            JOIN solo_registration sr ON lb.player_id = sr.solo_id
            WHERE lb.tournament_id=?";
} elseif ($match_type == 'duo') {
    $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, dr.team_name
            FROM leaderboard lb
            JOIN duo_registration dr ON lb.team_id = dr.duo_id
            WHERE lb.tournament_id=?";
} elseif ($match_type == 'squad') {
    $sql = "SELECT lb.id, lb.kills, lb.placement, lb.total_score, sq.team_name
            FROM leaderboard lb
            JOIN squad_registration sq ON lb.team_id = sq.squad_id
            WHERE lb.tournament_id=?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$res = $stmt->get_result();
while ($row = $res->fetch_assoc()) {
    $participants[] = $row;
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Update Leaderboard</title>
<style>
.lr-as-container { max-width: 100%; margin: 40px auto; background: #1f1f2f; border-radius: 12px; box-shadow: 0 8px 20px rgba(0,0,0,0.3); padding: 30px; color: #f4f4f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
.lr-as-header { text-align: center; margin-bottom: 25px; }
.lr-as-header h2 { font-size: 2rem; margin-bottom: 5px; color: #ff6f61; }
.lr-as-header p { font-size: 0.95rem; color: #ccc; }
.lr-as-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; border-radius: 8px; overflow: hidden; }
.lr-as-table thead { background: #2c3e50; color: #f4f4f9; }
.lr-as-table th, .lr-as-table td { padding: 12px 15px; text-align: center; border-bottom: 1px solid #2c3e50; }
.lr-as-table th { font-weight: 600; font-size: 0.95rem; }
.lr-as-table tbody tr:hover { background: rgba(255, 111, 97, 0.1); }
.lr-as-table td input[type=number] { width: 70px; padding: 5px; border-radius: 6px; border: none; text-align: center; background: #2c3e50; color: #f4f4f9; font-weight: bold; }
.lr-as-table td input[type=number]:focus { outline: none; background: #34495e; }
.lr-as-submit { text-align: center; }
.lr-as-submit button { background: #ff6f61; color: #fff; font-weight: bold; padding: 12px 25px; border-radius: 8px; border: none; cursor: pointer; font-size: 1rem; }
.lr-as-submit button:hover { background: #e55a4c; transform: translateY(-2px); }
</style>
</head>
<body>

<div class="lr-as-container">
    <div class="lr-as-header">
        <h2>Update Leaderboard</h2>
        <p>Tournament ID: <?php echo $tournament_id; ?> | Match Type: <?php echo ucfirst($match_type); ?></p>
    </div>

    <form action="<?php echo $_SERVER['PHP_SELF'] . '?tournament_id=' . $tournament_id; ?>" method="POST">
        <table class="lr-as-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Team / Player</th>
                    <th>Kills</th>
                    <th>Placement</th>
                </tr>
            </thead>
            <tbody>
                <?php $i=1; foreach($participants as $p): ?>
                <tr>
                    <td><?php echo $i++; ?></td>
                    <td><?php echo htmlspecialchars($p['team_name']); ?></td>
                    <td><input type="number" name="kills[<?php echo $p['id']; ?>]" value="<?php echo $p['kills']; ?>" min="0"></td>
                    <td><input type="number" name="placement[<?php echo $p['id']; ?>]" value="<?php echo $p['placement']; ?>" min="0"></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="lr-as-submit">
            <button type="submit">Update Leaderboard</button>
        </div>
    </form>
</div>

</body>
</html>

<?php include('footer.php'); ?>

<?php
include('header.php');

if (!isset($_GET['tournament_id']) || !is_numeric($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}

$tournament_id = intval($_GET['tournament_id']);
include('config.php'); // DB connection

// Fetch match type
$match_type = 'solo';
$stmt = $conn->prepare("SELECT match_type FROM brackets WHERE tournament_id=? LIMIT 1");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows) {
    $match_type = $res->fetch_assoc()['match_type'];
}
$stmt->close();

// Fetch leaderboard participants
$participants = [];
if ($match_type == 'solo') {
    $sql = "SELECT lb.id, sr.player_name AS team_name, lb.kills, lb.placement, lb.total_score
            FROM leaderboard lb
            JOIN solo_registration sr ON lb.player_id = sr.solo_id
            WHERE lb.tournament_id=?
            ORDER BY lb.total_score DESC";
} elseif ($match_type == 'duo') {
    $sql = "SELECT lb.id, dr.team_name, lb.kills, lb.placement, lb.total_score
            FROM leaderboard lb
            JOIN duo_registration dr ON lb.team_id = dr.duo_id
            WHERE lb.tournament_id=?
            ORDER BY lb.total_score DESC";
} elseif ($match_type == 'squad') {
    $sql = "SELECT lb.id, sq.team_name, lb.kills, lb.placement, lb.total_score
            FROM leaderboard lb
            JOIN squad_registration sq ON lb.team_id = sq.squad_id
            WHERE lb.tournament_id=?
            ORDER BY lb.total_score DESC";
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

<div class="container">
    <header>
        <h1>Battle Royale Championship 2025</h1>
        <p>Group Stage • Points Table & Match Results</p>
    </header>

    <div class="groups-container">
        <div class="group-card">
            <div class="group-header">Leaderboard</div>
            <table class="teams-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Team / Player</th>
                        <th>Kills</th>
                        <th>Placement</th>
                        <th>Total Score</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $rank = 1; foreach($participants as $p): ?>
                    <tr class="<?php echo ($rank <= 2 ? 'qualified' : 'eliminated'); ?>">
                        <td class="position"><?php echo $rank++; ?></td>
                        <td>
                            <div class="team-name">
                                <!-- Placeholder for flag/logo -->
                                <img src="https://via.placeholder.com/40" class="team-flag" alt="Team">
                                <?php echo htmlspecialchars($p['team_name']); ?>
                            </div>
                        </td>
                        <td><?php echo $p['kills']; ?></td>
                        <td><?php echo $p['placement']; ?></td>
                        <td class="points"><?php echo $p['total_score']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>

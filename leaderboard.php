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

// Fetch leaderboard participants including logo_path
if ($match_type == 'solo') {
    $sql = "SELECT lb.id, sr.player_name AS team_name, sr.logo_path, lb.kills, lb.placement
            FROM leaderboard lb
            JOIN solo_registration sr ON lb.player_id = sr.solo_id
            WHERE lb.tournament_id=?";
} elseif ($match_type == 'duo') {
    $sql = "SELECT lb.id, dr.team_name, dr.logo_path, lb.kills, lb.placement
            FROM leaderboard lb
            JOIN duo_registration dr ON lb.team_id = dr.duo_id
            WHERE lb.tournament_id=?";
} elseif ($match_type == 'squad') {
    $sql = "SELECT lb.id, sq.team_name, sq.logo_path, lb.kills, lb.placement
            FROM leaderboard lb
            JOIN squad_registration sq ON lb.team_id = sq.squad_id
            WHERE lb.tournament_id=?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$res = $stmt->get_result();

// Calculate total_score dynamically
$P_max = 100;
$K_weight = 10;

$participants = [];
while ($row = $res->fetch_assoc()) {
    $kills = intval($row['kills']);
    $placement = intval($row['placement']);
    $KS = $kills * $K_weight;
    $PS = max(0, $P_max - $placement);
    $row['total_score'] = $KS + $PS;

    // Ensure logo_path fallback if not uploaded
    if (empty($row['logo_path'])) {
        $row['logo_path'] = "https://via.placeholder.com/40";
    }

    $participants[] = $row;
}

$stmt->close();
$conn->close();

// Sort participants by total_score descending
usort($participants, function($a, $b){
    return $b['total_score'] <=> $a['total_score'];
});
?>

<style>
:root {
    --lbr-primary-color: #2c3e50;
    --lbr-secondary-color: #3498db;
    --lbr-accent-color: #e74c3c;
    --lbr-light-color: #ecf0f1;
    --lbr-dark-color: #2c3e50;
    --lbr-success-color: #2ecc71;
    --lbr-warning-color: #f39c12;
    --lbr-border-radius: 8px;
    --lbr-box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --lbr-transition: all 0.3s ease;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    background-color: #f5f7fa;
    color: var(--lbr-dark-color);
    line-height: 1.6;
    padding: 20px;
}

.lbr-container {
    /* max-width: 1200px; */
    margin: 0 auto;
}

.lbr-header {
    text-align: center;
    margin-bottom: 30px;
    padding: 20px;
    background: linear-gradient(135deg, var(--lbr-primary-color), var(--lbr-secondary-color));
    color: white;
    border-radius: var(--lbr-border-radius);
    box-shadow: var(--lbr-box-shadow);
}

.lbr-header h1 {
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.lbr-groups-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(100%, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.lbr-group-card {
    background: white;
    border-radius: var(--lbr-border-radius);
    box-shadow: var(--lbr-box-shadow);
    overflow: hidden;
    transition: var(--lbr-transition);
}

.lbr-group-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
}

.lbr-group-header {
    background: var(--lbr-primary-color);
    color: white;
    padding: 15px;
    text-align: center;
    font-weight: bold;
    font-size: 1.2rem;
}

.lbr-teams-table {
    width: 100%;
    border-collapse: collapse;
}

.lbr-teams-table th {
    background-color: var(--lbr-light-color);
    padding: 12px 8px;
    text-align: left;
    font-weight: 600;
    font-size: 0.9rem;
}

.lbr-teams-table td {
    padding: 12px 8px;
    border-bottom: 1px solid #eee;
}

.lbr-teams-table tr:last-child td {
    border-bottom: none;
}

.lbr-team-name {
    display: flex;
    align-items: center;
    gap: 10px;
}

.lbr-team-flag {
    width: 24px;
    height: 16px;
    border-radius: 2px;
    object-fit: cover;
}

.lbr-qualified {
    background-color: rgba(46, 204, 113, 0.1);
    font-weight: bold;
    border-left: 5px solid var(--lbr-success-color);
}

.lbr-eliminated {
    opacity: 0.6;
}

.lbr-position {
    font-weight: bold;
    width: 30px;
    text-align: center;
}

.lbr-points {
    font-weight: bold;
    color: var(--lbr-secondary-color);
}

@media (max-width: 768px) {
    .lbr-groups-container {
        grid-template-columns: 1fr;
    }
}

</style>

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Leaderboard</center>
            </h1>
          </div>
        </header>
      </article>  
    </main>

<div class="lbr-container">
    <header class="lbr-header">
        <h1>Battle Royale Championship 2025</h1>
        <p>Group Stage • Points Table & Match Results</p>
    </header>

    <div class="lbr-groups-container">
        <div class="lbr-group-card">
            <div class="lbr-group-header">Leaderboard</div>
            <table class="lbr-teams-table">
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
                    <tr class="<?php echo ($rank <= 2 ? 'lbr-qualified' : 'lbr-eliminated'); ?>">
                        <td class="lbr-position"><?php echo $rank++; ?></td>
                        <td>
                            <div class="lbr-team-name">
                                <img src="<?php echo !empty($p['logo_path']) ? htmlspecialchars($p['logo_path']) : 'https://via.placeholder.com/40'; ?>" 
                                    class="lbr-team-flag" 
                                    alt="<?php echo htmlspecialchars($p['team_name']); ?>">
                                <?php echo htmlspecialchars($p['team_name']); ?>
                            </div>
                        </td>
                        <td><?php echo $p['kills']; ?></td>
                        <td><?php echo $p['placement']; ?></td>
                        <td class="lbr-points"><?php echo $p['total_score']; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<?php include('footer.php'); ?>

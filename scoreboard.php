<?php
// scoreboard.php
session_start();
require_once 'config.php';
require_once 'algorithms/OptimizationEngine.php';
require_once 'scoreboard/ScoreboardEngine.php';

// Check if user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

$tournament_id = $_GET['tournament_id'] ?? $_GET['id'] ?? null;

if (!$tournament_id) {
    die("Error: Tournament ID required. Please specify a tournament.");
}

$tournament_id = intval($tournament_id);
$user_id = $_SESSION['user_id'];

// Get tournament info with enhanced statistics
$stmt = $conn->prepare("
    SELECT t.*, 
           u.uname as organizer_name,
           b.bracket_type,
           COUNT(DISTINCT CASE WHEN sr.id IS NOT NULL THEN sr.user_id END) as solo_count,
           COUNT(DISTINCT dr.duo_id) as duo_count,
           COUNT(DISTINCT sqr.squad_id) as squad_count,
           (SELECT COUNT(*) FROM match_results WHERE tournament_id = t.id AND match_status = 'completed') as completed_matches,
           (SELECT COUNT(*) FROM match_results WHERE tournament_id = t.id AND match_status = 'ongoing') as ongoing_matches
    FROM tournaments t
    LEFT JOIN users u ON t.user_id = u.id
    LEFT JOIN brackets b ON t.id = b.tournament_id
    LEFT JOIN solo_registration sr ON t.id = sr.tournament_id
    LEFT JOIN duo_registration dr ON t.id = dr.tournament_id
    LEFT JOIN squad_registration sqr ON t.id = sqr.tournament_id
    WHERE t.id = ?
    GROUP BY t.id
");

$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$tournament = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament) {
    die("Error: Tournament not found with ID: " . $tournament_id);
}

// Check if user has access (owner or admin)
$has_access = ($tournament['user_id'] == $user_id) || ($_SESSION['is_admin'] ?? false);
if (!$has_access) {
    // Check if user is a participant
    $check_participant = $conn->prepare("
        SELECT 1 FROM solo_registration WHERE tournament_id = ? AND user_id = ?
        UNION
        SELECT 1 FROM duo_players dp 
        JOIN duo_registration dr ON dp.duo_id = dr.duo_id 
        WHERE dr.tournament_id = ? AND dp.user_id = ?
        UNION
        SELECT 1 FROM squad_players sp 
        JOIN squad_registration sqr ON sp.squad_id = sqr.squad_id 
        WHERE sqr.tournament_id = ? AND sp.user_id = ?
    ");
    $check_participant->bind_param("iiiiii", 
        $tournament_id, $user_id,
        $tournament_id, $user_id,
        $tournament_id, $user_id
    );
    $check_participant->execute();
    $is_participant = $check_participant->get_result()->num_rows > 0;
    $check_participant->close();
    
    if (!$is_participant) {
        die("Error: You don't have permission to view this scoreboard.");
    }
}

// Initialize scoreboard engine
$scoreboardEngine = new AdvancedScoreboardEngine($conn, $tournament_id);

// Get scoreboard data
$scoreboard = $scoreboardEngine->generateScoreboard($tournament['bracket_type'] ?? null);
$leaderboards = $scoreboardEngine->generateLeaderboards(10);
$bracketData = $scoreboardEngine->generateBracketData();

// Get live matches
$live_matches = $scoreboardEngine->getLiveMatches();

// Calculate total participants
$total_participants = $tournament['solo_count'] + 
                     ($tournament['duo_count'] * 2) + 
                     ($tournament['squad_count'] * 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scoreboard - <?php echo htmlspecialchars($tournament['tname']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #6c63ff;
            --secondary-color: #ff6584;
            --success-color: #10b981;
            --warning-color: #f59e0b;
            --danger-color: #ef4444;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --text-light: #f8fafc;
            --text-muted: #94a3b8;
        }
        
        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1e293b 100%);
            color: var(--text-light);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }
        
        .glass-card:hover {
            border-color: rgba(108, 99, 255, 0.3);
            box-shadow: 0 12px 48px rgba(0, 0, 0, 0.4);
        }
        
        .tournament-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #4f46e5 100%);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .tournament-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1%, transparent 20%);
            opacity: 0.3;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        
        .stat-box {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-box:hover {
            transform: translateY(-5px);
            background: rgba(255, 255, 255, 0.12);
        }
        
        .stat-value {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 0.9rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .nav-tabs-custom .nav-link {
            color: var(--text-muted);
            border: none;
            padding: 12px 25px;
            background: transparent;
            position: relative;
            font-weight: 500;
        }
        
        .nav-tabs-custom .nav-link.active {
            color: var(--primary-color);
            background: transparent;
        }
        
        .nav-tabs-custom .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 15px;
            right: 15px;
            height: 3px;
            background: var(--primary-color);
            border-radius: 3px 3px 0 0;
        }
        
        .table-custom {
            --bs-table-bg: transparent;
            --bs-table-striped-bg: rgba(255, 255, 255, 0.03);
            --bs-table-hover-bg: rgba(108, 99, 255, 0.1);
            --bs-table-border-color: rgba(255, 255, 255, 0.1);
        }
        
        .ranking-badge {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-weight: bold;
            font-size: 14px;
        }
        
        .ranking-1 { background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); color: white; }
        .ranking-2 { background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%); color: white; }
        .ranking-3 { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); color: white; }
        .ranking-other { background: rgba(255, 255, 255, 0.1); color: var(--text-light); }
        
        .player-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 14px;
        }
        
        .progress-thin {
            height: 6px;
            border-radius: 3px;
            background: rgba(255, 255, 255, 0.1);
            overflow: hidden;
        }
        
        .progress-thin .progress-bar {
            border-radius: 3px;
            transition: width 0.6s ease;
        }
        
        .live-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: var(--danger-color);
            border-radius: 50%;
            margin-right: 5px;
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
            100% { opacity: 1; transform: scale(1); }
        }
        
        .match-card {
            border-left: 4px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .match-card:hover {
            border-left-color: var(--primary-color);
            transform: translateX(5px);
        }
        
        .score-badge {
            min-width: 45px;
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: bold;
            text-align: center;
            font-size: 1.1rem;
        }
        
        .winner-score {
            background: linear-gradient(135deg, var(--success-color), #059669);
            color: white;
        }
        
        .loser-score {
            background: rgba(255, 255, 255, 0.1);
            color: var(--text-light);
        }
        
        .bracket-container {
            overflow-x: auto;
            padding: 20px 0;
        }
        
        .bracket-tree {
            display: flex;
            gap: 40px;
            min-width: max-content;
        }
        
        .round {
            min-width: 280px;
        }
        
        .round-title {
            color: var(--text-muted);
            margin-bottom: 15px;
            text-align: center;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .match-node {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            margin-bottom: 10px;
            padding: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        
        .team-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }
        
        .team-row.winner {
            font-weight: bold;
            color: var(--success-color);
        }
        
        .team-name {
            flex: 1;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .team-score {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            padding: 3px 10px;
            font-weight: bold;
            margin-left: 10px;
            min-width: 40px;
            text-align: center;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color) 0%, #4f46e5 100%);
            border: none;
            padding: 10px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #5a52e0 0%, #4338ca 100%);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(108, 99, 255, 0.3);
        }
        
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            display: none;
        }
        
        .spinner {
            width: 60px;
            height: 60px;
            border: 5px solid rgba(108, 99, 255, 0.3);
            border-top: 5px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="text-center">
            <div class="spinner mb-3"></div>
            <p>Loading scoreboard data...</p>
            <small class="text-muted">Please wait</small>
        </div>
    </div>

    <!-- Header -->
    <?php include 'header.php'; ?>

    <div class="container-fluid py-4">
        <!-- Tournament Header -->
        <div class="tournament-header">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="display-5 fw-bold mb-2">
                        <i class="bi bi-trophy me-2"></i>
                        <?php echo htmlspecialchars($tournament['tname']); ?>
                    </h1>
                    <div class="d-flex flex-wrap gap-3 mb-3">
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-controller me-1"></i>
                            <?php echo htmlspecialchars($tournament['selected_game']); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-calendar-event me-1"></i>
                            <?php echo date('F j, Y', strtotime($tournament['sdate'])); ?>
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-people me-1"></i>
                            <?php echo $total_participants; ?> Participants
                        </span>
                        <span class="badge bg-light text-dark">
                            <i class="bi bi-diagram-3 me-1"></i>
                            <?php echo ucfirst(str_replace('_', ' ', $tournament['bracket_type'] ?? 'single elimination')); ?>
                        </span>
                        <?php if ($tournament['status'] === 'ongoing'): ?>
                        <span class="badge bg-danger">
                            <span class="live-indicator"></span> LIVE
                        </span>
                        <?php endif; ?>
                    </div>
                    <p class="mb-0 opacity-75"><?php echo htmlspecialchars($tournament['about']); ?></p>
                </div>
                <div class="col-md-4 text-md-end">
                    <div class="btn-group" role="group">
                        <button class="btn btn-outline-light" onclick="exportScoreboard('csv')">
                            <i class="bi bi-download me-1"></i> CSV
                        </button>
                        <button class="btn btn-outline-light" onclick="exportScoreboard('pdf')">
                            <i class="bi bi-file-pdf me-1"></i> PDF
                        </button>
                        <button class="btn btn-light" onclick="refreshScoreboard()">
                            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                        </button>
                        <?php if ($tournament['user_id'] == $user_id || ($_SESSION['is_admin'] ?? false)): ?>
                        <a href="edit_tour.php?tournament_id=<?php echo $tournament_id; ?>" class="btn btn-primary-custom">
                            <i class="bi bi-gear me-1"></i> Manage
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-box">
                <div class="stat-value"><?php echo count($scoreboard['standings']); ?></div>
                <div class="stat-label">Active Teams</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $tournament['completed_matches']; ?></div>
                <div class="stat-label">Matches Played</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo $tournament['ongoing_matches']; ?></div>
                <div class="stat-label">Live Matches</div>
            </div>
            <div class="stat-box">
                <div class="stat-value"><?php echo count($scoreboard['upcoming_matches']); ?></div>
                <div class="stat-label">Upcoming</div>
            </div>
        </div>
        
        <!-- Main Navigation -->
        <ul class="nav nav-tabs nav-tabs-custom mb-4" id="scoreboardTabs">
            <li class="nav-item">
                <a class="nav-link active" data-bs-toggle="tab" href="#standings">
                    <i class="bi bi-table me-1"></i> Standings
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#bracket">
                    <i class="bi bi-diagram-3 me-1"></i> Bracket
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#leaderboards">
                    <i class="bi bi-trophy me-1"></i> Leaderboards
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#matches">
                    <i class="bi bi-calendar-week me-1"></i> Matches
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-bs-toggle="tab" href="#stats">
                    <i class="bi bi-graph-up me-1"></i> Statistics
                </a>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content">
            <!-- Standings Tab -->
            <div class="tab-pane fade show active" id="standings">
                <div class="glass-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4><i class="bi bi-trophy me-2"></i>Tournament Standings</h4>
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-outline-light active" onclick="filterStandings('all')">All</button>
                            <button class="btn btn-sm btn-outline-light" onclick="filterStandings('qualified')">Qualified</button>
                            <button class="btn btn-sm btn-outline-light" onclick="filterStandings('active')">Active</button>
                            <button class="btn btn-sm btn-outline-light" onclick="filterStandings('eliminated')">Eliminated</button>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom table-hover">
                            <thead>
                                <tr>
                                    <th width="60">#</th>
                                    <th>Team/Player</th>
                                    <th class="text-center">MP</th>
                                    <th class="text-center">W</th>
                                    <th class="text-center">L</th>
                                    <th class="text-center">Pts</th>
                                    <th class="text-center">K/D</th>
                                    <th class="text-center">Rating</th>
                                    <th class="text-center">Form</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody id="standingsTable">
                                <?php if (empty($scoreboard['standings'])): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5">
                                        <i class="bi bi-info-circle display-4 text-muted d-block mb-3"></i>
                                        <h5>No standings available yet</h5>
                                        <p class="text-muted">Standings will appear after matches are played</p>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php foreach ($scoreboard['standings'] as $index => $standing): ?>
                                <tr class="standing-row <?php echo $standing['status'] ?? 'active'; ?>">
                                    <td>
                                        <div class="ranking-badge ranking-<?php echo min(4, $index + 1); ?>">
                                            <?php echo $index + 1; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="player-avatar me-3">
                                                <?php echo strtoupper(substr($standing['name'] ?? 'T', 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($standing['name'] ?? 'Team ' . ($index + 1)); ?></div>
                                                <small class="text-muted">
                                                    Win Rate: <?php 
                                                        $win_rate = isset($standing['matches_played']) && $standing['matches_played'] > 0 
                                                            ? round(($standing['wins'] / $standing['matches_played']) * 100) 
                                                            : 0;
                                                        echo $win_rate; 
                                                    ?>%
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center"><?php echo $standing['matches_played'] ?? 0; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-success"><?php echo $standing['wins'] ?? 0; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger"><?php echo $standing['losses'] ?? 0; ?></span>
                                    </td>
                                    <td class="text-center fw-bold"><?php echo $standing['points'] ?? 0; ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $kills = $standing['kills'] ?? 0;
                                            $deaths = $standing['deaths'] ?? 1;
                                            echo round($kills / $deaths, 2); 
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="progress-thin mb-1">
                                            <div class="progress-bar bg-warning" 
                                                 style="width: <?php echo min(100, ($standing['rating'] ?? 0) * 50); ?>%"></div>
                                        </div>
                                        <small><?php echo round($standing['rating'] ?? 0, 2); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <?php if (isset($standing['form']) && is_array($standing['form'])): ?>
                                            <?php foreach (array_slice($standing['form'], -5) as $result): ?>
                                                <span class="badge bg-<?php echo $result === 'W' ? 'success' : ($result === 'D' ? 'warning' : 'danger'); ?>">
                                                    <?php echo $result; ?>
                                                </span>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php 
                                            $status = $standing['status'] ?? 'active';
                                            if ($status === 'qualified'): 
                                        ?>
                                            <span class="badge bg-success">Qualified</span>
                                        <?php elseif ($status === 'eliminated'): ?>
                                            <span class="badge bg-danger">Eliminated</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Active</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Bracket Tab -->
            <div class="tab-pane fade" id="bracket">
                <div class="glass-card p-4">
                    <h4 class="mb-4"><i class="bi bi-diagram-3 me-2"></i>Tournament Bracket</h4>
                    <?php if (empty($bracketData['rounds'])): ?>
                    <div class="text-center py-5">
                        <i class="bi bi-diagram-3 display-4 text-muted d-block mb-3"></i>
                        <h5>Bracket not generated yet</h5>
                        <p class="text-muted">The bracket will appear once the tournament starts</p>
                        <?php if ($tournament['user_id'] == $user_id): ?>
                        <button class="btn btn-primary-custom mt-3" onclick="generateBracket()">
                            <i class="bi bi-magic me-1"></i> Generate Bracket
                        </button>
                        <?php endif; ?>
                    </div>
                    <?php else: ?>
                    <div class="bracket-container">
                        <div class="bracket-tree">
                            <?php foreach ($bracketData['rounds'] as $round): ?>
                            <div class="round">
                                <div class="round-title"><?php echo $round['round_name']; ?></div>
                                <div class="matches">
                                    <?php foreach ($round['matches'] as $match): ?>
                                    <div class="match-node">
                                        <div class="team-row <?php echo $match['team1']['score'] > $match['team2']['score'] ? 'winner' : ''; ?>">
                                            <span class="team-name"><?php echo htmlspecialchars($match['team1']['name']); ?></span>
                                            <span class="team-score"><?php echo $match['team1']['score']; ?></span>
                                        </div>
                                        <div class="team-row <?php echo $match['team2']['score'] > $match['team1']['score'] ? 'winner' : ''; ?>">
                                            <span class="team-name"><?php echo htmlspecialchars($match['team2']['name']); ?></span>
                                            <span class="team-score"><?php echo $match['team2']['score']; ?></span>
                                        </div>
                                        <div class="text-center mt-2">
                                            <small class="text-muted">
                                                <?php echo $match['status'] === 'completed' ? 'Completed' : 'Scheduled'; ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Leaderboards Tab -->
            <div class="tab-pane fade" id="leaderboards">
                <div class="row">
                    <?php if (empty($leaderboards['rating'])): ?>
                    <div class="col-12">
                        <div class="glass-card p-5 text-center">
                            <i class="bi bi-trophy display-4 text-muted d-block mb-3"></i>
                            <h5>No leaderboard data yet</h5>
                            <p class="text-muted">Statistics will appear after matches are played</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Rating Leaderboard -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-star-fill text-warning me-2"></i>Top Ratings</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach ($leaderboards['rating'] as $index => $player): ?>
                                <div class="list-group-item bg-transparent border-secondary d-flex align-items-center py-3">
                                    <div class="ranking-badge ranking-<?php echo min(4, $index + 1); ?> me-3">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($player['player_name']); ?></div>
                                        <small class="text-muted">
                                            <?php echo $player['matches_played'] ?? 0; ?> matches • 
                                            K/D: <?php 
                                                $kills = $player['total_kills'] ?? 0;
                                                $deaths = $player['total_deaths'] ?? 1;
                                                echo round($kills / $deaths, 2); 
                                            ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 mb-0"><?php echo round($player['avg_rating'] ?? 0, 2); ?></div>
                                        <small class="text-muted">Rating</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kills Leaderboard -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-bullseye text-danger me-2"></i>Most Kills</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach ($leaderboards['kills'] as $index => $player): ?>
                                <div class="list-group-item bg-transparent border-secondary d-flex align-items-center py-3">
                                    <div class="ranking-badge ranking-<?php echo min(4, $index + 1); ?> me-3">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($player['player_name']); ?></div>
                                        <small class="text-muted">
                                            ADR: <?php echo round($player['avg_adr'] ?? 0, 1); ?> • 
                                            HS: <?php 
                                                $kills = $player['total_kills'] ?? 0;
                                                $hs = $player['total_headshots'] ?? 0;
                                                echo $kills > 0 ? round(($hs / $kills) * 100) : 0; 
                                            ?>%
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 mb-0"><?php echo $player['total_kills'] ?? 0; ?></div>
                                        <small class="text-muted">Kills</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Clutch Leaderboard -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-shield-fill text-success me-2"></i>Clutch Masters</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach ($leaderboards['clutches'] as $index => $player): ?>
                                <div class="list-group-item bg-transparent border-secondary d-flex align-items-center py-3">
                                    <div class="ranking-badge ranking-<?php echo min(4, $index + 1); ?> me-3">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($player['player_name']); ?></div>
                                        <small class="text-muted">
                                            <?php echo $player['matches_played'] ?? 0; ?> matches • 
                                            <?php echo $player['total_first_kills'] ?? 0; ?> opening kills
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 mb-0"><?php echo $player['total_clutches'] ?? 0; ?></div>
                                        <small class="text-muted">Clutches</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ADR Leaderboard -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-speedometer2 text-primary me-2"></i>Highest ADR</h5>
                            <div class="list-group list-group-flush">
                                <?php foreach ($leaderboards['adr'] as $index => $player): ?>
                                <div class="list-group-item bg-transparent border-secondary d-flex align-items-center py-3">
                                    <div class="ranking-badge ranking-<?php echo min(4, $index + 1); ?> me-3">
                                        <?php echo $index + 1; ?>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-bold"><?php echo htmlspecialchars($player['player_name']); ?></div>
                                        <small class="text-muted">
                                            Total Damage: <?php echo number_format($player['total_damage'] ?? 0); ?>
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <div class="h5 mb-0"><?php echo round($player['avg_adr'] ?? 0, 1); ?></div>
                                        <small class="text-muted">ADR</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Matches Tab -->
            <div class="tab-pane fade" id="matches">
                <div class="row">
                    <!-- Live Matches -->
                    <?php if (!empty($live_matches)): ?>
                    <div class="col-12 mb-4">
                        <div class="glass-card p-4">
                            <h5 class="mb-3"><i class="bi bi-broadcast text-danger me-2"></i>Live Matches</h5>
                            <div class="row g-3" id="liveMatchesContainer">
                                <?php foreach ($live_matches as $match): ?>
                                <div class="col-md-6">
                                    <div class="match-card glass-card p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">
                                                Round <?php echo $match['current_round'] ?? 1; ?> / <?php echo $match['total_rounds'] ?? 30; ?>
                                            </small>
                                            <span class="badge bg-danger">
                                                <span class="live-indicator"></span> LIVE
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <div class="text-center flex-grow-1">
                                                <div class="fw-bold mb-1"><?php echo htmlspecialchars($match['team1_name']); ?></div>
                                                <div class="score-badge <?php echo ($match['score1'] ?? 0) > ($match['score2'] ?? 0) ? 'winner-score' : 'loser-score'; ?>">
                                                    <?php echo $match['score1'] ?? 0; ?>
                                                </div>
                                            </div>
                                            <div class="px-3">
                                                <div class="round-timer bg-dark px-2 py-1 rounded">
                                                    <?php 
                                                        $time = $match['round_time'] ?? 115;
                                                        echo floor($time / 60) . ':' . str_pad($time % 60, 2, '0', STR_PAD_LEFT); 
                                                    ?>
                                                </div>
                                            </div>
                                            <div class="text-center flex-grow-1">
                                                <div class="fw-bold mb-1"><?php echo htmlspecialchars($match['team2_name']); ?></div>
                                                <div class="score-badge <?php echo ($match['score2'] ?? 0) > ($match['score1'] ?? 0) ? 'winner-score' : 'loser-score'; ?>">
                                                    <?php echo $match['score2'] ?? 0; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="text-center">
                                            <button class="btn btn-sm btn-danger" onclick="viewLiveMatch(<?php echo $match['match_id']; ?>)">
                                                <i class="bi bi-play-circle me-1"></i> Watch Live
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Recent Matches -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-clock-history me-2"></i>Recent Matches</h5>
                            <div id="recentMatches">
                                <?php if (empty($scoreboard['recent_matches'])): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-clock-history display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted">No matches played yet</p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($scoreboard['recent_matches'] as $match): ?>
                                <div class="match-card glass-card p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <?php echo date('M j, g:i A', strtotime($match['end_time'] ?? 'now')); ?>
                                        </small>
                                        <span class="badge bg-<?php echo ($match['status'] ?? 'completed') === 'completed' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($match['status'] ?? 'completed'); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="text-center flex-grow-1">
                                            <div class="fw-bold"><?php echo htmlspecialchars($match['team1_name'] ?? 'Team 1'); ?></div>
                                            <div class="score-badge <?php echo ($match['score1'] ?? 0) > ($match['score2'] ?? 0) ? 'winner-score' : 'loser-score'; ?>">
                                                <?php echo $match['score1'] ?? 0; ?>
                                            </div>
                                        </div>
                                        <div class="px-3">
                                            <small class="text-muted">vs</small>
                                        </div>
                                        <div class="text-center flex-grow-1">
                                            <div class="fw-bold"><?php echo htmlspecialchars($match['team2_name'] ?? 'Team 2'); ?></div>
                                            <div class="score-badge <?php echo ($match['score2'] ?? 0) > ($match['score1'] ?? 0) ? 'winner-score' : 'loser-score'; ?>">
                                                <?php echo $match['score2'] ?? 0; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if (!empty($match['vod_link'])): ?>
                                    <div class="text-center mt-2">
                                        <a href="<?php echo $match['vod_link']; ?>" target="_blank" class="btn btn-sm btn-outline-light">
                                            <i class="bi bi-play-circle me-1"></i> Watch VOD
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Upcoming Matches -->
                    <div class="col-lg-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3"><i class="bi bi-calendar-check me-2"></i>Upcoming Matches</h5>
                            <div id="upcomingMatches">
                                <?php if (empty($scoreboard['upcoming_matches'])): ?>
                                <div class="text-center py-4">
                                    <i class="bi bi-calendar-x display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted">No upcoming matches scheduled</p>
                                </div>
                                <?php else: ?>
                                <?php foreach ($scoreboard['upcoming_matches'] as $match): ?>
                                <div class="match-card glass-card p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="text-muted">
                                            <?php echo date('M j, g:i A', strtotime($match['start_time'] ?? 'now')); ?>
                                        </small>
                                        <?php if ($match['is_live'] ?? false): ?>
                                        <span class="badge bg-danger">
                                            <span class="live-indicator"></span> LIVE
                                        </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="fw-bold text-center flex-grow-1"><?php echo htmlspecialchars($match['team1_name'] ?? 'Team 1'); ?></div>
                                        <div class="px-3 text-muted">vs</div>
                                        <div class="fw-bold text-center flex-grow-1"><?php echo htmlspecialchars($match['team2_name'] ?? 'Team 2'); ?></div>
                                    </div>
                                    <div class="text-center">
                                        <button class="btn btn-sm btn-outline-primary" onclick="predictMatch(<?php echo $match['id'] ?? 0; ?>)">
                                            <i class="bi bi-magic me-1"></i> Predict Outcome
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Statistics Tab -->
            <div class="tab-pane fade" id="stats">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3">Tournament Statistics</h5>
                            <canvas id="statsChart" height="250"></canvas>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="glass-card p-4 h-100">
                            <h5 class="mb-3">Performance Overview</h5>
                            <div id="performanceMetrics">
                                <!-- Will be populated by JavaScript -->
                                <div class="text-center py-4">
                                    <i class="bi bi-graph-up display-4 text-muted d-block mb-3"></i>
                                    <p class="text-muted">Statistics will appear after matches are played</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        const tournamentId = <?php echo $tournament_id; ?>;
        let refreshInterval;
        
        // Initialize on load
        document.addEventListener('DOMContentLoaded', function() {
            initializeCharts();
            setupAutoRefresh();
            setupTabListeners();
        });
        
        function initializeCharts() {
            // Statistics Chart
            const statsCtx = document.getElementById('statsChart').getContext('2d');
            if (statsCtx) {
                new Chart(statsCtx, {
                    type: 'bar',
                    data: {
                        labels: ['Kills', 'Deaths', 'Assists', 'Headshots', 'Damage', 'Clutches'],
                        datasets: [{
                            label: 'Tournament Totals',
                            data: [
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_kills')) ?? 0; ?>,
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_deaths')) ?? 0; ?>,
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_assists')) ?? 0; ?>,
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_headshots')) ?? 0; ?>,
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_damage')) ?? 0; ?>,
                                <?php echo array_sum(array_column($leaderboards['rating'] ?? [], 'total_clutches')) ?? 0; ?>
                            ],
                            backgroundColor: [
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(107, 114, 128, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(245, 158, 11, 0.8)',
                                'rgba(139, 92, 246, 0.8)',
                                'rgba(16, 185, 129, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(255, 255, 255, 0.1)' },
                                ticks: { color: 'rgba(255, 255, 255, 0.8)' }
                            },
                            x: {
                                grid: { color: 'rgba(255, 255, 255, 0.1)' },
                                ticks: { color: 'rgba(255, 255, 255, 0.8)' }
                            }
                        },
                        plugins: {
                            legend: {
                                labels: { color: 'rgba(255, 255, 255, 0.8)' }
                            }
                        }
                    }
                });
            }
        }
        
        function setupAutoRefresh() {
            // Refresh every 30 seconds if tournament is ongoing
            <?php if ($tournament['status'] === 'ongoing'): ?>
            refreshInterval = setInterval(refreshScoreboard, 30000);
            <?php endif; ?>
        }
        
        function setupTabListeners() {
            // Handle tab switching
            const tabLinks = document.querySelectorAll('.nav-tabs-custom .nav-link');
            tabLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    // Remove active class from all tabs
                    tabLinks.forEach(l => l.classList.remove('active'));
                    // Add active class to clicked tab
                    this.classList.add('active');
                });
            });
        }
        
        function refreshScoreboard() {
            showLoading();
            
            fetch(`api/scoreboard/refresh.php?tournament_id=${tournamentId}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        showNotification(data.error, 'error');
                        return;
                    }
                    
                    updateScoreboard(data);
                    showNotification('Scoreboard updated successfully', 'success');
                })
                .catch(error => {
                    console.error('Error refreshing scoreboard:', error);
                    showNotification('Failed to refresh scoreboard', 'error');
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        function updateScoreboard(data) {
            // Update standings if available
            if (data.standings && Array.isArray(data.standings)) {
                updateStandings(data.standings);
            }
            
            // Update recent matches if available
            if (data.recent_matches && Array.isArray(data.recent_matches)) {
                updateMatches('recentMatches', data.recent_matches);
            }
            
            // Update upcoming matches if available
            if (data.upcoming_matches && Array.isArray(data.upcoming_matches)) {
                updateMatches('upcomingMatches', data.upcoming_matches, true);
            }
            
            // Update live matches if available
            if (data.live_matches && Array.isArray(data.live_matches)) {
                updateLiveMatches(data.live_matches);
            }
        }
        
        function updateStandings(standings) {
            const table = document.getElementById('standingsTable');
            if (!table || !standings.length) return;
            
            let html = '';
            standings.forEach((standing, index) => {
                const winRate = standing.matches_played > 0 
                    ? Math.round((standing.wins / standing.matches_played) * 100) 
                    : 0;
                const kd = standing.kills / Math.max(1, standing.deaths);
                const rating = standing.rating || 0;
                
                html += `
                    <tr class="standing-row ${standing.status || 'active'}">
                        <td>
                            <div class="ranking-badge ranking-${Math.min(4, index + 1)}">
                                ${index + 1}
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <div class="player-avatar me-3">
                                    ${(standing.name || 'T').substring(0, 2).toUpperCase()}
                                </div>
                                <div>
                                    <div class="fw-bold">${escapeHtml(standing.name || 'Team ' + (index + 1))}</div>
                                    <small class="text-muted">Win Rate: ${winRate}%</small>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">${standing.matches_played || 0}</td>
                        <td class="text-center">
                            <span class="badge bg-success">${standing.wins || 0}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-danger">${standing.losses || 0}</span>
                        </td>
                        <td class="text-center fw-bold">${standing.points || 0}</td>
                        <td class="text-center">
                            ${kd.toFixed(2)}
                        </td>
                        <td class="text-center">
                            <div class="progress-thin mb-1">
                                <div class="progress-bar bg-warning" 
                                     style="width: ${Math.min(100, rating * 50)}%"></div>
                            </div>
                            <small>${rating.toFixed(2)}</small>
                        </td>
                        <td class="text-center">
                            ${standing.form && standing.form.length ? 
                                standing.form.slice(-5).map(result => 
                                    `<span class="badge bg-${result === 'W' ? 'success' : result === 'D' ? 'warning' : 'danger'}">${result}</span>`
                                ).join(' ') : ''}
                        </td>
                        <td class="text-center">
                            ${standing.status === 'qualified' ? 
                                '<span class="badge bg-success">Qualified</span>' : 
                                standing.status === 'eliminated' ? 
                                '<span class="badge bg-danger">Eliminated</span>' : 
                                '<span class="badge bg-warning">Active</span>'}
                        </td>
                    </tr>
                `;
            });
            
            table.innerHTML = html;
        }
        
        function updateMatches(containerId, matches, isUpcoming = false) {
            const container = document.getElementById(containerId);
            if (!container || !matches.length) return;
            
            let html = '';
            matches.forEach(match => {
                const isLive = match.is_live || false;
                const team1Name = match.team1_name || 'Team 1';
                const team2Name = match.team2_name || 'Team 2';
                const score1 = match.score1 || 0;
                const score2 = match.score2 || 0;
                const time = match.end_time || match.start_time || new Date();
                
                html += `
                    <div class="match-card glass-card p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <small class="text-muted">
                                ${new Date(time).toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' })}
                            </small>
                            ${isLive ? 
                                '<span class="badge bg-danger"><span class="live-indicator"></span> LIVE</span>' :
                                `<span class="badge bg-${(match.status || 'completed') === 'completed' ? 'success' : 'warning'}">
                                    ${(match.status || 'completed').charAt(0).toUpperCase() + (match.status || 'completed').slice(1)}
                                </span>`
                            }
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-center flex-grow-1">
                                <div class="fw-bold">${escapeHtml(team1Name)}</div>
                                <div class="score-badge ${score1 > score2 ? 'winner-score' : 'loser-score'}">
                                    ${score1}
                                </div>
                            </div>
                            <div class="px-3">
                                <small class="text-muted">vs</small>
                            </div>
                            <div class="text-center flex-grow-1">
                                <div class="fw-bold">${escapeHtml(team2Name)}</div>
                                <div class="score-badge ${score2 > score1 ? 'winner-score' : 'loser-score'}">
                                    ${score2}
                                </div>
                            </div>
                        </div>
                        ${match.vod_link ? `
                        <div class="text-center mt-2">
                            <a href="${match.vod_link}" target="_blank" class="btn btn-sm btn-outline-light">
                                <i class="bi bi-play-circle me-1"></i> Watch VOD
                            </a>
                        </div>
                        ` : ''}
                        ${isUpcoming && !isLive ? `
                        <div class="text-center mt-2">
                            <button class="btn btn-sm btn-outline-primary" onclick="predictMatch(${match.id || 0})">
                                <i class="bi bi-magic me-1"></i> Predict Outcome
                            </button>
                        </div>
                        ` : ''}
                    </div>
                `;
            });
            
            container.innerHTML = html || `
                <div class="text-center py-4">
                    <i class="bi bi-${isUpcoming ? 'calendar-x' : 'clock-history'} display-4 text-muted d-block mb-3"></i>
                    <p class="text-muted">No ${isUpcoming ? 'upcoming' : 'recent'} matches</p>
                </div>
            `;
        }
        
        function filterStandings(filter) {
            const rows = document.querySelectorAll('.standing-row');
            const buttons = document.querySelectorAll('#standingsTab .btn-group .btn');
            
            // Update active button
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Show/hide rows based on filter
            rows.forEach(row => {
                switch(filter) {
                    case 'all':
                        row.style.display = '';
                        break;
                    case 'qualified':
                        row.style.display = row.classList.contains('qualified') ? '' : 'none';
                        break;
                    case 'active':
                        row.style.display = !row.classList.contains('qualified') && !row.classList.contains('eliminated') ? '' : 'none';
                        break;
                    case 'eliminated':
                        row.style.display = row.classList.contains('eliminated') ? '' : 'none';
                        break;
                }
            });
        }
        
        function exportScoreboard(format) {
            showLoading();
            
            fetch(`api/scoreboard/export.php?tournament_id=${tournamentId}&format=${format}`)
                .then(response => {
                    if (format === 'pdf') {
                        return response.blob();
                    } else {
                        return response.text();
                    }
                })
                .then(data => {
                    if (format === 'pdf') {
                        // Download PDF
                        const url = window.URL.createObjectURL(data);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `scoreboard_${tournamentId}.pdf`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    } else {
                        // Download CSV
                        const blob = new Blob([data], { type: 'text/csv' });
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = `scoreboard_${tournamentId}.csv`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }
                    showNotification(`Scoreboard exported as ${format.toUpperCase()}`, 'success');
                })
                .catch(error => {
                    console.error('Export error:', error);
                    showNotification('Export failed: ' + error.message, 'error');
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        function generateBracket() {
            if (!confirm('Generate tournament bracket? This will create match pairings based on current standings.')) {
                return;
            }
            
            showLoading();
            
            fetch(`api/bracket/generate.php?tournament_id=${tournamentId}`, {
                method: 'POST'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Bracket generated successfully!', 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    showNotification(data.error || 'Failed to generate bracket', 'error');
                }
            })
            .catch(error => {
                console.error('Bracket generation error:', error);
                showNotification('Bracket generation failed', 'error');
            })
            .finally(() => {
                hideLoading();
            });
        }
        
        function predictMatch(matchId) {
            if (!matchId) return;
            
            showLoading();
            
            fetch(`api/predict_match.php?match_id=${matchId}`)
                .then(response => response.json())
                .then(prediction => {
                    // Create prediction modal
                    const modalHtml = `
                        <div class="modal fade" id="predictionModal">
                            <div class="modal-dialog">
                                <div class="modal-content glass-card">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">Match Prediction</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center">
                                            <h6>${prediction.team1_name} vs ${prediction.team2_name}</h6>
                                            <div class="mt-3">
                                                <div class="progress" style="height: 30px;">
                                                    <div class="progress-bar bg-success" style="width: ${prediction.team1_win_probability * 100}%">
                                                        ${Math.round(prediction.team1_win_probability * 100)}%
                                                    </div>
                                                    <div class="progress-bar bg-danger" style="width: ${prediction.team2_win_probability * 100}%">
                                                        ${Math.round(prediction.team2_win_probability * 100)}%
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-between mt-2">
                                                    <div>${prediction.team1_name}</div>
                                                    <div>${prediction.team2_name}</div>
                                                </div>
                                            </div>
                                            <div class="mt-3">
                                                <small class="text-muted">Confidence: ${Math.round(prediction.confidence * 100)}%</small>
                                            </div>
                                            ${prediction.expected_score ? `
                                            <div class="mt-3">
                                                <small class="text-muted">Expected Score: ${prediction.expected_score}</small>
                                            </div>
                                            ` : ''}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    
                    // Add modal to DOM
                    const modalContainer = document.createElement('div');
                    modalContainer.innerHTML = modalHtml;
                    document.body.appendChild(modalContainer);
                    
                    // Show modal
                    const modal = new bootstrap.Modal(document.getElementById('predictionModal'));
                    modal.show();
                    
                    // Clean up after modal is hidden
                    document.getElementById('predictionModal').addEventListener('hidden.bs.modal', function() {
                        modalContainer.remove();
                    });
                })
                .catch(error => {
                    console.error('Prediction error:', error);
                    showNotification('Failed to generate prediction', 'error');
                })
                .finally(() => {
                    hideLoading();
                });
        }
        
        function viewLiveMatch(matchId) {
            window.open(`live_scoreboard.php?match_id=${matchId}`, '_blank');
        }
        
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'flex';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        function showNotification(message, type) {
            // Remove existing notifications
            const existing = document.querySelectorAll('.notification');
            existing.forEach(notif => notif.remove());
            
            // Create new notification
            const notification = document.createElement('div');
            notification.className = `notification alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 5000);
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        });
    </script>
</body>
</html>
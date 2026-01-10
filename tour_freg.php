<?php
include('header.php');

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}

$tournament_id = intval($_GET['tournament_id']);
$tournament = [];
$error_message = '';
$slots_full_message = '';

// Function to decode and display content properly
function decodeTournamentContent($content) {
    if (empty($content)) {
        return '';
    }
    
    // Check if content is base64 encoded
    $decoded = @base64_decode($content, true);
    
    // If base64 decode was successful and didn't return the same string
    if ($decoded !== false && $decoded !== $content && base64_encode($decoded) === $content) {
        // Successfully decoded base64
        return $decoded;
    }
    
    // If not base64, return the original content
    return $content;
}

// Fetch tournament details
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS creator_name
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id  
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc();
        
        // Assign variables
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Game';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $bracket_type = $tournament['bracket_type'] ?? '';
        $match_type = $tournament['match_type'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';
        $social_media_input = $tournament['social_media_input'] ?? '';
        
        // Decode the text content properly
        $about = decodeTournamentContent($tournament['about'] ?? '');
        $rules = decodeTournamentContent($tournament['rules'] ?? '');
        $prizes = decodeTournamentContent($tournament['prizes'] ?? '');

        // Calculate registered count
        $registered_count = 0;
        $total_teams = 0;
        $registration_message = "Registered";

        if ($match_type == 'solo') {
            $sql_solo = "SELECT COUNT(*) AS registered FROM solo_registration WHERE tournament_id = ?";
            $stmt_solo = $conn->prepare($sql_solo);
            if ($stmt_solo) {
                $stmt_solo->bind_param("i", $tournament_id);
                $stmt_solo->execute();
                $result_solo = $stmt_solo->get_result();
                $row_solo = $result_solo->fetch_assoc();
                $registered_count = $row_solo['registered'];
                $total_teams = $tournament['solo_players'];
                $registration_message = "Players Registered";
                $stmt_solo->close();
            }
        } elseif ($match_type == 'duo') {
            $sql_duo = "SELECT COUNT(*) AS registered FROM duo_registration WHERE tournament_id = ?";
            $stmt_duo = $conn->prepare($sql_duo);
            if ($stmt_duo) {
                $stmt_duo->bind_param("i", $tournament_id);
                $stmt_duo->execute();
                $result_duo = $stmt_duo->get_result();
                $row_duo = $result_duo->fetch_assoc();
                $registered_count = $row_duo['registered'];
                $total_teams = $tournament['duo_teams'];
                $registration_message = "Teams Registered";
                $stmt_duo->close();
            }
        } elseif ($match_type == 'squad') {
            $sql_squad = "SELECT COUNT(*) AS registered FROM squad_registration WHERE tournament_id = ?";
            $stmt_squad = $conn->prepare($sql_squad);
            if ($stmt_squad) {
                $stmt_squad->bind_param("i", $tournament_id);
                $stmt_squad->execute();
                $result_squad = $stmt_squad->get_result();
                $row_squad = $result_squad->fetch_assoc();
                $registered_count = $row_squad['registered'];
                $total_teams = $tournament['squad_teams'];
                $registration_message = "Teams Registered";
                $stmt_squad->close();
            }
        } else {
            $registered_count = 0;
            $total_teams = 0;
            $registration_message = "Unknown Registration Type";
        }

        // Check if slots are full
        if ($registered_count >= $total_teams) {
            $slots_full_message = "Slots Full";
        }

        // Teams registered message
        $teams_registered_message = "$registered_count / $total_teams $registration_message";

        // Fetch registered participants
        $participants = [];
        if ($match_type == 'solo') {
            $sql_participants = "SELECT player_name AS team_name, NULL AS logo_path 
                                 FROM solo_registration 
                                 WHERE tournament_id = ?";
        } elseif ($match_type == 'duo') {
            $sql_participants = "SELECT team_name, logo_path 
                                 FROM duo_registration 
                                 WHERE tournament_id = ?";
        } elseif ($match_type == 'squad') {
            $sql_participants = "SELECT team_name, logo_path 
                                 FROM squad_registration 
                                 WHERE tournament_id = ?";
        }

        if (isset($sql_participants)) {
            $stmt_participants = $conn->prepare($sql_participants);
            if ($stmt_participants) {
                $stmt_participants->bind_param("i", $tournament_id);
                $stmt_participants->execute();
                $result_participants = $stmt_participants->get_result();
                while ($row = $result_participants->fetch_assoc()) {
                    $participants[] = $row;
                }
                $stmt_participants->close();
            }
        }

    } else {
        $error_message = "No tournament found with that ID.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tname); ?> - Tournament Details</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .banner-cont{
            background-color: #282828;
            margin-top: 0;
        }
        
        .banner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 70vh;
            position: relative;
        }

        .banner-img-container {
            width: 100%;
            display: flex;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .banner-img {
            position: relative;
            width: 100%;
            height: 100%;
            overflow: hidden;
        }

        .banner-photo-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: relative;
        }

        .tournament-operation {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
        }

        .operation-btn {
            display: flex;
            gap: 10px;
            margin-right: 20px;
        }

        .operation-btn button {
            background-color: rgb(30, 196, 141);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .operation-btn button:hover {
            color: black;
        }

        .operation-btn .options {
            background-color: #a75928;
        }

        .operation-btn .options:hover {
            color: black;
        }

        .gameuser {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 10px 0;
            font-size: 21px;
        }

        .gameuser .titlename {
            color: aquamarine;
            margin: 0 5px;
            font-size: 24px;
        }

        .teams-registered {
            font-size: 20px;
            color: #fff;
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }

        .tournament-details {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
            border-top: 1px solid grey;
            border-radius: 8px;
        }

        .details, .rules, .prizes, .contact, .schedule {
            width: 100%;
            border-right: 1px solid grey;
            padding: 10px 20px;
            color: #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .details:hover, .rules:hover, .prizes:hover, .contact:hover, .schedule:hover {
            color: rgb(0, 255, 170);
        }

        .details.active, .rules.active, .prizes.active, .contact.active, .schedule.active {
            color: rgb(0, 255, 170);
            font-weight: bold;
        }

        .tour-title {
            font-size: 20px;
            font-weight: bold;
        }

        .content-container {
            display: none;
        }

        .content-container.active {
            display: block;
        }

        .container-row {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .content-container {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            padding: 20px;
            border-top: 1px solid grey;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            background-color: #282828;
            border-radius: 5px;
        }

        .tournament-details div {
            cursor: pointer;
            margin: 5px;
        }

        .content-title{
            font-size: 18px;
            font-weight: bold;
            color: #ffffff;
            margin: 5px;
            padding: 5px;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
        }

        .cont-title{
            font-size: 16px;
            color: #ffffff;
            margin: 5px;
            margin-right: 10px;
            padding: 10px 5px;
            line-height: 1.6;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #ff0019ff;
            border: 1px solid #f5c6cb;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .participant-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #333;
            border-radius: 5px;
        }

        .small-banner {
            width: 40px;
            height: 40px;
            margin-right: 15px;
            overflow: hidden;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
            flex-shrink: 0;
        }

        .small-banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .organizer-actions.disabled {
            background-color: #666 !important;
            cursor: not-allowed !important;
            opacity: 0.7;
        }

        @media (max-width: 768px) {
            .tournament-details {
                flex-direction: column;
                align-items: stretch;
            }
            
            .details, .rules, .prizes, .contact, .schedule {
                border-right: none;
                border-bottom: 1px solid grey;
                margin-bottom: 5px;
            }
            
            .container-row {
                flex-direction: column;
            }
            
            .operation-btn {
                flex-wrap: wrap;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message) || !empty($slots_full_message)): ?>
        <div class="modal fade" id="messageModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">Notice</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error_message); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($slots_full_message)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($slots_full_message); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
<?php endif; ?>

    <div class="banner-cont">
        <div class="banner-container">
            <div class="banner-img-container">
                <div class="banner-img">
                    <?php if (!empty($bannerimg)): ?>
                        <img id="bannerimg" name="bannerimg" src="image.php?tournament_id=<?php echo urlencode($tournament_id); ?>" alt="Banner Image" class="banner-photo-img" />
                    <?php else: ?>
                        <p style="color: white; text-align: center; padding: 20px;">No banner image available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>


        <div class="tournament">
            <div class="tournament-operation">
                <div class="operation-btn">
                    <button 
                        class="organizer-actions <?php echo (!empty($slots_full_message)) ? 'disabled' : ''; ?>" 
                        onclick="<?php echo (!empty($slots_full_message)) ? '' : "joinTournament($tournament_id, '" . htmlspecialchars($match_type) . "')"; ?>"
                        <?php echo (!empty($slots_full_message)) ? 'disabled' : ''; ?>
                    >
                        <i class="fas fa-gamepad"></i>&nbsp; 
                        <?php echo (!empty($slots_full_message)) ? 'Slots Full' : 'Join Tournament'; ?>
                    </button>

                    <button class="options" onclick="leaderboard(<?php echo $tournament_id; ?>)">
                        <i class="fas fa-list-ol"></i> Leaderboard
                    </button>

                    <button class="options"><i class='fa fa-share-alt'></i> Share</button>
                </div>
            </div>

            <div class="gameuser">
                <p class="titlename"><?php echo htmlspecialchars($selected_game); ?></p>
                Tournament By:
                <p class="titlename"><?php echo htmlspecialchars($creator_name); ?></p>
            </div>
            
            <p class="teams-registered"><?php echo $teams_registered_message; ?></p>
        </div>

        <div class="tournament-details">
            <div id="details" class="details active" onclick="showContent('details')">
                <span class="tour-title">Details</span>
            </div>

            <div id="rules" class="rules" onclick="showContent('rules')">
                <span class="tour-title">Rules</span>
            </div>

            <div id="prizes" class="prizes" onclick="showContent('prizes')">
                <span class="tour-title">Prizes</span>
            </div>

            <div id="contact" class="contact" onclick="showContent('contact')">
                <span class="tour-title">Contact</span>
            </div>

            <div id="schedule" class="schedule" onclick="showContent('schedule')">
                <span class="tour-title">Participants</span>
            </div>
        </div>

        <div class="container-row">
            <!-- DETAILS SECTION -->
            <div class="content-container details-container active" id="details-container">
                <p class="content-title">Game Name</p>
                <p class="cont-title"><?php echo htmlspecialchars($selected_game); ?></p>

                <p class="content-title">Start Date</p>
                <p class="cont-title"><?php echo htmlspecialchars($sdate); ?></p>

                <p class="content-title">Start Time</p>
                <p class="cont-title"><?php echo htmlspecialchars($stime); ?></p>

                <p class="content-title">Bracket Type</p>
                <p class="cont-title"><?php echo htmlspecialchars($bracket_type); ?></p>

                <p class="content-title">Match Type</p>
                <p class="cont-title"><?php echo htmlspecialchars($match_type); ?></p>

                <p class="content-title">About Game</p>
                <p class="cont-title"><?php 
                    if (!empty($about)) {
                        echo html_entity_decode(htmlspecialchars($about));
                    } else {
                        echo "No description available.";
                    }
                ?></p>
            </div>

            <!-- RULES SECTION -->
            <div class="content-container rules-container" id="rules-container">
                <p class="content-title">Game Critical Rules</p>
                <p class="cont-title"><?php 
                    if (!empty($rules)) {
                       echo html_entity_decode(htmlspecialchars($rules));
                    } else {
                        echo "No rules specified.";
                    }
                ?></p>
            </div>

            <!-- PRIZES SECTION -->
            <div class="content-container prizes-container" id="prizes-container">
                <p class="content-title">Prize Details</p>
                <p class="cont-title"><?php 
                    if (!empty($prizes)) {
                        echo html_entity_decode(htmlspecialchars($prizes));
                    } else {
                        echo "No prize details available.";
                    }
                ?></p>
            </div>

            <!-- CONTACT SECTION -->
            <div class="content-container contact-container" id="contact-container">
                <p class="content-title">Contact Info</p>
                <p class="cont-title"><?php echo htmlspecialchars($social_media_input); ?></p>
            </div>

            <!-- PARTICIPANTS SECTION -->
            <div class="content-container schedule-container" id="schedule-container">
                <p class="content-title"><i class="fas fa-user"></i>&nbsp;&nbsp;Registered Participants</p>
                <div class="participants-list">
                    <?php
                    if (!empty($participants)) {
                        $counter = 1;
                        foreach ($participants as $participant) {
                            ?>
                            <div class="participant-item">
                                <div class="small-banner">
                                    <?php
                                    $logo_path = !empty($participant['logo_path']) && file_exists('uploads/' . htmlspecialchars($participant['logo_path'])) 
                                                ? 'uploads/' . htmlspecialchars($participant['logo_path']) 
                                                : 'uploads/dash-logo.png';
                                    echo '<img src="' . $logo_path . '" alt="Participant Logo">';
                                    ?>
                                </div>
                                <span style="color: white;"><?php echo $counter . ". " . htmlspecialchars($participant['team_name']); ?></span>
                            </div>
                            <?php
                            $counter++;
                        }
                    } else {
                        echo '<p class="cont-title" style="text-align: center;">No participants registered yet.</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Accordion functionality
        function showContent(section) {
            // Remove active class from all tabs
            var tabs = document.querySelectorAll('.tournament-details > div');
            tabs.forEach(function(tab) {
                tab.classList.remove('active');
            });
            
            // Add active class to clicked tab
            document.getElementById(section).classList.add('active');
            
            // Hide all content containers
            var containers = document.querySelectorAll('.content-container');
            containers.forEach(function(container) {
                container.classList.remove('active');
            });
            
            // Show the selected content container
            var activeContainer = document.getElementById(section + '-container');
            if (activeContainer) {
                activeContainer.classList.add('active');
            }
        }

        // Initialize with details section active
        document.addEventListener('DOMContentLoaded', function() {
            showContent('details');
        });

        function joinTournament(tournamentId, matchType) {
            window.location.href = 'register.php?tournament_id=' + tournamentId + '&match_type=' + encodeURIComponent(matchType);
        }
        
        function leaderboard(tournamentId) {
            window.location.href = 'leaderboard.php?tournament_id=' + tournamentId;
        }
    </script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('messageModal');
    if (modalEl) {
        var modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
});
</script>

    
</body>
</html>

<?php include('footer.php'); ?>
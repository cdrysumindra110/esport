<?php
include('header.php');

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}



// Handle participant/team removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove']) && $_POST['remove'] === 'yes') {
  $tournament_id = $_POST['tournament_id']; // Tournament ID from the form
  $team_name = $_POST['team_name']; // Team name from the form
  $match_type = $_POST['match_type']; // Match type from the form

  $sql_remove = '';
  if ($match_type === 'solo') {
      // Remove a solo player
      $sql_remove = "DELETE FROM solo_registration WHERE tournament_id = ? AND player_name = ?";
  } elseif ($match_type === 'duo') {
      // Remove a duo team and its players
      $sql_remove = "DELETE dr, dp 
                     FROM duo_registration dr
                     LEFT JOIN duo_players dp ON dr.duo_id = dp.duo_id
                     WHERE dr.tournament_id = ? AND dr.team_name = ?";
  } elseif ($match_type === 'squad') {
      // Remove a squad team and its players
      $sql_remove = "DELETE sr, sp 
                     FROM squad_registration sr
                     LEFT JOIN squad_players sp ON sr.squad_id = sp.squad_id
                     WHERE sr.tournament_id = ? AND sr.team_name = ?";
  }

  if ($sql_remove) {
      $stmt_remove = $conn->prepare($sql_remove);
      if ($stmt_remove) {
          // Bind parameters for tournament ID and team name
          $stmt_remove->bind_param("is", $tournament_id, $team_name);
          if ($stmt_remove->execute()) {
              // $success_message = "Team successfully removed from the tournament.";
              header("Location: mytournaments.php?success_message=Team removed successfully");
              exit();

          } else {
              $error_message = "Error executing the removal query: " . $stmt_remove->error;
              echo $error_message;
          }
          $stmt_remove->close();
      } else {
          $error_message = "Error preparing the removal query: " . $conn->error;
          echo $error_message;
      }
  }
}

$tournament_id = intval($_GET['tournament_id']);
$tournament = [];
$error_message = '';
$slots_full_message = ''; // Variable to hold the slots full message

// Fetch tournament details including creator name
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS creator_name  -- Use 'uname' for the creator's name
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
        $tournament = $result->fetch_assoc(); // Fetch tournament details

        // Assign variables from the $tournament array
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Game';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $bracket_type = $tournament['bracket_type'] ?? '';
        $match_type = $tournament['match_type'] ?? '';
        $about = $tournament['about'] ?? '';
        $rules = $tournament['rules'] ?? '';
        $prizes = $tournament['prizes'] ?? '';
        $social_media_input = $tournament['social_media_input'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator'; // Get creator's name

        // Initialize registered count variable
        $registered_count = 0;

        // Calculate registered teams/players based on match type
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
                $stmt_solo->close(); // Close the statement here
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
                $stmt_duo->close(); // Close the statement here
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
                $stmt_squad->close(); // Close the statement here
            }
        } else {
            $registered_count = 0; // Default to 0 if the match type is invalid
            $total_teams = 0; // Default total to 0
            $registration_message = "Unknown Registration Type"; // Default message
        }

        // Check if the slots are full
        if ($registered_count >= $total_teams) {
            $slots_full_message = "Slots Full"; // Show this message if slots are full
        }

        // Display the teams/players-registered message
        $teams_registered_message = "$registered_count / $total_teams $registration_message";

        // Fetch registered participants
        $participants = []; // Initialize an array to hold participants' details
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
                $stmt_participants->close(); // Close this statement
            } else {
                $error_message = "Error preparing the participants query: " . $conn->error;
            }
        }

        

    } else {
        $error_message = "No tournament found with that ID.";
    }
    $stmt->close(); // Close the tournament detail statement
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
}

$conn->close();

?>

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

<style>
      
      .banner-cont{
      background-color: #282828;
      margin-top: 0;
      }
      /* Center the entire profile-container */
      .banner-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      width: 100%;
      height: 70vh; /* Full height of the viewport */
      position: relative;
      }
  
      /* Styling the cover photo container */
      .banner-img-container {
      width: 100%;
      display: flex;
      justify-content: center;
      position: relative;
      overflow: hidden;
      }
  
      /* Cover photo styling */
      .banner-img {
      position: relative;
      width: 100%;
      height: 100%;
      overflow: hidden;
      }
  
      /* Cover photo image */
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
          position: relative; 
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
          position: relative; 
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
  
      /* Dropdown menu styles */
      .dropdown-menu {
          display: none;
          position: absolute;
          top: 100%;
          left: 0;
          background-color: #ffffff;
          border: 1px solid #ddd;
          border-radius: 5px;
          list-style: none;
          padding: 0;
          margin: 0;
          width: 200px; /* Adjust as needed */
          box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
          z-index: 10; 
      }
  
      .dropdown-menu li {
          padding: 10px;
          display: flex;
          align-items: center;
      }
  
      .dropdown-menu li i {
          margin-right: 8px; 
      }
  
      .dropdown-menu li:hover {
          background-color: #f1f1f1;
      }
  
      /* Show dropdown on hover */
      .organizer-actions:hover .dropdown-menu {
          display: block;
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
  
      .tournament-details {
          display: flex;
          justify-content: space-around; 
          align-items: center; 
          padding: 20px; 
          border-top: 1px solid grey;
          border-radius: 8px; 
      }
  
      .details, .rules, .prizes, .schedule, .contact {
          width: 100%;
          border-right: 1px solid grey;
          padding: 10px 20px; 
          color: #ddd;
          border-radius: 5px; 
          text-align: center; 
          cursor: pointer; 
          transition: background-color 0.3s ease; 
      }
  
      .details:hover, .rules:hover, .prizes:hover, .schedule:hover, .contact:hover {
          color: rgb(0, 255, 170); /* Changes background on hover */
      }
  
      .tour-title {
          font-size: 20px; /* Adjust font size */
          font-weight: bold;
      }
  
      /* Hide all content containers by default */
      .content-container {
          display: none;
      }
  
      /* Show only the active content container */
      .content-container.active {
          display: block;
      }
  
      /* Flexbox layout for containers */
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
          padding: 10px;
          border-top: 1px solid grey;
          box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      }
  
      .tournament-details div {
          cursor: pointer;
          margin: 5px;
      }
      .content-title{
      font-size: 18px; /* Adjust font size */
      font-weight: bold;
      color: #ffffff;
      margin: 5px;
      padding: 5px;
      }
  
      .cont-title{
      font-size: 16px; /* Adjust font size */
      color: #ffffff;
      margin: 5px;
      margin-right: 10px;
      padding:10px 5px;
      border-bottom: 0.5px solid grey;
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
      /* Responsive Adjustments */
  @media (max-width: 992px) {
      .operation-btn {
          flex-direction: column;
          align-items: center;
      }
  
      .content-container {
          flex: 1 1 100%;
      }
  
      .tournament-details {
          flex-direction: column;
          align-items: center;
      }
  }
  
  @media (max-width: 768px) {
      .banner-img img {
          max-height: 250px;
      }
  
      .operation-btn button {
          width: 100%;
      }
  }
  
  @media (max-width: 576px) {
      .tournament-details div {
          width: 100%;
      }
  
      .container-row {
          flex-direction: column;
      }
  }

  /* .popup-dialog {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 10000;
}

.popup-content {
    background: #222;
    color: white;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    min-width: 300px;
}

.popup-buttons {
    margin-top: 20px;
    display: flex;
    justify-content: center;
    gap: 15px;
}

.popup-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.popup-btn.confirm {
    background-color: #4CAF50;
    color: white;
}

.popup-btn.cancel {
    background-color: #f44336;
    color: white;
} */

/* Container for dropdown */
.br-controls-dropdown {
    position: relative;
    display: inline-block;
    z-index: 1000;
}

.br-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 15px;
    cursor: pointer;
    color: #f4f4f9;
    background-color: #2c2c44;
    border-radius: 6px;
    font-size: 0.95rem;
    border: none;
    transition: background 0.3s ease;
}
.br-btn:hover {
    background-color: #3a3a5e;
}

.br-dropdown-content {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background-color: #1a1a2e;
    min-width: 220px;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    flex-direction: column;
    gap: 5px;
    padding: 10px 0;
}

/* Show dropdown */
.br-controls-dropdown.show .br-dropdown-content {
    display: flex;
}

.br-btn-small {
    width: 100%;
    padding: 8px 15px;
    text-align: left;
    background: none;
    border: none;
    color: #f4f4f9;
    font-size: 0.9rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
}
.br-btn-small:hover {
    background-color: #2c2c44;
}

</style>

    <div id="popup-dialog" class="popup-dialog">
        <div class="popup-content">
            <p id="popup-message"></p>
            <div class="popup-buttons">
                <button id="popup-confirm" class="popup-btn confirm">OK</button>
                <button id="popup-cancel" class="popup-btn cancel">Cancel</button>
            </div>
        </div>
    </div>

    <div class="banner-cont">
        <div class="banner-container">
            <div class="banner-img-container">
                <div class="banner-img">
                    <?php if (!empty($bannerimg)): ?>
                        <img id="bannerimg" name="bannerimg" src="image.php?tournament_id=<?php echo urlencode($tournament_id); ?>" alt="Banner Image" class="banner-photo-img" />
                    <?php else: ?>
                        <p>No banner image available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tournament">
            <div class="tournament-operation">
                <div class="operation-btn">
                    <button class="organizer-actions">
                        <i class='fa fa-gears'></i> Organizer Actions
                    <ul class="dropdown-menu">
                        <li style="margin-bottom: 10px;">
                            <i class='fa fa-edit'></i>
                            <a href="edit_tour.php?tournament_id=<?php echo urlencode($tournament_id); ?>" style="text-decoration: none; color: #007bff;">
                                Edit Tournament
                            </a>
                        </li>
                        <li style="margin-bottom: 10px;">
                            <i class='fa fa-trash'></i>
                            <a href="javascript:void(0);" style="text-decoration: none; color: #dc3545;" 
                            onclick="confirmDelete(<?php echo (int)$tournament_id; ?>)">
                                Delete Tournament
                            </a>
                        </li>
                    </ul>
                    </button>
                    <button onclick="start_game(<?php echo (int)$tournament_id; ?>)">
                        <i class='fa fa-play'></i> Start Game
                    </button>
                    <button class="options"><i class='fa fa-share-alt'></i> Share</button>

<div class="br-controls-dropdown">
    <button class="br-btn" id="br-options-btn">
        <i class="fas fa-cog"></i> Options
    </button>
    <div class="br-dropdown-content" id="br-dropdown">
        <button class="br-btn-small" onclick="window.location.href='update_br_leaderboard.php?tournament_id=<?php echo $tournament_id; ?>'">
            <i class="fas fa-chart-line"></i> Update Leaderboard
        </button>
        <button class="br-btn-small" onclick="updateBrackets(<?php echo $tournament_id; ?>)">
            <i class="fas fa-project-diagram"></i> Update Brackets
        </button>
        <button class="br-btn-small" onclick="simulateMatches(<?php echo $tournament_id; ?>)">
            <i class="fas fa-dice"></i> Simulate Matches
        </button>
        <button class="br-btn-small" onclick="location.reload()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
</div>

                </div>
            </div>

            <div class="gameuser">
                <p class="titlename"><?php echo htmlspecialchars($selected_game); ?></p>
                Tournament By:
                <p class="titlename"><?php echo htmlspecialchars($creator_name); ?></p>
            </div>
            
            <p class="teams-registered"><?php echo $teams_registered_message; ?></p>
        </div>

        <div class="tournament-details" id="tournament-details">
            <div id="details" class="details active" onclick="showContent('details')">
                <span class="tour-title">Details</span>
            </div>

            <div id="rules" class="rules" onclick="showContent('rules')">
                <span class="tour-title">Rules</span>
            </div>

            <div id="prizes" class="prizes" onclick="showContent('prizes')">
                <span class="tour-title">Prizes</span>
            </div>

            <div id="schedule" class="schedule" onclick="showContent('schedule')">
                <span class="tour-title">Participants</span>
            </div>

            <div id="contact" class="contact" onclick="showContent('contact')">
                <span class="tour-title">Contact</span>
            </div>
        </div>

        <div class="container-row">
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
                <p class="cont-title"><?php echo htmlspecialchars($about); ?></p>
            </div>

            <div class="content-container rules-container" id="rules-container">
                <p class="content-title">Game Critical Rules</p>
                <p class="cont-title"><?php echo htmlspecialchars($rules); ?></p>
            </div>

            <div class="content-container prizes-container" id="prizes-container">
                <p class="content-title">Prize Details</p>
                <p class="cont-title"><?php echo htmlspecialchars($prizes); ?></p>
            </div>

            <div class="content-container schedule-container" id="schedule-container">
                <p class="content-title">Registered Teams</p>
                <p class="cont-title"> </p>
                <?php
                    if (!empty($participants)) {
                        $counter = 1; // Initialize the counter
                        foreach ($participants as $participant) {
                            ?>
                            <div style="margin-bottom: 20px; padding: 10px; border-bottom: 1px solid #ddd; border-radius: 5px; background: #282828;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
                                    <!-- Team Logo -->
                                    <div class="small-banner" style="width: 40px; height: 40px; overflow: hidden; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2);">
                                        <?php
                                        $logo_path = !empty($participant['logo_path']) && file_exists('uploads/' . htmlspecialchars($participant['logo_path']))
                                                    ? 'uploads/' . htmlspecialchars($participant['logo_path'])
                                                    : 'uploads/dash-logo.png';
                                        echo '<img src="' . $logo_path . '" alt="Team Logo" style="width: 100%; height: 100%; object-fit: cover;">';
                                        ?>
                                    </div>
                                    <!-- Team Name -->
                                    <span style="flex: 1; margin-left: 10px; font-weight: bold;"><?php echo $counter . ". " . htmlspecialchars($participant['team_name']); ?></span>
                                    <!-- Remove Team Button -->
                                    <button onclick="confirmRemove(<?php echo (int)$tournament_id; ?>, '<?php echo $match_type; ?>', '<?php echo addslashes($participant['team_name']); ?>')">
                                        <i class="fa fa-trash"></i> Remove Team
                                    </button>
                                </div>

                                <!-- Players Row -->
                                <?php if (!empty($participant['players'])): ?>
                                    <div style="display: flex; align-items: center; margin-left: 50px; gap: 10px; flex-wrap: wrap;">
                                        <?php foreach ($participant['players'] as $player): ?>
                                            <span style="background: #e0e0e0; padding: 5px 10px; border-radius: 20px; font-size: 14px;"><?php echo htmlspecialchars($player); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php
                            $counter++; // Increment the counter
                        }
                    } else {
                        echo "<p>No participants registered yet.</p>";
                    }
                ?>
            </div>

            <div class="content-container contact-container" id="contact-container">
                <p class="content-title">Contact Info</p>
                <p class="cont-title"><?php echo htmlspecialchars($social_media_input); ?></p>
            </div>
        </div>
    </div>
<script src="js/tournament.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropdownBtn = document.getElementById('br-options-btn');
    const dropdownContainer = dropdownBtn.parentElement;

    dropdownBtn.addEventListener('click', function(e) {
        e.stopPropagation(); // prevent closing immediately
        dropdownContainer.classList.toggle('show');
    });

    // Close dropdown when clicking outside
    window.addEventListener('click', function() {
        dropdownContainer.classList.remove('show');
    });
});

// Functions for buttons
// Redirect to update leaderboard page
function updateLeaderboard(tournamentId) {
    if (!tournamentId || isNaN(tournamentId)) return alert("❌ Invalid tournament ID");
    window.location.href = 'update_br_leaderboard.php?tournament_id=' + tournamentId;
}

// Redirect to update brackets page
function updateBrackets(tournamentId) {
    if (!tournamentId || isNaN(tournamentId)) return alert("❌ Invalid tournament ID");
    window.location.href = 'update_br_brackets.php?tournament_id=' + tournamentId;
}

// Redirect to simulate matches page
function simulateMatches(tournamentId) {
    if (!tournamentId || isNaN(tournamentId)) return alert("❌ Invalid tournament ID");
    window.location.href = 'simulate_matches.php?tournament_id=' + tournamentId;
}
</script>

<?php include('footer.php'); ?>
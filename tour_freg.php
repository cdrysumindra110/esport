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
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
      position: relative; /* Needed for dropdown positioning */
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
      position: relative; /* Needed for dropdown positioning */
  }

  .operation-btn button:hover {
      color: black;
  }

  .operation-btn .options {
      background-color: #a75928;
  }

  .operation-btn .options:hover {
      color: black; /* Ensures consistency with hover color */
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
      z-index: 10; /* Ensures dropdown appears above other content */
  }

  .dropdown-menu li {
      padding: 10px;
      display: flex;
      align-items: center;
  }

  .dropdown-menu li i {
      margin-right: 8px; /* Space between icon and text */
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


  .teams-registered {
      font-size: 20px; /* You can adjust the font size as needed */
      color: #fff; /* Set the color to white for contrast */
      margin-top: 15px; /* Add some space above the text */
      font-weight: bold; /* Make the text stand out more */
      text-align: center; /* Center the text horizontally */
  }
  .tournament-details {
      display: flex;
      justify-content: space-around; /* Spreads the items evenly */
      align-items: center; /* Vertically centers the items */
      padding: 20px; /* Adds padding around the container */
      border-top: 1px solid grey;
      border-radius: 8px; /* Optional: adds rounded corners */
  }

  .details, .rules, .prizes, .schedule, .contact {
      width: 100%;
      border-right: 1px solid grey;
      padding: 10px 20px; /* Adds space inside each div */
      color: #ddd;
      border-radius: 5px; /* Rounded corners */
      text-align: center; /* Centers the text inside the div */
      cursor: pointer; /* Adds a pointer on hover */
      transition: background-color 0.3s ease; /* Smooth hover transition */
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

</style>


    </div><div class="banner-cont">
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
            <button 
                class="organizer-actions" 
                onclick="joinTournament(<?php echo $tournament_id; ?>, '<?php echo htmlspecialchars($match_type); ?>')"
            >
                <i class="fas fa-gamepad"></i>&nbsp; Join Tournament
            </button>

            <button class="options" onclick="leaderboard(<?php echo $tournament_id; ?>)">
              <i class="fas fa-cog"></i> Leaderboard
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

    <div class="tournament-details active" id="tournament-details">
        <div id="details" class="details" onclick="showContent('details')">
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

        <div class="content-container contact-container" id="contact-container">
            <p class="content-title">Contact Info</p>
            <p class="cont-title"><?php echo htmlspecialchars($social_media_input); ?></p>
        </div>

        <div class="content-container schedule-container" id="schedule-container">
            <p class="content-title"><i class="fas fa-user"></i>&nbsp;&nbsp;Registered Participants</p>
            <p class="cont-title" style="display: flex; flex-direction: column;">
            <?php
              if (!empty($participants)) {
                  $counter = 1; // Initialize the counter
                  foreach ($participants as $participant) {
                      ?>
                      <div style="display: flex; align-items: center; margin-bottom: 10px;">
                          <!-- Display participant logo dynamically -->
                          <div class="small-banner" style="width: 40px; height: 40px; margin-right: 10px; overflow: hidden; border-radius: 50%; box-shadow: 0 0 5px rgba(0,0,0,0.2);">
                              <?php
                              // Determine the logo path (check if participant has a logo)
                              $logo_path = !empty($participant['logo_path']) && file_exists('uploads/' . htmlspecialchars($participant['logo_path'])) 
                                          ? 'uploads/' . htmlspecialchars($participant['logo_path']) 
                                          : 'uploads/dash-logo.png'; // Fallback to default logo if no participant logo
                              
                              // Display the logo
                              echo '<img src="' . $logo_path . '" alt="Participant Logo" style="width: 100%; height: 100%; object-fit: cover;">';
                              ?>
                          </div>
                          <!-- Display participant team name or player name with the counter -->
                          <span><?php echo $counter . ". " . htmlspecialchars($participant['team_name']); ?></span>
                      </div>
                      <?php
                      $counter++; // Increment the counter
                  }
              } else {
                  echo "<p>No participants registered yet.</p>";
              }
              ?>

            </p>
        </div>
    </div>
</div>

<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
<script>
function showContent(section) {
    // Hide all content containers
    var containers = document.querySelectorAll('.content-container');
    containers.forEach(function(container) {
        container.classList.remove('active');
    });

    // Show the clicked section
    var activeContainer = document.getElementById(section + '-container');
    if (activeContainer) {
        activeContainer.classList.add('active');
    }
}


// Function to show the popup message
function showPopupMessage(message, type) {
  const popup = document.getElementById('popup-message');
  popup.textContent = message;
  popup.className = 'popup-message'; // Reset to default
  if (type === 'success') {
    popup.classList.add('success');
  } else if (type === 'error') {
    popup.classList.add('error');
  }
  popup.style.display = 'block'; // Show the popup
  setTimeout(() => {
    popup.style.display = 'none'; // Hide after 3 seconds
  }, 3000);
}

// Example usage for PHP error and success messages
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($success_message)): ?>
    showPopupMessage("<?php echo $success_message; ?>", 'success');
  <?php elseif (!empty($error_message)): ?>
    showPopupMessage("<?php echo $error_message; ?>", 'error');
  <?php endif; ?>
});


function showContent(section) {
    // Get all tabs and content containers
    var tabs = document.querySelectorAll('.tournament-details > div');
    var containers = document.querySelectorAll('.content-container');

    // Remove the 'active' class and hide all containers
    tabs.forEach(function(tab) {
        tab.classList.remove('active');
    });
    containers.forEach(function(container) {
        container.style.display = 'none';
    });

    // Add the 'active' class to the clicked tab and show the corresponding container
    document.getElementById(section).classList.add('active');
    document.getElementById(section + '-container').style.display = 'block';
}

function joinTournament(tournamentId, matchType) {
    // Check if slots are full (this should be based on the registration count and total teams)
    var slotsFull = <?php echo ($slots_full_message) ? 'true' : 'false'; ?>;
    
    if (slotsFull) {
        // Display error message (if not already displayed)
        document.getElementById("error-message").style.display = "block";
        // Optionally disable the button if not disabled yet
        document.querySelector('.organizer-actions').disabled = true;
        document.querySelector('.organizer-actions').style.backgroundColor = 'grey';
    } else {
        // Proceed with joining the tournament logic (if slots are not full)
        // For example, send an AJAX request to register the user.
    }
}

</script>

<script>
  function joinTournament(tournamentId, matchType) {
    // Redirect to register.php with tournament_id and match_type as query parameters
    window.location.href = 'register.php?tournament_id=' + tournamentId + '&match_type=' + encodeURIComponent(matchType);
  }
  function joinGame(tournamentId) {
    // Redirect to success.php with tournament_id and match_type as query parameters
    window.location.href = 'success.php?tournament_id=' + tournamentId;
  }
  function leaderboard(tournamentId) {
    window.location.href = 'leaderboard.php?tournament_id=' + tournamentId;
  }
</script>

<?php include('footer.php'); ?>
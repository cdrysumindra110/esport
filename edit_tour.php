<?php
// Include the config file for database connection
require_once 'config.php';

// Start the session
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

// Check if the user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

// Get the logged-in user ID
if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

$user_id = $_SESSION['user_id'];

// Retrieve the tournament ID from a GET parameter
$tournament_id = $_GET['tournament_id'] ?? null;

if (!$tournament_id) {
    die("Error: Tournament ID is required.");
}

// Fetch the existing tournament data
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $tournament_id, $user_id);
$stmt->execute();
$tournament_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tournament_data) {
    die("Error: Tournament not found or access denied.");
}

// Fetch the existing bracket data
$stmt = $conn->prepare("SELECT * FROM brackets WHERE tournament_id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$bracket_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch the existing stream data
$stmt = $conn->prepare("SELECT * FROM streams WHERE tournament_id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$stream_data = $stmt->get_result()->fetch_assoc();
$stmt->close();


// Check if form data is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $selected_game = $_POST['selected_game'] ?? null;
    $tname = $_POST['tname'] ?? null;
    $sdate = $_POST['sdate'] ?? null;
    $stime = $_POST['stime'] ?? null;
    $about = $_POST['about'] ?? null;

    // Retrieve stream and social media data
    $provider = $_POST['select-provider'] ?? null;
    $channel_name = $_POST['channel-name'] ?? null;
    $social_media = $_POST['social-media'] ?? null;
    $social_media_input = $_POST['social-media-input'] ?? null;
    
    // Retrieve the 'rules' and 'prizes' fields with default values
    $rules = $_POST['rules'] ?? null;  // Default to null if not set
    $prizes = $_POST['prizes'] ?? null;  // Default to null if not set

    // Validate required fields
    if (empty($selected_game) || empty($tname) || empty($sdate) || empty($stime)) {
        $error_message = "Required fields are missing.";
    } else {
        // Handle file upload and read binary data
        $bannerimg = $tournament_data['bannerimg']; // Default to existing banner if no new file uploaded
        if (isset($_FILES['bannerimg']) && $_FILES['bannerimg']['error'] === UPLOAD_ERR_OK) {
            $bannerimg = file_get_contents($_FILES['bannerimg']['tmp_name']);
        }

        // Update the tournaments table
        $stmt = $conn->prepare("UPDATE tournaments SET selected_game = ?, tname = ?, sdate = ?, stime = ?, bannerimg = ?, about = ? WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ssssssii", $selected_game, $tname, $sdate, $stime, $bannerimg, $about, $tournament_id, $user_id);

        if ($stmt->execute()) {
            // Update the brackets table
            $bracket_type = $_POST['bracket-type'] ?? null;
            $match_type = $_POST['match-type'] ?? null;
            $solo_players = $_POST['solo-players'] ?? null;
            $duo_teams = $_POST['duo-teams'] ?? null;
            $duo_players_per_team = $_POST['duo-players'] ?? null;
            $squad_teams = $_POST['squad-teams'] ?? null;
            $squad_players_per_team = $_POST['squad-players'] ?? null;
            $rounds = $_POST['rounds'] ?? null;
            $placement = $_POST['placement'] ?? null;
            $rules = $_POST['rules'] ?? null;
            $prizes = $_POST['prizes'] ?? null;

            $stmt2 = $conn->prepare("UPDATE brackets SET bracket_type = ?, match_type = ?, solo_players = ?, duo_teams = ?, duo_players_per_team = ?, squad_teams = ?, squad_players_per_team = ?, rounds = ?, placement = ?, rules = ?, prizes = ? WHERE tournament_id = ?");
            $stmt2->bind_param("ssiiiiiiissi", $bracket_type, $match_type, $solo_players, $duo_teams, $duo_players_per_team, $squad_teams, $squad_players_per_team, $rounds, $placement, $rules, $prizes, $tournament_id);

            if ($stmt2->execute()) {
                // Update or insert the stream data if available
                if ($provider && $channel_name) {
                    if ($stream_data) {
                        // Update existing stream
                        $stmt3 = $conn->prepare("UPDATE streams SET provider = ?, channel_name = ?, social_media = ?, social_media_input = ? WHERE tournament_id = ?");
                        $stmt3->bind_param("ssssi", $provider, $channel_name, $social_media, $social_media_input, $tournament_id);
                    } else {
                        // Insert new stream
                        $stmt3 = $conn->prepare("INSERT INTO streams (tournament_id, provider, channel_name, social_media, social_media_input) VALUES (?, ?, ?, ?, ?)");
                        $stmt3->bind_param("issss", $tournament_id, $provider, $channel_name, $social_media, $social_media_input);
                    }
                    $stmt3->execute();
                    $stmt3->close();
                }

                $success_message = "Tournament, brackets, and stream data updated successfully!";
                header('Location: mytournaments.php');
                exit();
            } else {
                $error_message = "Error updating brackets: " . $stmt2->error;
            }
            $stmt2->close();
        } else {
            $error_message = "Error updating tournament: " . $stmt->error;
        }
        $stmt->close();
    }

    // Close connection
    $conn->close();
}
?>



<!DOCTYPE html>
<html lang="en-US">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Esports Website</title>
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="css/icons.css">
    <link rel="stylesheet" href="css/responsee.css">
    <link rel="stylesheet" href="owl-carousel/owl.carousel.css">
    <link rel="stylesheet" href="owl-carousel/owl.theme.css">
    <!-- CUSTOM STYLE -->      
    <link rel="stylesheet" href="css/template-style.css">
    <link rel="stylesheet" href="css/tour_org.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <!-- popup -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

    <!-- Include Quill's CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
  </head>

  <body class="size-1280 primary-color-red">

    <!-- HEADER -->
    <header role="banner" class="position-absolute">
      <!-- Top Bar -->
      <div class="top-bar full-width hide-s hide-m">
        <div class="right">
            <a href="tel:080055544444444" class="text-white text-primary-hover">Phone : +977 9864666601 </a> 
            <span class="sep text-white">|</span> <a href="mailto:infiknightesports@gmail.com" class="text-white text-primary-hover"><i ></i>Email : infiknightesports@gmail.com</a>
        </div>  
      </div>    
      <!-- Top Navigation -->
      <nav class="background-transparent background-transparent-hightlight full-width sticky">
        <div class="s-12 l-2">
          <a href="index.php" class="logo">
            <!-- Logo White Version -->
            <img class="logo-white" src="img/logo.png" alt="">
            <!-- Logo Dark Version -->
            <img class="logo-dark" src="img/logo.png" alt="">
          </a>
        </div>
        <div class="top-nav s-12 l-10">
          <ul class="right chevron">
            <li><a href="index.php">Home</a></li>
            <li><a href="tournaments.php">Tournaments</a></li>
            <li><a href="news.php">News</a></li>
            <li><a href="our-services.php">Our Services</a></li>
             
            <li><a href="organize.php">Organize</a></li>
            <li><a href="about-us.php">About</a></li>
            <li><a href="#"><i class="fas fa-user"></i><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : ''; ?></a>
              <ul>
                <?php if (isset($_SESSION['isSignin']) && $_SESSION['isSignin']): ?>
                  <li><a href="dashboard.php">Profile</a></li>
                  <li><a href="logout.php"><i class='fa fa-sign-out'></i>Signout</a></li>
                <?php else: ?>
                  <li><a href="signin.php">Signin</a></li>
                  <li><a href="signup.php">Signup</a></li>
                <?php endif; ?>
              </ul>
            </li>
          </li>
        </div>
      </nav>
    </header>
    
   <!-- MAIN -->
    <main role="main"> 
        
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
            <div class="line">
              <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
                Organize Tournament
              </h1>
            </div>
          </header>
    </main>

        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>
<!-- ++++++++++++++++++++++++++++++++++++++++++++++Form containrerer+++++++++++++++++++++++++++++++++++ -->
    <div id="tournament-form" class="tournament_form">
      <div id="popup-alert" class="popup hidden">
        <i class="fa fa-exclamation-triangle fa-2x" aria-hidden="true"></i>
        <span id="popup-message">Please select a provider.</span>
        <span id="close-popup" class="close-btn">&times;</span>
      </div>
    </div>
    <!-- partial:index.partial.php -->
    <div class="container-fluid" style="width: 100%;">
            <div class="row justify-content-center" style="width: 100%;">
                <div class="col-11 col-sm-10 col-md-10 col-lg-6 col-xl-5 text-center p-0 mt-3 mb-2" style="width: 100%;">
                    <div class="card px-0 pt-4 pb-0 mt-3 mb-3" >
                        <div class="back-arrow-container">
                          <button id="back-arrow" class="btn btn-light">
                              <i class="fa fa-arrow-left"></i> Back
                          </button>
                        </div>
                        <h2 id="heading">Modify Tournament</h2>
                        <p>Modify the fields below to update the tournament.</p>
                        <form id="msform" action="edit_tour.php?tournament_id=<?php echo $tournament_id; ?>" method="post" enctype="multipart/form-data">
                          <!-- progressbar -->
                          <ul id="progressbar">
                              <li class="active" id="setup"><strong>Setup</strong></li>
                              <li id="brackets"><strong>Brackets</strong></li>
                              <li id="stream"><strong>Stream</strong></li>
                              <li id="publish"><strong>Publish</strong></li>
                          </ul>
                          <div class="progress">
                              <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                          </div>
                          <br> <!-- fieldsets -->
                          
                          <!-- Setup Tournament Section -->
                          <fieldset>
                              <div class="form-card">
                                  <div class="row">
                                      <div class="col-7">
                                          <h2 class="fs-title">Update Tournament</h2>
                                      </div>
                                      <div class="col-5">
                                          <h2 class="steps">Step 1 - 4</h2>
                                      </div>
                                  </div> 
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Selected Game</label>
                                  <input type="text" id="selected_game" name="selected_game" value="<?php echo htmlspecialchars($tournament_data['selected_game']); ?>" readonly />
                                  
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Tournament Name</label>
                                  <input type="text" name="tname" id="tname" value="<?php echo htmlspecialchars($tournament_data['tname']); ?>" placeholder="Tournament Name" required />
                                  
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Date</label>
                                  <input type="date" name="sdate" id="sdate" value="<?php echo htmlspecialchars($tournament_data['sdate']); ?>" required />
                                  
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Time</label>
                                  <input type="time" name="stime" id="stime" value="<?php echo htmlspecialchars($tournament_data['stime']); ?>" required />
                                  
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Game Banner</label>
                                  <input type="file" id="bannerimg" name="bannerimg" accept="image/*" onchange="showPreview(event);" />
                                  <div class="preview">
                                      <?php if ($tournament_data['bannerimg']): ?>
                                          <img id="bannerimg-preview" src="data:image/jpeg;base64,<?php echo base64_encode($tournament_data['bannerimg']); ?>" alt="Tournament Banner" />
                                      <?php else: ?>
                                          <img id="bannerimg-preview" />
                                      <?php endif; ?>
                                  </div>
                                  
                                  <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">About</label>
                                  <div id="editor-container-about" style="height: 200px;"><?php echo htmlspecialchars($tournament_data['about']); ?></div>
                                  <input type="hidden" name="about" id="about">
                              </div>
                              <input type="button" name="next" class="next action-button" value="Next" />
                          </fieldset>

                          <!-- Bracket & Rules Section -->
                          <fieldset>
                              <div class="form-card">
                                  <div class="row">
                                      <div class="col-7">
                                          <h2 class="fs-title">Bracket & Rules</h2>
                                      </div>
                                      <div class="col-5">
                                          <h2 class="steps">Step 2 - 4</h2>
                                      </div>
                                  </div>
                                  <div class="match-details-container">
                                      <h3 class="fs-titleh3">Bracket Details</h3>
                                      <div class="bracket-selection">
                                          <label class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;" for="bracket-type">Bracket Type</label>
                                          <select id="bracket-type" name="bracket-type" class="brac-input">
                                              <option value="battle_royal" <?php echo ($bracket_data['bracket_type'] == 'battle_royal') ? 'selected' : ''; ?>>Battle Royal</option>
                                              <option value="round_robin" <?php echo ($bracket_data['bracket_type'] == 'round_robin') ? 'selected' : ''; ?>>Round Robin</option>
                                              <option value="double_elimination" <?php echo ($bracket_data['bracket_type'] == 'double_elimination') ? 'selected' : ''; ?> disabled>Double Elimination</option>
                                              <option value="single_elimination" <?php echo ($bracket_data['bracket_type'] == 'single_elimination') ? 'selected' : ''; ?> disabled>Single Elimination</option>
                                          </select>
                                      </div>

                                      <div class="match-selection">
                                          <label class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;" for="match-type">Match Type</label>
                                          <select id="match-type" name="match-type" class="brac-input">
                                              <option value="solo" <?php echo ($bracket_data['match_type'] == 'solo') ? 'selected' : ''; ?>>Solo</option>
                                              <option value="duo" <?php echo ($bracket_data['match_type'] == 'duo') ? 'selected' : ''; ?>>Duo</option>
                                              <option value="squad" <?php echo ($bracket_data['match_type'] == 'squad') ? 'selected' : ''; ?>>Squad</option>
                                          </select>
                                      </div>

                                      <!-- Solo Container -->
                                      <div id="solo-container" class="match-container" style="display: <?php echo ($bracket_data['match_type'] == 'solo') ? 'block' : 'none'; ?>;">
                                          <label for="solo-players" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Players</label>
                                          <input type="number" id="solo-players" name="solo-players" class="brac-input" value="<?php echo htmlspecialchars($bracket_data['solo_players']); ?>" />
                                      </div>

                                      <!-- Duo Container -->
                                      <div id="duo-container" class="match-container" style="display: <?php echo ($bracket_data['match_type'] == 'duo') ? 'block' : 'none'; ?>;">
                                          <label for="duo-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                          <input type="number" id="duo-teams" name="duo-teams" class="brac-input" value="<?php echo htmlspecialchars($bracket_data['duo_teams']); ?>" />
                                          
                                          <label for="duo-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                          <input type="number" id="duo-players" name="duo-players" class="brac-input" value="<?php echo htmlspecialchars($bracket_data['duo_players_per_team']); ?>" />
                                      </div>

                                      <!-- Squad Container -->
                                      <div id="squad-container" class="match-container" style="display: <?php echo ($bracket_data['match_type'] == 'squad') ? 'block' : 'none'; ?>;">
                                          <label for="squad-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                          <input type="number" id="squad-teams" name="squad-teams" class="brac-input" value="<?php echo htmlspecialchars($bracket_data['squad_teams']); ?>" />
                                          
                                          <label for="squad-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                          <input type="number" id="squad-players" name="squad-players" class="brac-input" value="<?php echo htmlspecialchars($bracket_data['squad_players_per_team']); ?>" />
                                      </div>

                                      <!-- Placement Points and Rounds -->
                                      <label for="rounds" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Rounds</label>
                                      <select id="rounds" name="rounds" class="brac-input">
                                          <option value="1" <?php echo ($bracket_data['rounds'] == '1') ? 'selected' : ''; ?>>1</option>
                                          <option value="2" <?php echo ($bracket_data['rounds'] == '2') ? 'selected' : ''; ?>>2</option>
                                          <option value="3" <?php echo ($bracket_data['rounds'] == '3') ? 'selected' : ''; ?>>3</option>
                                          <option value="4" <?php echo ($bracket_data['rounds'] == '4') ? 'selected' : ''; ?>>4</option>
                                          <option value="5" <?php echo ($bracket_data['rounds'] == '5') ? 'selected' : ''; ?>>5</option>
                                          <option value="6" <?php echo ($bracket_data['rounds'] == '6') ? 'selected' : ''; ?>>6</option>
                                      </select>

                                      <h3 class="fs-titleh3">Placement Point System</h3>
                                      <label for="placement" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Placement</label>
                                      <textarea id="placement" name="placement" class="brac-input" rows="7"><?php echo htmlspecialchars($bracket_data['placement']); ?></textarea>
                                  </div>
                              </div>
                              <input type="button" name="next" class="next action-button" value="Next" />
                              <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                          </fieldset>

                          <!-- Streams Section -->
                          <fieldset>
                              <div class="form-card">
                                  <div class="row">
                                      <div class="col-7">
                                          <h2 class="fs-title">Streams</h2>
                                      </div>
                                      <div class="col-5">
                                          <h2 class="steps">Step 3 - 4</h2>
                                      </div>
                                  </div>
                                  <main class="main-container section-padding">
                                      <div class="unique-input-field">
                                          <label for="select-provider" class="unique-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Select Provider</label>
                                          <div class="social-media-row">
                                              <select id="select-provider" name="select-provider" class="unique-select">
                                                  <option value="twitch" <?php echo (isset($stream_data['provider']) && $stream_data['provider'] == 'twitch') ? 'selected' : ''; ?>>Twitch</option>
                                                  <option value="youtube" <?php echo (isset($stream_data['provider']) && $stream_data['provider'] == 'youtube') ? 'selected' : ''; ?>>YouTube</option>
                                                  <option value="facebook" <?php echo (isset($stream_data['provider']) && $stream_data['provider'] == 'facebook') ? 'selected' : ''; ?>>Facebook</option>
                                              </select>
                                              <input type="text" id="channel-name" name="channel-name" class="dynamic-input" value="<?php echo htmlspecialchars($stream_data['channel_name']); ?>" placeholder="Enter your Channel Name" />
                                          </div>
                                          <label for="social-media" class="unique-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">How players will contact you?</label>
                                          <div class="social-media-row">
                                              <select id="social-media" name="social-media" class="unique-select">
                                                  <option value="facebook" <?php echo ($stream_data['social_media'] == 'facebook') ? 'selected' : ''; ?>>Facebook</option>
                                                  <option value="twitter" <?php echo ($stream_data['social_media'] == 'twitter') ? 'selected' : ''; ?>>Twitter</option>
                                                  <option value="discord" <?php echo ($stream_data['social_media'] == 'discord') ? 'selected' : ''; ?>>Discord</option>
                                                  <option value="instagram" <?php echo ($stream_data['social_media'] == 'instagram') ? 'selected' : ''; ?>>Instagram</option>
                                                  <option value="linkedin" <?php echo ($stream_data['social_media'] == 'linkedin') ? 'selected' : ''; ?>>LinkedIn</option>
                                              </select>
                                              <input id="social-media-input" name="social-media-input" class="dynamic-input" type="text" value="<?php echo htmlspecialchars($stream_data['social_media_input']); ?>" placeholder="Enter your username">
                                          </div>
                                      </div>
                                      
                                      <dl class="accordion">
                                        <dt>Critical Rules</dt>
                                        <dd>
                                            <div id="editor-container-rules" style="height: 200px;">
                                                <?php echo htmlspecialchars(isset($bracket_data['rules']) ? $bracket_data['rules'] : ''); ?>
                                            </div>
                                            <input type="hidden" name="rules" id="rules" value="<?php echo htmlspecialchars(isset($bracket_data['rules']) ? $bracket_data['rules'] : ''); ?>">
                                        </dd>
                                        <dt>Prizes</dt>
                                        <dd>
                                            <div id="editor-container-prizes" style="height: 200px;">
                                                <?php echo htmlspecialchars(isset($bracket_data['prizes']) ? $bracket_data['prizes'] : ''); ?>
                                            </div>
                                            <input type="hidden" name="prizes" id="prizes" value="<?php echo htmlspecialchars(isset($bracket_data['prizes']) ? $bracket_data['prizes'] : ''); ?>">
                                        </dd>
                                    </dl>
                                  </main>
                              </div>
                              <input type="submit" name="next" class="next action-button" value="Submit" />
                              <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                          </fieldset>
                            
                          <fieldset>
                              <div class="form-card">
                                  <div class="row">
                                      <div class="col-7"> 
                                          <h2 class="fs-title">Finish:</h2>    
                                      </div>
                                      <div class="col-5">
                                          <h2 class="steps">Step 4 - 4</h2>
                                      </div>
                                  </div> 
                                  <br><br>
                                  <h2 class="purple-text text-center"><strong>Tournament Created</strong></h2> 
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-3">
                                          <img id="final-banner-img" src="" class="fit-image" style="   width: 100%; max-width: 400px; height: auto; object-fit: cover; ">
                                      </div>
                                  </div> 
                                  <br><br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                          <h5 class="purple-text text-center" id="final-tournament-name"></h5>
                                          <p id="final-tournament-start-date"></p>
                                      </div>
                                  </div>
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                          <a href="mytournaments.php" class="button-custom">View Tournament</a>
                                      </div>
                                  </div>
                              </div>
                          </fieldset>
                      </form>
                    </div>
                </div>
            </div>
          </div>

    
    <!-- FOOTER -->
    <footer>
      <!-- Social -->
      <div class="background-primary padding text-center">
        <a href="#"><i class="icon-facebook_circle text-size-30 text-white"></i></a> 
        <a href="#"><i class="icon-twitter_circle text-size-30 text-white"></i></a>
        <a href="#"><i class="icon-google_plus_circle text-size-30 text-white"></i></a>
        <a href="#"><i class="icon-instagram_circle text-size-30 text-white"></i></a> 
        <a href="#"><i class="icon-linked_in_circle text-size-30 text-white"></i></a>                                                                       
      </div>
      <!-- Animated Logos -->
      <div class="container-animated sticky" id="logo-container">
        <div class="scrollable-container">
          <button class="animated-btn left-button">&nbsp;&nbsp;&nbsp;&nbsp;We are Trusted By:&nbsp;&nbsp;&nbsp;&nbsp;</button>
          <div class="logos">
            <img src="img/logo/ESports.jpg" alt="Esports" class="image">
            <img src="img/logo/amd.jpg" alt="AMD" class="image">
            <img src="img/logo/redbull.jpg" alt="Red Bull" class="image">
            <img src="img/logo/unicef.jpg" alt="UNICEF" class="image">
            <img src="img/logo/tencent.jpg" alt="Tencent" class="image">
            <img src="img/logo/KoHire.png" alt="KoHire" class="image">
            <img src="img/logo/masterportfolio-banner-dark.png" alt="masterportfolio-banner-dark" class="image">
            <img src="img/logo/Empyre.png" alt="Empyre" class="image">
          </div>
            <button onclick="window.location.href='our-services.php'" class="animated-btn right-button">&nbsp;&nbsp;Become our Client&nbsp;&nbsp;</button>
        </div>
      </div>
      <section class="section background-dark">
        <!-- Main Footer -->
        <div class="line"> 
          <div class="margin2x">
            <div class="hide-s hide-m hide-l xl-2">
              <img src="img/logo.png" alt="">
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong">Our Mission</h4>
               <p style="text-align: justify;">
                To create a thriving esports ecosystem where players can showcase their skills, 
                teams can compete at the highest level, and fans can experience the excitement 
                of world-class gaming events.
               </p>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Useful Links</h4> 
               <a class="text-primary-hover" href="index.php">Home</a><br>
               <a class="text-primary-hover" href="news.php">News</a><br>     
               <a class="text-primary-hover" href="our-services.php">Contact Us</a><br>
               <a class="text-primary-hover" href="about-us.php">About Us</a><br>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Term of Use</h4>
               <a class="text-primary-hover" href="faq.php">FAQ</a><br>
               <a class="text-primary-hover" href="privacy-policy.php">Privacy Policy</a><br>
               <a class="text-primary-hover" href="disclaimer.php">Disclaimer</a><br>
               <a class="text-primary-hover" href="terms-of-use.php">Terms Of Use</a>
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong margin-m-top-30">Contact Us</h4>
                <a class="text-primary-hover" href="tel:+977 9864666601"><i class="icon-sli-screen-smartphone text-primary"></i> +977 9864666601</a><br>
                <a class="text-primary-hover" href="mailto:infiknightesports@gmail.com"><i class="fa-solid fa-envelope text-primary"></i> infiknightesports@gmail.com</a><br>
                <a class="text-primary-hover" href="https://maps.app.goo.gl/grg9akhzXTNkd1yU7"><i class="fa-solid fa-map-marker-alt text-primary"></i> Bafal Marga, Kathmandu, Nepal</a>
            </div>
          </div>  
        </div>    
      </section>
      <div class="background-dark">
        <hr class="break margin-top-bottom-0" style="border-color: #777;">
      </div>
      <!-- Bottom Footer -->
      <section class="padding-2x background-dark full-width">
        <div class="full-width">
          <div class="s-12 l-6">
            <p class="text-size-16 margin-bottom-0">Copyright 2024 &Sigma;Indra65 , MK38 - BCA 2K22</p>
            <p class="text-size-12">Copyright 2024 InfiKnight Esports. All Rights Reserved.</p>
          </div>
          <div class="s-12 l-6">
            <a class="right text-size-12 text-primary-hover" href="#" title="Team InfiKnight">Developed by Team <span style="font-size: 25px;">&infin;</span>
            </a>
          </div>
        </div>  
      </section>
    </footer>
    <script type="text/javascript" src="./js/responsee.js"></script>
    <script type="text/javascript" src="./owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="./js/template-scripts.js"></script> 
    <script src="./js/tour_org.js"></script>

    <!-- Popup page Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        myModal.show();
    }, 1000); // 1-second delay before modal appears
});

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill editors
    var quillAbout = new Quill('#editor-container-about', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    var quillRules = new Quill('#editor-container-rules', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

    var quillPrizes = new Quill('#editor-container-prizes', {
        theme: 'snow',
        modules: {
            toolbar: [
                ['bold', 'italic', 'underline', 'strike'],
                ['blockquote', 'code-block'],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'script': 'sub' }, { 'script': 'super' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                [{ 'direction': 'rtl' }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                ['clean']
            ]
        }
    });

        // Update hidden input fields with Quill plain text content before form submission
        document.querySelector('form').addEventListener('submit', function() {
        document.getElementById('about').value = quillAbout.getText().trim();
        document.getElementById('rules').value = quillRules.getText().trim();
        document.getElementById('prizes').value = quillPrizes.getText().trim();
    });
});




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
</script>

<!-- JavaScript -->
<script>
    // Initialize Quill Editors
    const aboutEditor = new Quill('#editor-container-about', { theme: 'snow' });
    aboutEditor.setContents(<?php echo json_encode($about_content); ?>);
    aboutEditor.on('text-change', function() {
        document.getElementById('about').value = aboutEditor.root.innerHTML;
    });

    const rulesEditor = new Quill('#editor-container-rules', { theme: 'snow' });
    rulesEditor.setContents(<?php echo json_encode($rules_content); ?>);
    rulesEditor.on('text-change', function() {
        document.getElementById('rules').value = rulesEditor.root.innerHTML;
    });

    // Show/hide match containers based on selected type
    document.getElementById('match-type').addEventListener('change', function () {
        document.getElementById('solo-container').style.display = this.value === 'solo' ? 'block' : 'none';
        document.getElementById('duo-container').style.display = this.value === 'duo' ? 'block' : 'none';
    });

    // Show selected image preview
    function showPreview(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const preview = document.getElementById('bannerimg-preview');
            preview.src = reader.result;
            preview.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
<script>
    // Get today's date in the format YYYY-MM-DD
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('sdate').setAttribute('min', today);
</script>
<script>
document.querySelector('input[name="next"]').addEventListener('click', function(event) {
    event.preventDefault(); // Prevent the default behavior of form submission
    
    // Get values from the form fields
    const tournamentName = document.getElementById('tname').value;
    const startDate = document.getElementById('sdate').value;
    const bannerImage = document.getElementById('bannerimg-preview').src;

    // Set the preview content in Step 4
    document.getElementById('final-tournament-name').innerText = tournamentName;
    document.getElementById('final-tournament-start-date').innerText = `Start Date: ${startDate}`;
    document.getElementById('final-banner-img').src = bannerImage;

    // Optionally, proceed to the next step or submit the form
    // Example:
    // document.getElementById('msform').submit();
});

function showPreview(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var previewImage = document.getElementById('bannerimg-preview');
        previewImage.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

  </script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</body>
</html>
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

    // Validate required fields
    if (empty($selected_game) || empty($tname) || empty($sdate) || empty($stime)) {
        $error_message = "Required fields are missing.";
    } else {
        // Handle file upload and read binary data
        $bannerimg = null;
        if (isset($_FILES['bannerimg']) && $_FILES['bannerimg']['error'] === UPLOAD_ERR_OK) {
            $bannerimg = file_get_contents($_FILES['bannerimg']['tmp_name']);
        }

        // Insert into tournaments table
        if (empty($error_message)) {
            $stmt = $conn->prepare("INSERT INTO tournaments (user_id, selected_game, tname, sdate, stime, bannerimg, about) VALUES (?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                $error_message = "Prepare failed: " . $conn->error;
            } else {
                $stmt->bind_param("issssss", $user_id, $selected_game, $tname, $sdate, $stime, $bannerimg, $about);

                if ($stmt->execute()) {
                    // Get the last inserted ID
                    $tournament_id = $stmt->insert_id;

                    // Insert into brackets table
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

                    // Insert into brackets table
                    $stmt2 = $conn->prepare("INSERT INTO brackets (tournament_id, bracket_type, match_type, solo_players, duo_teams, duo_players_per_team, squad_teams, squad_players_per_team, rounds, placement, rules, prizes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    if (!$stmt2) {
                        $error_message = "Prepare failed: " . $conn->error;
                    } else {
                        $stmt2->bind_param("issiiiiiiiss", $tournament_id, $bracket_type, $match_type, $solo_players, $duo_teams, $duo_players_per_team, $squad_teams, $squad_players_per_team, $rounds, $placement, $rules, $prizes);
                        if ($stmt2->execute()) {
                            // Insert stream data if available
                            if ($provider && $channel_name) {
                                $stmt3 = $conn->prepare("INSERT INTO streams (tournament_id, provider, channel_name, social_media, social_media_input) VALUES (?, ?, ?, ?, ?)");
                                if (!$stmt3) {
                                    $error_message = "Prepare failed: " . $conn->error;
                                } else {
                                    $stmt3->bind_param("issss", $tournament_id, $provider, $channel_name, $social_media, $social_media_input);
                                    if ($stmt3->execute()) {
                                        $success_message = "Tournament, brackets, and related data successfully inserted!";
                                        header('Location: mytournaments.php');
                                        exit();
                                    } else {
                                        $error_message = "Error inserting stream: " . $stmt3->error;
                                    }
                                    $stmt3->close();
                                }
                            } else {
                                $success_message = "Tournament and brackets successfully inserted!";
                                header('Location: mytournaments.php');
                                exit();
                            }
                        } else {
                            $error_message = "Error inserting brackets: " . $stmt2->error;
                        }
                        $stmt2->close();
                    }
                } else {
                    $error_message = "Error inserting tournament: " . $stmt->error;
                }
                $stmt->close();
            }
        }
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
    <link rel="stylesheet" href="./css/template-style.css">
    <link rel="stylesheet" href="./css/tour_org.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <!-- popup -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
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
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="width: 100%; height: 100%; object-fit: cover;background-image:url(img/battleground.gif)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Organize Tournament</center>
            </h1>
          
          </div>
  
        </header>
        
      </article>  

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
                        <h2 id="heading">Create Tournament</h2>
                        <p>Fill all form field to go to next step</p>
                        <form id="msform" action="create_tour.php" method="post" enctype="multipart/form-data">
                            <!-- progressbar -->
                            <ul id="progressbar">
                                <li class="active" id="setup"><strong>Setup</strong></li>
                                <li id="brackets"><strong>Brackets</strong></li>
                                <li id="stream"><strong>Stream</strong></li>
                                <li id="publish"><strong>Publish</strong></li>
                            </ul>
                            <div class="progress" >
                                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuemin="0" aria-valuemax="100"></div>
                            </div> <br> <!-- fieldsets -->
                            <fieldset>
                                <div class="form-card">
                                    <div class="row">
                                        <div class="col-7">
                                            <h2 class="fs-title">Setup Tournament</h2>
                                        </div>
                                        <div class="col-5">
                                            <h2 class="steps">Step 1 - 4</h2>
                                        </div>
                                    </div> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Selected Game</label>
                                      <input type="text" id="selected_game" name="selected_game" readonly />
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Tournament Name</label> 
                                      <input type="text" name="tname" id="tname" placeholder="Tournament Name" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Date</label> 
                                      <input type="date" name="sdate" id="sdate" placeholder="Start Date(DD/MM/YYYY)" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Time</label> 
                                      <input type="time" name="stime" id="stime" placeholder="Time displayed in Time displayed in +0545" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Game Banner</label>
                                      <input type="file" id="bannerimg" name="bannerimg" accept="image/*" onchange="showPreview(event);" required />
                                      <div class="preview">
                                        <img id="bannerimg-preview">
                                      </div>
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">About</label>
                                      <div id="editor-container-about" style="height: 200px; display: block !important; height: 200px !important;"></div>
                                      <input type="hidden" name="about" id="about">                           
                                    </div> 
                                      <input type="button" name="next" class="next action-button" value="Next" />
                            </fieldset>
                            <!-- Bracket & Rules -->
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
                                      <div class="bracket-selection">
                                        <label class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;" for="bracket-type">Bracket Type</label>
                                        <select id="bracket-type" name="bracket-type" class="brac-input">
                                          <option value="battle_royal">Battle Royal</option>
                                          <option value="round_robin">Round Robin</option>
                                          <option value="double_elimination" disabled>Double Elimination</option>
                                          <option value="single_elimination" disabled>Single Elimination</option>
                                        </select>
                                      </div>
                                    
                                      <div class="match-selection">
                                        <label class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;" for="match-type">Match Type</label>
                                        <select id="match-type" name="match-type" class="brac-input">
                                          <option value="solo">Solo</option>
                                          <option value="duo">Duo</option>
                                          <option value="squad">Squad</option>
                                        </select>
                                      </div>
                                    
                                      <!-- Solo Container -->
                                      <div id="solo-container" class="match-container" style="display: none;">
                                        <label for="solo-players" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Players</label>
                                        <input type="number" id="solo-players" name="solo-players" class="brac-input">
                                      </div>
                                    
                                      <!-- Duo Container -->
                                      <div id="duo-container" class="match-container" style="display: none;">
                                        <label for="duo-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                        <input type="number" id="duo-teams" name="duo-teams" class="brac-input">
                                    
                                        <label for="duo-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                        <input type="number" id="duo-players" name="duo-players" class="brac-input"></div>
                                    
                                      <!-- Squad Container -->
                                      <div id="squad-container" class="match-container" style="display: none;">
                                        <label for="squad-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                        <select id="squad-teams" name="squad-teams" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                          <option value="7">7</option>
                                          <option value="8">8</option>
                                          <option value="9">9</option>
                                          <option value="10">10</option>
                                          <option value="11">11</option>
                                          <option value="12">12</option>
                                          <option value="13">13</option>
                                          <option value="14">14</option>
                                          <option value="15">15</option>
                                          <option value="16">16</option>
                                          <option value="17">17</option>
                                          <option value="18">18</option>
                                          <option value="19">19</option>
                                          <option value="20">20</option>
                                        </select>
                                    
                                        <label for="squad-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                        <input type="number" id="squad-players" name="squad-players" class="brac-input">
                                      </div>


                                      <label for="rounds" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Rounds</label>
                                        <select id="rounds" name="rounds" class="brac-input">
                                          <option value="1">1</option>
                                          <option value="2">2</option>
                                          <option value="3">3</option>
                                          <option value="4">4</option>
                                          <option value="5">5</option>
                                          <option value="6">6</option>
                                        </select>

                                        <!-- <label for="advancement" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Group Advancement</label>
                                        <select id="advancement" name="advancement" class="brac-input">
                                            <option value="random">Random</option>
                                            <option value="elimination">Elimination</option>
                                        </select> -->
                                    
                                        <h3 class="fs-titleh3">Placement Point System</h3>
                                        <label for="placement" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Placement</label>
                                        <textarea id="placement" name="placement" class="brac-input" rows="7" placeholder="#1 = 10pts\n#2 = 8pts\n#3 = 6pts\n#Kill = 1pt"></textarea>
                                    </div>
                                    
                                </div> 
                                <input type="button" name="next" class="next action-button" value="Next" /> 
                                <input type="button" name="previous" class="previous action-button-previous" value="Previous" />
                            </fieldset>

                              <!-- Streams -->
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
                                                    <option value="">Select Provider</option>
                                                    <option value="twitch">Twitch</option>
                                                    <option value="youtube">YouTube</option>
                                                    <option value="facebook">Facebook</option>
                                                  </select>
                                                <input type="text" id="channel-name" name="channel-name" class="dynamic-input" placeholder="Enter your Channel Name" />
                                            </div>

                                            <label for="social-media" class="unique-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">How players will contact you ?</label>
                                            <div class="social-media-row">
                                                <select id="social-media" name="social-media" class="unique-select">
                                                    <option value="">Select a social media</option>
                                                    <option value="facebook">Facebook</option>
                                                    <option value="twitter">Twitter</option>
                                                    <option value="discord">Discord</option>
                                                    <option value="instagram">Instagram</option>
                                                    <option value="linkedin">LinkedIn</option>
                                                    <!-- Add more social media options here -->
                                                </select>
                                                <input id="social-media-input" name="social-media-input" class="dynamic-input" type="text" placeholder="Enter your username">
                                            </div>
                                        </div>
                                        
                                       <dl class="accordion">
                                           <dt>Critical Rules</dt>
                                           <dd>
                                           <div id="editor-container-rules" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="rules" id="rules">
                                           </dd>
                                           <dt>Prizes</dt>
                                           <dd>
                                           <div id="editor-container-prizes" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="prizes" id="prizes">
                                           </dd>
                                       </dl>
                                   </main>

                                    
                                </div> 
                                  <input type="submit" name="next" class="next action-button" id="create_tour" value="Submit" /> 
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
  <script>
    // Get today's date in the format YYYY-MM-DD
    const today = new Date().toISOString().split('T')[0];

    document.getElementById('sdate').setAttribute('min', today);
</script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</body>
</html>
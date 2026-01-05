<?php
include('header.php');

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$user_id = $_SESSION['user_id'];

// Initialize error/success messages
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic validation
    $selected_game = $_POST['selected_game'] ?? '';
    $tname = $_POST['tname'] ?? '';
    $sdate = $_POST['sdate'] ?? '';
    $stime = $_POST['stime'] ?? '';
    $about = $_POST['about'] ?? '';

    // Debug: Check what's being received
    error_log("POST Data: " . print_r($_POST, true));
    error_log("FILES Data: " . print_r($_FILES, true));

    if (!$selected_game || !$tname || !$sdate || !$stime) {
        $error_message = "Required fields missing: Game, Tournament Name, Start Date, and Start Time are required.";
    } else {
        /* ---------- IMAGE ---------- */
        $bannerimg = null;
        if (!empty($_FILES['bannerimg']['tmp_name']) && $_FILES['bannerimg']['error'] === UPLOAD_ERR_OK) {
            $bannerimg = file_get_contents($_FILES['bannerimg']['tmp_name']);
        }

        /* ---------- BRACKETS ---------- */
        $bracket_type = $_POST['bracket-type'] ?? null;
        $match_type = $_POST['match-type'] ?? null;
        $solo_players = $_POST['solo-players'] ?? null;
        $duo_teams = $_POST['duo-teams'] ?? null;
        $duo_players = $_POST['duo-players'] ?? null;
        $squad_teams = $_POST['squad-teams'] ?? null;
        $squad_players = $_POST['squad-players'] ?? null;
        $rounds = $_POST['rounds'] ?? null;
        $placement = $_POST['placement'] ?? null;
        $rules = $_POST['rules'] ?? null;
        $prizes = $_POST['prizes'] ?? null;

        /* ---------- STREAM ---------- */
        $provider = $_POST['select-provider'] ?? null;
        $channel_name = $_POST['channel-name'] ?? null;
        $social_media = $_POST['social-media'] ?? null;
        $social_media_input = $_POST['social-media-input'] ?? null;

        /* ---------- TRANSACTION ---------- */
        $conn->begin_transaction();

        try {
            /* Tournament */
            $stmt = $conn->prepare("
                INSERT INTO tournaments 
                (user_id, selected_game, tname, sdate, stime, bannerimg, about)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            // Check if statement was prepared successfully
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("issssss", 
                $user_id, 
                $selected_game, 
                $tname, 
                $sdate, 
                $stime, 
                $bannerimg, 
                $about
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $tournament_id = $stmt->insert_id;
            $stmt->close();

            /* Brackets */
            $stmt2 = $conn->prepare("
                INSERT INTO brackets
                (tournament_id, bracket_type, match_type, solo_players, duo_teams,
                 duo_players_per_team, squad_teams, squad_players_per_team, rounds,
                 placement, rules, prizes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            if (!$stmt2) {
                throw new Exception("Prepare failed for brackets: " . $conn->error);
            }
            
            // Convert empty strings to NULL for integer fields
            $solo_players = ($solo_players === '') ? null : (int)$solo_players;
            $duo_teams = ($duo_teams === '') ? null : (int)$duo_teams;
            $duo_players = ($duo_players === '') ? null : (int)$duo_players;
            $squad_teams = ($squad_teams === '') ? null : (int)$squad_teams;
            $squad_players = ($squad_players === '') ? null : (int)$squad_players;
            $rounds = ($rounds === '') ? null : (int)$rounds;
            
            $stmt2->bind_param(
                "issiiiiiiiss",
                $tournament_id,
                $bracket_type,
                $match_type,
                $solo_players,
                $duo_teams,
                $duo_players,
                $squad_teams,
                $squad_players,
                $rounds,
                $placement,
                $rules,
                $prizes
            );
            
            if (!$stmt2->execute()) {
                throw new Exception("Execute failed for brackets: " . $stmt2->error);
            }
            
            $stmt2->close();

            /* Streams (optional) */
            if ($provider && $channel_name) {
                $stmt3 = $conn->prepare("
                    INSERT INTO streams
                    (tournament_id, provider, channel_name, social_media, social_media_input)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                if ($stmt3) {
                    $stmt3->bind_param(
                        "issss",
                        $tournament_id,
                        $provider,
                        $channel_name,
                        $social_media,
                        $social_media_input
                    );
                    
                    if (!$stmt3->execute()) {
                        error_log("Stream insertion failed: " . $stmt3->error);
                    }
                    
                    $stmt3->close();
                }
            }

            $conn->commit();
            $success_message = "Tournament created successfully!";
            
            // Redirect after successful creation
            header("Location: mytournaments.php?success=1");
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Failed to create tournament: " . $e->getMessage();
            error_log("Tournament creation error: " . $e->getMessage());
        }
    }
}
?>

    <link rel="stylesheet" href="./css/tour_org.css">
    <!-- popup -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

    <!-- Include Quill's CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">

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

<!-- Tournament Form containrerer -->
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
                                <input type="button" name="next" class="next action-button" id="last-nextBtn" value="Next" /> 
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


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script src="./js/tour_org.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
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

    // Set default text for Quill editors
    quillAbout.setText('About this tournament...');
    quillRules.setText('1. Be respectful to all players\n2. No cheating or hacking\n3. Follow the game rules');
    quillPrizes.setText('1st Place: $100\n2nd Place: $50\n3rd Place: $25');

    // Update hidden input fields with Quill content before form submission
    document.getElementById('msform').addEventListener('submit', function(e) {
        // Only update if not already submitted
        if (!this.classList.contains('submitting')) {
            e.preventDefault();
            
            // Get content from Quill editors
            document.getElementById('about').value = quillAbout.root.innerHTML;
            document.getElementById('rules').value = quillRules.root.innerHTML;
            document.getElementById('prizes').value = quillPrizes.root.innerHTML;
            
            // Validate required fields
            const requiredFields = [
                document.getElementById('selected_game'),
                document.getElementById('tname'),
                document.getElementById('sdate'),
                document.getElementById('stime')
            ];
            
            let isValid = true;
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = 'red';
                    showPopupMessage(`Please fill in: ${field.previousElementSibling?.textContent || 'Required field'}`, 'error');
                } else {
                    field.style.borderColor = '';
                }
            }
            
            if (isValid) {
                // Mark form as submitting to prevent multiple submissions
                this.classList.add('submitting');
                
                // Show loading state
                const submitBtn = document.querySelector('input[name="next"][type="submit"]');
                if (submitBtn) {
                    submitBtn.value = 'Creating...';
                    submitBtn.disabled = true;
                }
                
                // Submit the form
                this.submit();
            }
        }
    });

    // Function to show the popup message
    function showPopupMessage(message, type) {
        const popup = document.getElementById('popup-alert');
        const popupMessage = document.getElementById('popup-message');
        
        if (popup && popupMessage) {
            popupMessage.textContent = message;
            popup.className = 'popup';
            
            if (type === 'success') {
                popup.classList.add('success');
            } else if (type === 'error') {
                popup.classList.add('error');
            }
            
            popup.classList.remove('hidden');
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                popup.classList.add('hidden');
            }, 5000);
        }
        
        // Also log to console for debugging
        console.log(`${type.toUpperCase()}: ${message}`);
    }

    // Close popup handler
    const closePopup = document.getElementById('close-popup');
    if (closePopup) {
        closePopup.addEventListener('click', function() {
            document.getElementById('popup-alert').classList.add('hidden');
        });
    }

    // Show PHP messages if any
    <?php if (!empty($success_message)): ?>
        showPopupMessage("<?php echo addslashes($success_message); ?>", 'success');
    <?php elseif (!empty($error_message)): ?>
        showPopupMessage("<?php echo addslashes($error_message); ?>", 'error');
    <?php endif; ?>

    // Function to show image preview
    function showPreview(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var previewImage = document.getElementById('bannerimg-preview');
            if (previewImage) {
                previewImage.src = reader.result;
                
                // Also update the final preview in step 4
                var finalPreview = document.getElementById('final-banner-img');
                if (finalPreview) {
                    finalPreview.src = reader.result;
                }
            }
        };
        reader.readAsDataURL(event.target.files[0]);
    }

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    const dateInput = document.getElementById('sdate');
    if (dateInput) {
        dateInput.setAttribute('min', today);
    }

    // Match type selection handler
    const matchTypeSelect = document.getElementById('match-type');
    if (matchTypeSelect) {
        matchTypeSelect.addEventListener('change', function() {
            // Hide all containers first
            document.querySelectorAll('.match-container').forEach(container => {
                container.style.display = 'none';
            });
            
            // Show the selected container
            const selectedValue = this.value;
            if (selectedValue === 'solo') {
                document.getElementById('solo-container').style.display = 'block';
            } else if (selectedValue === 'duo') {
                document.getElementById('duo-container').style.display = 'block';
            } else if (selectedValue === 'squad') {
                document.getElementById('squad-container').style.display = 'block';
            }
        });
        
        // Trigger change event to show initial state
        matchTypeSelect.dispatchEvent(new Event('change'));
    }

    // Back button handler
    const backButton = document.getElementById('back-arrow');
    if (backButton) {
        backButton.addEventListener('click', function() {
            window.history.back();
        });
    }

    // Update final preview when moving to step 4
    const nextButtons = document.querySelectorAll('.next.action-button');
    nextButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Update preview in step 4
            const tournamentName = document.getElementById('tname').value;
            const startDate = document.getElementById('sdate').value;
            
            if (tournamentName) {
                const finalName = document.getElementById('final-tournament-name');
                if (finalName) {
                    finalName.textContent = tournamentName;
                }
            }
            
            if (startDate) {
                const finalDate = document.getElementById('final-tournament-start-date');
                if (finalDate) {
                    const formattedDate = new Date(startDate).toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });
                    finalDate.textContent = `Starts: ${formattedDate}`;
                }
            }
        });
    });
});
</script>
<?php include('footer.php'); ?>
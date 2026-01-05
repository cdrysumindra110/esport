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

// Check if tournament ID is provided
if (isset($_GET['tournament_id']) && !empty($_GET['tournament_id'])) {
    $tournament_id = intval($_GET['tournament_id']);
} elseif (isset($_GET['id']) && !empty($_GET['id'])) {
    $tournament_id = intval($_GET['id']);
} else {
    header('Location: mytournaments.php');
    exit();
}

// Initialize error/success messages
$error_message = '';
$success_message = '';

// Fetch existing tournament data
$tournament_data = null;
$brackets_data = null;
$streams_data = null;

// Get tournament details - USING 'id' AS SHOWN IN YOUR DATABASE STRUCTURE
$stmt = $conn->prepare("
    SELECT * FROM tournaments 
    WHERE id = ? AND user_id = ?
");
$stmt->bind_param("ii", $tournament_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$tournament_data = $result->fetch_assoc();
$stmt->close();

// If tournament doesn't exist or doesn't belong to user
if (!$tournament_data) {
    header('Location: mytournaments.php');
    exit();
}

// Get brackets data
$stmt = $conn->prepare("SELECT * FROM brackets WHERE tournament_id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$brackets_data = $result->fetch_assoc();
$stmt->close();

// Get streams data
$stmt = $conn->prepare("SELECT * FROM streams WHERE tournament_id = ?");
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();
$streams_data = $result->fetch_assoc();
$stmt->close();

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
        $bannerimg = $tournament_data['bannerimg']; // Keep existing image by default
        
        if (!empty($_FILES['bannerimg']['tmp_name']) && $_FILES['bannerimg']['error'] === UPLOAD_ERR_OK) {
            $bannerimg = file_get_contents($_FILES['bannerimg']['tmp_name']);
        } elseif (isset($_POST['remove_banner']) && $_POST['remove_banner'] == '1') {
            $bannerimg = null; // Remove banner if requested
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
            /* Update Tournament */
            if ($bannerimg === null) {
                $stmt = $conn->prepare("
                    UPDATE tournaments 
                    SET selected_game = ?, tname = ?, sdate = ?, stime = ?, 
                        bannerimg = NULL, about = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->bind_param("sssssii", 
                    $selected_game, 
                    $tname, 
                    $sdate, 
                    $stime, 
                    $about,
                    $tournament_id,
                    $user_id
                );
            } else {
                $stmt = $conn->prepare("
                    UPDATE tournaments 
                    SET selected_game = ?, tname = ?, sdate = ?, stime = ?, 
                        bannerimg = ?, about = ?
                    WHERE id = ? AND user_id = ?
                ");
                $stmt->bind_param("ssssssii", 
                    $selected_game, 
                    $tname, 
                    $sdate, 
                    $stime, 
                    $bannerimg, 
                    $about,
                    $tournament_id,
                    $user_id
                );
            }
            
            // Check if statement was prepared successfully
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $stmt->close();

            /* Update Brackets */
            // Convert empty strings to NULL for integer fields
            $solo_players = ($solo_players === '') ? null : (int)$solo_players;
            $duo_teams = ($duo_teams === '') ? null : (int)$duo_teams;
            $duo_players = ($duo_players === '') ? null : (int)$duo_players;
            $squad_teams = ($squad_teams === '') ? null : (int)$squad_teams;
            $squad_players = ($squad_players === '') ? null : (int)$squad_players;
            $rounds = ($rounds === '') ? null : (int)$rounds;
            
            if ($brackets_data) {
                // Update existing brackets
                $stmt2 = $conn->prepare("
                    UPDATE brackets
                    SET bracket_type = ?, match_type = ?, solo_players = ?, duo_teams = ?,
                        duo_players_per_team = ?, squad_teams = ?, squad_players_per_team = ?, 
                        rounds = ?, placement = ?, rules = ?, prizes = ?
                    WHERE tournament_id = ?
                ");
                
                if (!$stmt2) {
                    throw new Exception("Prepare failed for brackets: " . $conn->error);
                }
                
                $stmt2->bind_param(
                    "ssiiiiiiissi",
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
                    $prizes,
                    $tournament_id
                );
            } else {
                // Insert new brackets if they don't exist
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
            }
            
            if (!$stmt2->execute()) {
                throw new Exception("Execute failed for brackets: " . $stmt2->error);
            }
            
            $stmt2->close();

            /* Update Streams (optional) */
            if ($provider && $channel_name) {
                if ($streams_data) {
                    // Update existing stream
                    $stmt3 = $conn->prepare("
                        UPDATE streams
                        SET provider = ?, channel_name = ?, social_media = ?, social_media_input = ?
                        WHERE tournament_id = ?
                    ");
                    
                    if ($stmt3) {
                        $stmt3->bind_param(
                            "ssssi",
                            $provider,
                            $channel_name,
                            $social_media,
                            $social_media_input,
                            $tournament_id
                        );
                    }
                } else {
                    // Insert new stream
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
                    }
                }
                
                if ($stmt3 && !$stmt3->execute()) {
                    error_log("Stream update failed: " . $stmt3->error);
                }
                
                if ($stmt3) {
                    $stmt3->close();
                }
            } elseif ($streams_data) {
                // Remove stream if provider/channel name is empty
                $stmt3 = $conn->prepare("DELETE FROM streams WHERE tournament_id = ?");
                $stmt3->bind_param("i", $tournament_id);
                $stmt3->execute();
                $stmt3->close();
            }

            $conn->commit();
            
            // REDIRECT TO TOURNAMENT DETAILS PAGE AFTER SUCCESSFUL UPDATE
            header("Location: tournament_details.php?tournament_id=" . $tournament_id);
            exit();

        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Failed to update tournament: " . $e->getMessage();
            error_log("Tournament update error: " . $e->getMessage());
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
              <center>Edit Tournament</center>
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
                        <h2 id="heading">Edit Tournament</h2>
                        <p>Make changes to your tournament</p>
                        <form id="msform" action="edit_tour.php?tournament_id=<?php echo $tournament_id; ?>" method="post" enctype="multipart/form-data">
                            <!-- progressbar -->
                            <ul id="progressbar">
                                <li class="active" id="setup"><strong>Setup</strong></li>
                                <li id="brackets"><strong>Brackets</strong></li>
                                <li id="stream"><strong>Stream</strong></li>
                                <li id="publish"><strong>Review</strong></li>
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
                                      <input type="text" id="selected_game" name="selected_game" value="<?php echo htmlspecialchars($tournament_data['selected_game'] ?? ''); ?>" readonly />
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Tournament Name</label> 
                                      <input type="text" name="tname" id="tname" placeholder="Tournament Name" value="<?php echo htmlspecialchars($tournament_data['tname'] ?? ''); ?>" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Date</label> 
                                      <input type="date" name="sdate" id="sdate" placeholder="Start Date(DD/MM/YYYY)" value="<?php echo htmlspecialchars($tournament_data['sdate'] ?? ''); ?>" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Start Time</label> 
                                      <input type="time" name="stime" id="stime" placeholder="Time displayed in Time displayed in +0545" value="<?php echo htmlspecialchars($tournament_data['stime'] ?? ''); ?>" required /> 
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Game Banner</label>
                                      <?php if ($tournament_data['bannerimg']): ?>
                                        <div class="current-banner">
                                            <p>Current Banner:</p>
                                            <img src="data:image/jpeg;base64,<?php echo base64_encode($tournament_data['bannerimg']); ?>" style="max-width: 200px; max-height: 150px; margin-bottom: 10px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="remove_banner" name="remove_banner" value="1">
                                                <label class="form-check-label" for="remove_banner">Remove current banner</label>
                                            </div>
                                            <p>Or upload new banner:</p>
                                        </div>
                                      <?php endif; ?>
                                      <input type="file" id="bannerimg" name="bannerimg" accept="image/*" onchange="showPreview(event);" />
                                      <div class="preview">
                                        <img id="bannerimg-preview">
                                      </div>
                                      <label class="fieldlabels" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">About</label>
                                      <div id="editor-container-about" style="height: 200px; display: block !important; height: 200px !important;"></div>
                                      <input type="hidden" name="about" id="about" value="<?php echo htmlspecialchars($tournament_data['about'] ?? ''); ?>">                           
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
                                          <option value="battle_royal" <?php echo ($brackets_data['bracket_type'] ?? '') == 'battle_royal' ? 'selected' : ''; ?>>Battle Royal</option>
                                          <option value="round_robin" <?php echo ($brackets_data['bracket_type'] ?? '') == 'round_robin' ? 'selected' : ''; ?>>Round Robin</option>
                                          <option value="double_elimination" disabled>Double Elimination</option>
                                          <option value="single_elimination" disabled>Single Elimination</option>
                                        </select>
                                      </div>
                                    
                                      <div class="match-selection">
                                        <label class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;" for="match-type">Match Type</label>
                                        <select id="match-type" name="match-type" class="brac-input">
                                          <option value="solo" <?php echo ($brackets_data['match_type'] ?? '') == 'solo' ? 'selected' : ''; ?>>Solo</option>
                                          <option value="duo" <?php echo ($brackets_data['match_type'] ?? '') == 'duo' ? 'selected' : ''; ?>>Duo</option>
                                          <option value="squad" <?php echo ($brackets_data['match_type'] ?? '') == 'squad' ? 'selected' : ''; ?>>Squad</option>
                                        </select>
                                      </div>
                                    
                                      <!-- Solo Container -->
                                      <div id="solo-container" class="match-container" style="display: none;">
                                        <label for="solo-players" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Players</label>
                                        <input type="number" id="solo-players" name="solo-players" class="brac-input" value="<?php echo htmlspecialchars($brackets_data['solo_players'] ?? ''); ?>">
                                      </div>
                                    
                                      <!-- Duo Container -->
                                      <div id="duo-container" class="match-container" style="display: none;">
                                        <label for="duo-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                        <input type="number" id="duo-teams" name="duo-teams" class="brac-input" value="<?php echo htmlspecialchars($brackets_data['duo_teams'] ?? ''); ?>">
                                    
                                        <label for="duo-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                        <input type="number" id="duo-players" name="duo-players" class="brac-input" value="<?php echo htmlspecialchars($brackets_data['duo_players_per_team'] ?? ''); ?>"></div>
                                    
                                      <!-- Squad Container -->
                                      <div id="squad-container" class="match-container" style="display: none;">
                                        <label for="squad-teams" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Teams</label>
                                        <select id="squad-teams" name="squad-teams" class="brac-input">
                                          <?php for ($i = 1; $i <= 20; $i++): ?>
                                          <option value="<?php echo $i; ?>" <?php echo ($brackets_data['squad_teams'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                          <?php endfor; ?>
                                        </select>
                                    
                                        <label for="squad-players-per-team" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Players per Team</label>
                                        <input type="number" id="squad-players" name="squad-players" class="brac-input" value="<?php echo htmlspecialchars($brackets_data['squad_players_per_team'] ?? ''); ?>">
                                      </div>

                                      <label for="rounds" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Number of Rounds</label>
                                        <select id="rounds" name="rounds" class="brac-input">
                                          <?php for ($i = 1; $i <= 6; $i++): ?>
                                          <option value="<?php echo $i; ?>" <?php echo ($brackets_data['rounds'] ?? '') == $i ? 'selected' : ''; ?>><?php echo $i; ?></option>
                                          <?php endfor; ?>
                                        </select>
                                    
                                        <h3 class="fs-titleh3">Placement Point System</h3>
                                        <label for="placement" class="brac-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">Placement</label>
                                        <textarea id="placement" name="placement" class="brac-input" rows="7" placeholder="#1 = 10pts\n#2 = 8pts\n#3 = 6pts\n#Kill = 1pt"><?php echo htmlspecialchars($brackets_data['placement'] ?? ''); ?></textarea>
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
                                                    <option value="twitch" <?php echo ($streams_data['provider'] ?? '') == 'twitch' ? 'selected' : ''; ?>>Twitch</option>
                                                    <option value="youtube" <?php echo ($streams_data['provider'] ?? '') == 'youtube' ? 'selected' : ''; ?>>YouTube</option>
                                                    <option value="facebook" <?php echo ($streams_data['provider'] ?? '') == 'facebook' ? 'selected' : ''; ?>>Facebook</option>
                                                  </select>
                                                <input type="text" id="channel-name" name="channel-name" class="dynamic-input" placeholder="Enter your Channel Name" value="<?php echo htmlspecialchars($streams_data['channel_name'] ?? ''); ?>" />
                                            </div>

                                            <label for="social-media" class="unique-label" style="color: #000000 !important; text-align: left !important; font-size: 22px !important; margin-bottom: 15px; display: block;">How players will contact you ?</label>
                                            <div class="social-media-row">
                                                <select id="social-media" name="social-media" class="unique-select">
                                                    <option value="">Select a social media</option>
                                                    <option value="facebook" <?php echo ($streams_data['social_media'] ?? '') == 'facebook' ? 'selected' : ''; ?>>Facebook</option>
                                                    <option value="twitter" <?php echo ($streams_data['social_media'] ?? '') == 'twitter' ? 'selected' : ''; ?>>Twitter</option>
                                                    <option value="discord" <?php echo ($streams_data['social_media'] ?? '') == 'discord' ? 'selected' : ''; ?>>Discord</option>
                                                    <option value="instagram" <?php echo ($streams_data['social_media'] ?? '') == 'instagram' ? 'selected' : ''; ?>>Instagram</option>
                                                    <option value="linkedin" <?php echo ($streams_data['social_media'] ?? '') == 'linkedin' ? 'selected' : ''; ?>>LinkedIn</option>
                                                </select>
                                                <input id="social-media-input" name="social-media-input" class="dynamic-input" type="text" placeholder="Enter your username" value="<?php echo htmlspecialchars($streams_data['social_media_input'] ?? ''); ?>">
                                            </div>
                                        </div>
                                        
                                       <dl class="accordion">
                                           <dt>Critical Rules</dt>
                                           <dd>
                                           <div id="editor-container-rules" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="rules" id="rules" value="<?php echo htmlspecialchars($brackets_data['rules'] ?? ''); ?>">
                                           </dd>
                                           <dt>Prizes</dt>
                                           <dd>
                                           <div id="editor-container-prizes" style="height: 200px;display: block !important; height: 200px !important;"></div>
                                            <input type="hidden" name="prizes" id="prizes" value="<?php echo htmlspecialchars($brackets_data['prizes'] ?? ''); ?>">
                                           </dd>
                                       </dl>
                                   </main>

                                    
                                </div> 
                                  <input type="submit" name="next" class="next action-button" id="update_tour" value="Update Tournament" /> 
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
                                  <h2 class="purple-text text-center"><strong>Tournament Updated</strong></h2> 
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-3">
                                          <?php if ($tournament_data['bannerimg']): ?>
                                          <img id="final-banner-img" src="data:image/jpeg;base64,<?php echo base64_encode($tournament_data['bannerimg']); ?>" class="fit-image" style="width: 100%; max-width: 400px; height: auto; object-fit: cover;">
                                          <?php endif; ?>
                                      </div>
                                  </div> 
                                  <br><br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                          <h5 class="purple-text text-center" id="final-tournament-name"><?php echo htmlspecialchars($tournament_data['tname'] ?? ''); ?></h5>
                                          <?php if (isset($tournament_data['sdate'])): ?>
                                          <p id="final-tournament-start-date">Starts: <?php echo date('F j, Y', strtotime($tournament_data['sdate'])); ?></p>
                                          <?php endif; ?>
                                      </div>
                                  </div>
                                  <br>
                                  <div class="row justify-content-center">
                                      <div class="col-7 text-center">
                                          <!-- UPDATED LINKS -->
                                          <a href="tournament_details.php?id=<?php echo $tournament_id; ?>" class="button-custom">View Tournament</a>
                                          <a href="mytournaments.php" class="button-custom">My Tournaments</a>
                                          <a href="edit_tour.php?tournament_id=<?php echo $tournament_id; ?>" class="button-custom">Edit Again</a>
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
<!-- Temporarily disable external JS for debugging -->
<script src="./js/tour_org.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    console.log('Edit tournament page loaded - Tournament ID: <?php echo $tournament_id; ?>');
    
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

    // Set existing content for Quill editors
    <?php if (isset($tournament_data['about']) && !empty($tournament_data['about'])): ?>
    quillAbout.root.innerHTML = `<?php echo addslashes($tournament_data['about']); ?>`;
    <?php else: ?>
    quillAbout.setText('About this tournament...');
    <?php endif; ?>

    <?php if (isset($brackets_data['rules']) && !empty($brackets_data['rules'])): ?>
    quillRules.root.innerHTML = `<?php echo addslashes($brackets_data['rules']); ?>`;
    <?php else: ?>
    quillRules.setText('1. Be respectful to all players\n2. No cheating or hacking\n3. Follow the game rules');
    <?php endif; ?>

    <?php if (isset($brackets_data['prizes']) && !empty($brackets_data['prizes'])): ?>
    quillPrizes.root.innerHTML = `<?php echo addslashes($brackets_data['prizes']); ?>`;
    <?php else: ?>
    quillPrizes.setText('1st Place: $100\n2nd Place: $50\n3rd Place: $25');
    <?php endif; ?>

    // SIMPLIFIED FORM SUBMISSION
    const updateButton = document.getElementById('update_tour');
    if (updateButton) {
        updateButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Update button clicked');
            
            // Get content from Quill editors
            document.getElementById('about').value = quillAbout.root.innerHTML;
            document.getElementById('rules').value = quillRules.root.innerHTML;
            document.getElementById('prizes').value = quillPrizes.root.innerHTML;
            
            // Validate required fields
            const requiredFields = [
                {field: document.getElementById('selected_game'), name: 'Game'},
                {field: document.getElementById('tname'), name: 'Tournament Name'},
                {field: document.getElementById('sdate'), name: 'Start Date'},
                {field: document.getElementById('stime'), name: 'Start Time'}
            ];
            
            let isValid = true;
            let errorMessage = '';
            
            for (let item of requiredFields) {
                if (!item.field.value.trim()) {
                    isValid = false;
                    errorMessage = `${item.name} is required`;
                    item.field.style.borderColor = 'red';
                    break;
                } else {
                    item.field.style.borderColor = '';
                }
            }
            
            if (!isValid) {
                alert(errorMessage);
                return false;
            }
            
            // Show loading state
            this.value = 'Updating...';
            this.disabled = true;
            
            // Submit the form
            console.log('Submitting form...');
            document.getElementById('msform').submit();
        });
    }

    // Handle back button
    const backButton = document.getElementById('back-arrow');
    if (backButton) {
        backButton.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Back button clicked, going to mytournaments.php');
            window.location.href = 'mytournaments.php';
        });
    }

    // Handle next/previous buttons for multi-step form
    const nextButtons = document.querySelectorAll('.next.action-button:not(#update_tour)');
    nextButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Next button clicked');
            
            // Simple multi-step navigation
            const currentFieldset = this.closest('fieldset');
            const nextFieldset = currentFieldset.nextElementSibling;
            
            if (currentFieldset && nextFieldset && nextFieldset.tagName === 'FIELDSET') {
                currentFieldset.style.display = 'none';
                nextFieldset.style.display = 'block';
                
                // Update progress bar
                const progressbar = document.querySelectorAll('#progressbar li');
                if (progressbar.length >= 4) {
                    progressbar.forEach(li => li.classList.remove('active'));
                    
                    if (nextFieldset.querySelector('.fs-title').textContent.includes('Bracket')) {
                        progressbar[1].classList.add('active');
                    } else if (nextFieldset.querySelector('.fs-title').textContent.includes('Stream')) {
                        progressbar[2].classList.add('active');
                    } else if (nextFieldset.querySelector('.fs-title').textContent.includes('Finish')) {
                        progressbar[3].classList.add('active');
                    }
                }
            }
        });
    });

    const prevButtons = document.querySelectorAll('.previous.action-button-previous');
    prevButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Previous button clicked');
            
            const currentFieldset = this.closest('fieldset');
            const prevFieldset = currentFieldset.previousElementSibling;
            
            if (currentFieldset && prevFieldset && prevFieldset.tagName === 'FIELDSET') {
                currentFieldset.style.display = 'none';
                prevFieldset.style.display = 'block';
                
                // Update progress bar
                const progressbar = document.querySelectorAll('#progressbar li');
                if (progressbar.length >= 4) {
                    progressbar.forEach(li => li.classList.remove('active'));
                    
                    if (prevFieldset.querySelector('.fs-title').textContent.includes('Setup')) {
                        progressbar[0].classList.add('active');
                    } else if (prevFieldset.querySelector('.fs-title').textContent.includes('Bracket')) {
                        progressbar[1].classList.add('active');
                    } else if (prevFieldset.querySelector('.fs-title').textContent.includes('Stream')) {
                        progressbar[2].classList.add('active');
                    }
                }
            }
        });
    });

    // Initialize all fieldsets to show only the first one
    const fieldsets = document.querySelectorAll('#msform fieldset');
    if (fieldsets.length > 0) {
        fieldsets.forEach((fieldset, index) => {
            if (index === 0) {
                fieldset.style.display = 'block';
            } else {
                fieldset.style.display = 'none';
            }
        });
    }

    // Function to show image preview
    function showPreview(event) {
        var reader = new FileReader();
        reader.onload = function() {
            var previewImage = document.getElementById('bannerimg-preview');
            if (previewImage) {
                previewImage.src = reader.result;
                
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
        function updateMatchTypeDisplay() {
            document.querySelectorAll('.match-container').forEach(container => {
                container.style.display = 'none';
            });
            
            const selectedValue = matchTypeSelect.value;
            if (selectedValue === 'solo') {
                document.getElementById('solo-container').style.display = 'block';
            } else if (selectedValue === 'duo') {
                document.getElementById('duo-container').style.display = 'block';
            } else if (selectedValue === 'squad') {
                document.getElementById('squad-container').style.display = 'block';
            }
        }
        
        matchTypeSelect.addEventListener('change', updateMatchTypeDisplay);
        updateMatchTypeDisplay();
    }

    // Show success message if redirected with success parameter
    <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
        alert('Tournament updated successfully!');
    <?php endif; ?>

    // Show error message if any
    <?php if (!empty($error_message)): ?>
        alert('<?php echo addslashes($error_message); ?>');
    <?php endif; ?>

    console.log('JavaScript initialization complete');
});
</script>
<?php include('footer.php'); ?>
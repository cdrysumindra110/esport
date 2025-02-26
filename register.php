<?php
// Include the config file for database connection
require_once 'config.php';
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

// Check if the user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

// Initialize variables for tournament and match type (for form display purposes)
$tournament_id = isset($_GET['tournament_id']) ? $_GET['tournament_id'] : null;
$match_type = isset($_GET['match_type']) ? $_GET['match_type'] : null;

// Check if tournament ID or match type is missing in the URL
if (!$tournament_id || !$match_type) {
    die("Error: Missing tournament ID or match type.");
}

// Fetch tournament details from the database
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, u.uname AS creator_name 
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN users u ON t.user_id = u.id  
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tournament = $result->fetch_assoc();

        // Assign variables from the $tournament array
        $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
        $tname = $tournament['tname'] ?? 'Unknown Game';
        $sdate = $tournament['sdate'] ?? '';
        $stime = $tournament['stime'] ?? '';
        $about = $tournament['about'] ?? '';
        $bannerimg = $tournament['bannerimg'] ?? '';
        $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';

        // Format the date to show month and day in words (keep year as a number)
        if ($sdate) {
            $date = new DateTime($sdate);
            $sdate = $date->format('F j, Y');  // Month name, day, year
        }
    } else {
        $error_message = "No tournament found with that ID.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament detail statement: " . $conn->error;
}


// Check if slots are full for a specific match type
function checkSlotsFull($tournament_id, $match_type) {
    global $conn;
    $current_count = 0;
    $max_slots = 0;
    
    // Retrieve the maximum number of slots for the given match type
    $sql = "SELECT solo_players, duo_teams, squad_teams FROM brackets WHERE tournament_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $stmt->bind_result($solo_players, $duo_teams, $squad_teams);
    $stmt->fetch();
    $stmt->close();

    // Set max slots based on match type
    if ($match_type == 'solo') {
        $max_slots = $solo_players;
    } elseif ($match_type == 'duo') {
        $max_slots = $duo_teams;
    } elseif ($match_type == 'squad') {
        $max_slots = $squad_teams;
    }

    // Check current registrations based on match type
    if ($match_type == 'solo') {
        $sql = "SELECT COUNT(*) AS count FROM solo_registration WHERE tournament_id = ?";
    } elseif ($match_type == 'duo') {
        $sql = "SELECT COUNT(*) AS count FROM duo_registration WHERE tournament_id = ?";
    } elseif ($match_type == 'squad') {
        $sql = "SELECT COUNT(*) AS count FROM squad_registration WHERE tournament_id = ?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $tournament_id);
    $stmt->execute();
    $stmt->bind_result($current_count);
    $stmt->fetch();
    $stmt->close();

    return $current_count >= $max_slots;
}

// Function to handle file upload
function uploadFile($input_name) {
    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
        $target_dir = "uploads/";
        $file_path = $target_dir . basename($_FILES[$input_name]["name"]);
        if (move_uploaded_file($_FILES[$input_name]["tmp_name"], $file_path)) {
            return $file_path;
        }
    }
    return null;
}

// Handle form data when the form is submitted via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $tournament_id = $_POST['tournament_id'];
    $match_type = $_POST['match_type'];

    // Check if the slots are full for the match type
    if (checkSlotsFull($tournament_id, $match_type)) {
        $error_message = "Registration is closed as the tournament is full.";
    } else {
        try {
            if ($match_type == "solo") {
                $player_name = $_POST['solo_name'];
                $email = $_POST['solo_email'];
                
                // Check if the player already registered
                $sql = "SELECT * FROM solo_registration WHERE tournament_id = ? AND email = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("is", $tournament_id, $email);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result->num_rows > 0) {
                    $error_message = "You have already registered for this tournament.";
                } else {
                    $ign = $_POST['solo_ign'];
                    $logo_path = uploadFile('solo_logo');

                    $sql = "INSERT INTO solo_registration (tournament_id, player_name, email, ign, logo_path)
                            VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issss", $tournament_id, $player_name, $email, $ign, $logo_path);
                    $stmt->execute();
                    $stmt->close();
                    $success_message = "Solo registration successful.";
            }

        } elseif ($match_type == "duo") {
            $team_name = $_POST['duo_name'];
            $mentor_name = $_POST['duo_mentor'];
            $email = $_POST['duo_email'];

            // Check if the team already registered
            $sql = "SELECT * FROM duo_registration WHERE tournament_id = ? AND email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $tournament_id, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error_message = "You have already registered for this tournament.";
            } else {
                $logo_path = uploadFile('duo_logo');
                $sql = "INSERT INTO duo_registration (tournament_id, team_name, mentor_name, email, logo_path)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $tournament_id, $team_name, $mentor_name, $email, $logo_path);
                $stmt->execute();
                
                $duo_id = $stmt->insert_id;
                $stmt->close();

                // Insert players for duo match type
                $players = [
                    ['name' => $_POST['duop1_name'], 'email' => $_POST['duop1_email'], 'role' => $_POST['duop1_role'], 'ign' => $_POST['duop1_ign']],
                    ['name' => $_POST['duop2_name'], 'email' => $_POST['duop2_email'], 'role' => $_POST['duop2_role'], 'ign' => $_POST['duop2_ign']]
                ];

                foreach ($players as $player) {
                    $sql = "INSERT INTO duo_players (duo_id, name, email, role, ign) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issss", $duo_id, $player['name'], $player['email'], $player['role'], $player['ign']);
                    $stmt->execute();
                    $stmt->close();
                }
                $success_message = "Duo registration successful.";
            }

        } elseif ($match_type == "squad") {
            $team_name = $_POST['sqd_name'];
            $mentor_name = $_POST['sqd_mentor'];
            $email = $_POST['sqd_email'];

            // Check if the team already registered
            $sql = "SELECT * FROM squad_registration WHERE tournament_id = ? AND email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("is", $tournament_id, $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $error_message = "You have already registered for this tournament.";
            } else {
                $logo_path = uploadFile('sqd_logo');
                $sql = "INSERT INTO squad_registration (tournament_id, team_name, mentor_name, email, logo_path)
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("issss", $tournament_id, $team_name, $mentor_name, $email, $logo_path);
                $stmt->execute();

                $squad_id = $stmt->insert_id;
                $stmt->close();

                // Insert players for squad match type
                $players = [
                    ['name' => $_POST['sqdp1_name'], 'email' => $_POST['sqdp1_email'], 'role' => $_POST['sqdp1_role'], 'ign' => $_POST['sqdp1_ign']],
                    ['name' => $_POST['sqdp2_name'], 'email' => $_POST['sqdp2_email'], 'role' => $_POST['sqdp2_role'], 'ign' => $_POST['sqdp2_ign']],
                    ['name' => $_POST['sqdp3_name'], 'email' => $_POST['sqdp3_email'], 'role' => $_POST['sqdp3_role'], 'ign' => $_POST['sqdp3_ign']],
                    ['name' => $_POST['sqdp4_name'], 'email' => $_POST['sqdp4_email'], 'role' => $_POST['sqdp4_role'], 'ign' => $_POST['sqdp4_ign']],
                    ['name' => $_POST['sqdsb_name'], 'email' => $_POST['sqdsb_email'], 'role' => $_POST['sqdsb_role'], 'ign' => $_POST['sqdsb_ign']]
                ];

                foreach ($players as $player) {
                    $sql = "INSERT INTO squad_players (squad_id, name, email, role, ign) VALUES (?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issss", $squad_id, $player['name'], $player['email'], $player['role'], $player['ign']);
                    $stmt->execute();
                    $stmt->close();
                }
                $success_message = "Squad registration successful.";
            }
        }

        // Redirect to success page if no errors occurred
        if (!$error_message) {
            header("Location: success.php?tournament_id=" . $tournament_id);
            exit;
        }

    } catch (Exception $e) {
        $error_message = "Registration failed: " . $e->getMessage();
    }
}

// Function to handle file upload
function uploadFile($input_name) {
    if (isset($_FILES[$input_name]) && $_FILES[$input_name]['error'] == 0) {
        $target_dir = "uploads/";
        $file_path = $target_dir . basename($_FILES[$input_name]["name"]);
        if (move_uploaded_file($_FILES[$input_name]["tmp_name"], $file_path)) {
            return $file_path;
        }
    }
    return null;
}
}
// Close the database connection
$conn->close();
?>






<!-- HTML form goes here, including success and error messages -->

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

    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        .tournament-reg_container {
            background: linear-gradient(135deg, #3a1c71, #d76d77, #ffaf7b);
            color: #fff;
            text-align: center;
            padding: 50px 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            margin-bottom: 30px;
        }

        .tournament-reg_container .content {
            max-width: 800px;
            margin: 0 auto;
        }

        .tournament-reg_container h1 {
            font-size: 36px;
            margin: 15px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 2px;
            transition: color 0.3s ease;
        }

        .tournament-reg_container h2 {
            font-size: 24px;
            margin: 10px 0;
            font-weight: semi-bold;
            letter-spacing: 1.5px;
            color: #ffeb3b;
        }

        .tournament-reg_container h3 {
            font-size: 20px;
            margin: 8px 0;
            font-weight: normal;
        }

        .ctn_btn {
            display: inline-block;
            background-color: #ff4081;
            color: #fff;
            padding: 12px 25px;
            font-size: 18px;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
            margin-top: 20px;
        }

        .ctn_btn:hover {
            background-color: #f50057;
            transform: scale(1.05);
        }

        .countdown {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin-top: 20px;
            font-size: 24px;
            color: #fff;
            }

            .time-section {
                display: flex;
                flex-direction: column;
                align-items: center;
                background-color: rgba(255, 255, 255, 0.2);
            }

            .countdown div {
                padding: 5px 10px;
                border-radius: 5px;
                min-width: 60px;
                font-weight: bold;
                text-align: center;
            }

            .countdown span {
                margin-top: 5px;
                font-size: 16px;
            }
            .separator {
                font-size: 24px;
                font-weight: bold;
                align-self: center;
                color: #fff; /* Adjust color as needed */
            }


        @media (max-width: 768px) {
            .tournament-reg_container {
                padding: 30px 15px;
            }

            .tournament-reg_container h1 {
                font-size: 28px;
            }

            .tournament-reg_container h2 {
                font-size: 20px;
            }

            .tournament-reg_container h3 {
                font-size: 16px;
            }

            .ctn_btn {
                font-size: 16px;
                padding: 10px 20px;
            }

            .countdown div {
                padding: 15px;
                font-size: 20px;
            }
        }

        .container-registration {
            background-color: #fff;
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .container-registration:hover {
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .container-registration h2 {
            text-align: center;
            font-size: 28px;
            color: #333;
        }

        #registration_form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        #solo_reg,
        #duo_reg,
        #sqd_reg {
            background-color: #fafafa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .form-group-solo,
        .form-group-duo,
        .form-group-squad {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 12px;
        }

        #players {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .player-section {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        @media (max-width: 768px) {
            #players {
                grid-template-columns: 1fr;
            }
        }

        input[type="text"],
        input[type="email"],
        input[type="tel"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="tel"]:focus,
        input[type="file"]:focus {
            border-color: #6200ea;
            box-shadow: 0 0 5px rgba(98, 0, 234, 0.4);
        }

        button[type="submit"] {
            background-color: #6200ea;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #4b00b3;
        }

        .preview img {
            max-width: 100%;
            max-height: 150px;
            border-radius: 5px;
        }

        .player-section h3 {
            font-size: 20px;
            color: #333;
            margin-bottom: 8px;
        }

        .player-section label {
            font-size: 14px;
            color: #666;
        }

        .player-section input {
            margin-bottom: 8px;
        }

        #solo_reg .form-group-solo label,
        #duo_reg .form-group-duo label,
        #sqd_reg .form-group-squad label {
            font-weight: bold;
            color: #444;
        }

        .container-registration,
        #registration_form div,
        input,
        button {
            transition: all 0.3s ease;
        }

        #reg-btn {
            background-color: #6200ea;
            color: #fff;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            display: none;
            opacity: 0;
            pointer-events: none;
            visibility: hidden;
            margin-top: 20px;
        }

        #reg-btn:hover {
            background-color: #4b00b3;
        }

        #solo_reg,
        #duo_reg,
        #squad_reg {
            display: none;
        }

        .hidden {
            display: none !important;
        }

        form > div:last-child {
            margin-bottom: 10px;
        }

        button[type="submit"] {
            margin-top: 10px;
        }

        form > div {
            margin-bottom: 7px;
        }

    </style>
  </head>

  <body class="size-1280 primary-color-red">

    <!-- HEADER -->
    <header role="banner" class="position-absolute">
      <!-- Top Bar -->
      <div class="top-bar full-width hide-s hide-m">
        <div class="right">
            <a href="tel:080055544444444" class="text-white text-primary-hover">Phone : +977 9864666601 </a> 
            <span class="sep text-white">|</span> <a href="mailto:infiknightesports@gmail.com" class="text-white text-primary-hover"><i ></i>Email :infiknightesports@gmail.com</a>
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
                Register Tournament
              </h1>
            </div>
        </header>
    </main>

        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>
<!-- ++++++++++++++++++++++++++++++++++++++++++++++Form containrerer+++++++++++++++++++++++++++++++++++ -->
    
<div class="tournament-reg_container">
    <div class="content">
        <h1 style="color:aqua">Drop In. Gear Up. Take Over.</h1>
        <h2><?php echo htmlspecialchars($tname); ?></h2>
        <br>
        <h2>JOIN US FOR THIS EXCLUSIVE LIVE EVENT</h2>
        <h1 style="color:aliceblue"><?php echo htmlspecialchars($sdate); ?> at <?php echo htmlspecialchars($stime); ?></h1>
        <!-- <a href="#" class="ctn_btn" >CLAIM MY SPOT!</a> -->
        <button class="ctn_btn" onclick="joinGame(<?php echo $tournament_id; ?>)">Already Registered</button>
        <br><br>
        <h3 style="color:white">THE TOURNAMENT STARTS IN:</h3>
        <div class="countdown">
            <div class="time-section">
                <div id="days">0</div>
                <span>Days</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="hours">0</div>
                <span>Hours</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="minutes">0</div>
                <span>Minutes</span>
            </div>
            <div class="separator">:</div>
            <div class="time-section">
                <div id="seconds">0</div>
                <span>Seconds</span>
            </div>
        </div>
    </div>
</div>

    <div class="container">
        <h1>Register for <?php echo $tname; ?></h1>
        
        <?php if ($error_message): ?>
            <div class="error"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tournament_id" value="<?php echo $tournament_id; ?>">
            <input type="hidden" name="match_type" value="<?php echo $match_type; ?>">

            <h2><?php echo ucfirst($match_type); ?> Registration</h2>
            <p>Tournament: <?php echo $tname; ?> | Game: <?php echo $selected_game; ?></p>

            <!-- Solo Registration Form -->
            <?php if ($match_type == 'solo'): ?>
                <div>
                    <label for="solo_name">Player Name:</label>
                    <input type="text" name="solo_name" id="solo_name" required>

                    <label for="solo_email">Email:</label>
                    <input type="email" name="solo_email" id="solo_email" required>

                    <label for="solo_ign">In-Game Name (IGN):</label>
                    <input type="text" name="solo_ign" id="solo_ign" required>

                    <label for="solo_logo">Upload Logo:</label>
                    <input type="file" name="solo_logo" id="solo_logo">
                </div>
            <!-- Duo Registration Form -->
            <?php elseif ($match_type == 'duo'): ?>
                <div>
                    <label for="duo_name">Team Name:</label>
                    <input type="text" name="duo_name" id="duo_name" required>

                    <label for="duo_mentor">Mentor Name:</label>
                    <input type="text" name="duo_mentor" id="duo_mentor" required>

                    <label for="duo_email">Email:</label>
                    <input type="email" name="duo_email" id="duo_email" required>

                    <label for="duo_logo">Upload Logo:</label>
                    <input type="file" name="duo_logo" id="duo_logo">
                </div>

                <h3>Player 1</h3>
                <div>
                    <label for="duop1_name">Name:</label>
                    <input type="text" name="duop1_name" required>

                    <label for="duop1_email">Email:</label>
                    <input type="email" name="duop1_email" required>

                    <label for="duop1_role">Role:</label>
                    <input type="text" name="duop1_role" required>

                    <label for="duop1_ign">In-Game Name (IGN):</label>
                    <input type="text" name="duop1_ign" required>
                </div>

                <h3>Player 2</h3>
                <div>
                    <label for="duop2_name">Name:</label>
                    <input type="text" name="duop2_name" required>

                    <label for="duop2_email">Email:</label>
                    <input type="email" name="duop2_email" required>

                    <label for="duop2_role">Role:</label>
                    <input type="text" name="duop2_role" required>

                    <label for="duop2_ign">In-Game Name (IGN):</label>
                    <input type="text" name="duop2_ign" required>
                </div>
            <!-- Squad Registration Form -->
            <?php elseif ($match_type == 'squad'): ?>
                <div>
                    <label for="sqd_name">Team Name:</label>
                    <input type="text" name="sqd_name" id="sqd_name" required>

                    <label for="sqd_mentor">Mentor Name:</label>
                    <input type="text" name="sqd_mentor" id="sqd_mentor" required>

                    <label for="sqd_email">Email:</label>
                    <input type="email" name="sqd_email" id="sqd_email" required>

                    <label for="sqd_logo">Upload Logo:</label>
                    <input type="file" name="sqd_logo" id="sqd_logo">
                </div>

                <h3>Player 1</h3>
                <div>
                    <label for="sqdp1_name">Name:</label>
                    <input type="text" name="sqdp1_name" required>

                    <label for="sqdp1_email">Email:</label>
                    <input type="email" name="sqdp1_email" required>

                    <label for="sqdp1_role">Role:</label>
                    <input type="text" name="sqdp1_role" required>

                    <label for="sqdp1_ign">In-Game Name (IGN):</label>
                    <input type="text" name="sqdp1_ign" required>
                </div>

                <h3>Player 2</h3>
                <div>
                    <label for="sqdp2_name">Name:</label>
                    <input type="text" name="sqdp2_name" required>

                    <label for="sqdp2_email">Email:</label>
                    <input type="email" name="sqdp2_email" required>

                    <label for="sqdp2_role">Role:</label>
                    <input type="text" name="sqdp2_role" required>

                    <label for="sqdp2_ign">In-Game Name (IGN):</label>
                    <input type="text" name="sqdp2_ign" required>
                </div>

                <h3>Player 3</h3>
                <div>
                    <label for="sqdp3_name">Name:</label>
                    <input type="text" name="sqdp3_name" required>

                    <label for="sqdp3_email">Email:</label>
                    <input type="email" name="sqdp3_email" required>

                    <label for="sqdp3_role">Role:</label>
                    <input type="text" name="sqdp3_role" required>

                    <label for="sqdp3_ign">In-Game Name (IGN):</label>
                    <input type="text" name="sqdp3_ign" required>
                </div>

                <h3>Player 4</h3>
                <div>
                    <label for="sqdp4_name">Name:</label>
                    <input type="text" name="sqdp4_name" required>

                    <label for="sqdp4_email">Email:</label>
                    <input type="email" name="sqdp4_email" required>

                    <label for="sqdp4_role">Role:</label>
                    <input type="text" name="sqdp4_role" required>

                    <label for="sqdp4_ign">In-Game Name (IGN):</label>
                    <input type="text" name="sqdp4_ign" required>
                </div>

                <h3>Substitute Player</h3>
                <div>
                    <label for="sqdsb_name">Name:</label>
                    <input type="text" name="sqdsb_name" required>

                    <label for="sqdsb_email">Email:</label>
                    <input type="email" name="sqdsb_email" required>

                    <label for="sqdsb_role">Role:</label>
                    <input type="text" name="sqdsb_role" required>

                    <label for="sqdsb_ign">In-Game Name (IGN):</label>
                    <input type="text" name="sqdsb_ign" required>
                </div>
            <?php endif; ?>

            <div>
                <button type="submit">Register</button>
            </div>
        </form>
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

    <!-- Popup page Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function() {
        var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
        myModal.show();
    }, 1000); // 1-second delay before modal appears
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
        // Countdown function
        function updateCountdown() {
            var eventDate = new Date('<?php echo $sdate; ?> ' + '<?php echo $stime; ?>');
            var now = new Date().getTime();
            var timeLeft = eventDate - now;

            var days = Math.floor(timeLeft / (1000 * 60 * 60 * 24));
            var hours = Math.floor((timeLeft % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            var minutes = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            var seconds = Math.floor((timeLeft % (1000 * 60)) / 1000);

            document.getElementById('days').innerText = days;
            document.getElementById('hours').innerText = hours;
            document.getElementById('minutes').innerText = minutes;
            document.getElementById('seconds').innerText = seconds;
        }

        // Update countdown every second
        setInterval(updateCountdown, 1000);


        // Show image preview
        function showPreview(event) {
            var input = event.target;
            var preview = input.nextElementSibling.querySelector('img');
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
</script>


<script>
document.addEventListener("DOMContentLoaded", function() {
    // Initially hide all registration sections and the submit button
    const soloReg = document.getElementById("solo_reg");
    const duoReg = document.getElementById("duo_reg");
    const squadReg = document.getElementById("squad_reg");
    const regButton = document.getElementById("reg-btn");

    soloReg.style.display = "none";
    duoReg.style.display = "none";
    squadReg.style.display = "none";
    regButton.style.display = "none"; // Hide submit button initially

    // Get the match_type from PHP (this is dynamically set in PHP)
    const matchType = "<?php echo $match_type; ?>";

    // Show the correct form section based on match_type
    if (matchType === "solo") {
        soloReg.style.display = "block";
    } else if (matchType === "duo") {
        duoReg.style.display = "block";
    } else if (matchType === "squad") {
        squadReg.style.display = "block";
    }

    // Only show the submit button if a section is displayed
    if (soloReg.style.display === "block" || duoReg.style.display === "block" || squadReg.style.display === "block") {
        regButton.style.display = "inline-block"; // Show the submit button
        regButton.style.opacity = 1;  // Ensure the button is fully visible
        regButton.style.visibility = "visible";  // Make button visible
        regButton.style.pointerEvents = "auto";  // Enable clickability
    }
});
function joinGame(tournamentId) {
    // Redirect to success.php with tournament_id and match_type as query parameters
    window.location.href = 'success.php?tournament_id=' + tournamentId;
  }
</script>

<!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

<!-- Bootstrap JS -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });


  </script>
</body>
</html>

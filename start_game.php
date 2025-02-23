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

// Get the logged-in user's ID
$user_id = $_SESSION['user_id']; // Assuming 'user_id' is stored in session

// Check if tournament ID is passed via POST after form submission
$tournament_id = isset($_POST['tournament_id']) ? $_POST['tournament_id'] : null;

// If not set, check if it's passed via GET (for initial page load)
if (!$tournament_id) {
    $tournament_id = isset($_GET['tournament_id']) ? $_GET['tournament_id'] : null;
}

// Handle case when tournament_id is missing
if (!$tournament_id) {
    die("Error: Missing tournament ID.");
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Retrieve form data
  $game_id = isset($_POST['game_id']) ? intval($_POST['game_id']) : null;
  $password = isset($_POST['password']) ? trim($_POST['password']) : '';
  $expire_time = isset($_POST['expire_time']) ? trim($_POST['expire_time']) : '';

  // Validate form data
  if (empty($game_id)) {
      $error_message = "Game ID is required.";
  }
  if (empty($password)) {
      $error_message = "Password is required.";
  }
  if (!empty($expire_time) && strtotime($expire_time) === false) {
      $error_message = "Invalid expiration time format.";
  }

  // Check if game already exists for this tournament and user
  if (empty($error_message)) {
      $sql = "SELECT COUNT(*) FROM game WHERE tournament_id = ? AND user_id = ?";
      if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("ii", $tournament_id, $user_id);
          $stmt->execute();
          $stmt->bind_result($count);
          $stmt->fetch();
          $stmt->close();

          // If the game already exists for this tournament and user
          if ($count > 0) {
              $error_message = "You have already created a game for this tournament.";
          }
      } else {
          $error_message = "Error checking existing game data: " . $conn->error;
      }
  }

  // Insert into the database if no errors
  if (empty($error_message)) {
      $sql = "INSERT INTO game (tournament_id, user_id, game_id, password, expire_time) VALUES (?, ?, ?, ?, ?)";
      if ($stmt = $conn->prepare($sql)) {
          $stmt->bind_param("iiiss", $tournament_id, $user_id, $game_id, $password, $expire_time);
          if ($stmt->execute()) {
              $success_message = "Game created successfully!";
              // Redirect to success page
              header("Location: success.php?tournament_id=" . $tournament_id);
              exit();
          } else {
              $error_message = "Error inserting data: " . $stmt->error;
          }
          $stmt->close();
      } else {
          $error_message = "Error preparing the statement: " . $conn->error;
      }
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

        /* Overall Container Styling */
        .tournament-reg_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            width: 80%;
            max-width: 100%;
            margin: 0 auto;
            background-color: #f9f9f9;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Left Container (Countdown and Text) */
        .left-container {
            flex: 1;
            padding: 40px;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            color: white;
            font-family: 'Arial', sans-serif;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        /* Adding a decorative element in the left container */
        .left-container::before {
            content: '';
            position: absolute;
            top: -100px;
            left: 0;
            width: 100%;
            background: url('https://www.example.com/path-to-image.svg') no-repeat center center;
            background-size: cover;
            opacity: 0.1;
            pointer-events: none;
        }

        /* Heading styles */
        .left-container h1, 
        .left-container h2, 
        .left-container h3 {
            margin: 10px 0;
            font-family: 'Helvetica', sans-serif;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        /* Main Title */
        .left-container h1 {
            font-size: 2rem;
            color: #ecf0f1;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Subtitle */
        .left-container h2 {
            font-size: 1.5rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }


        /* Tournament Date and Time */
        .left-container h1 + h1 {
            font-size: 1.8rem;
            text-align: center;
            color: #ecf0f1;
            margin-top: 20px;
        }

        .ctn_btn:hover {
            background-color: #2ecc71;
            border-color: #2ecc71;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        /* Countdown Timer */
        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .time-section {
            padding: 10px 20px;
            margin: 0 10px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
        }

        .separator {
            font-size: 1.8rem;
            margin: 0 15px;
            color: #f39c12;
        }

        /* Time numbers */
        .countdown #days,
        .countdown #hours,
        .countdown #minutes,
        .countdown #seconds {
            font-size: 2rem;
            color: #ecf0f1;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.4);
        }


        /* Right Container (Form) */
        .right-container {
            flex: 1;
            padding: 20px;
            background: linear-gradient(135deg, #8e44ad, #3498db);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .right-container h2 {
            font-size: 3.12rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            color: #fff;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #27ae60;
            outline: none;
        }

        .start-btn, .cancel-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            width: 20%;
            margin-top: 20px;
        }

        .start-btn {
            background-color: #27ae60;
            color: white;
        }

        .start-btn:hover {
            background-color: #2ecc71;
        }

        .cancel-btn {
            background-color: #e74c3c;
            color: white;
        }

        .cancel-btn:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .tournament-reg_container {
                flex-direction: column;
                align-items: center;
            }

            .left-container, .right-container {
                width: 100%;
                margin-bottom: 20px;
            }
        }

    </style>
  </head>

  <body class="size-1280 primary-color-red">
    <div id="preloader" style="background: #1E1E2F url(./img/loader.gif) no-repeat center center; 
      background-size: 4.5%;height: 100vh;width: 100%;position: fixed;z-index: 999;">
    </div>
    <!-- HEADER -->
    <header role="banner" class="position-absolute">
      <!-- Top Bar -->
      <div class="top-bar full-width hide-s hide-m">
        <div class="right">
            <a href="tel:080055544444444" class="text-white text-primary-hover">Phone : +977 8888888888 </a> 
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
                Start Tournament
              </h1>
            </div>
        </header>
    </main>

        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>
<!-- ++++++++++++++++++++++++++++++++++++++++++++++Form containrerer+++++++++++++++++++++++++++++++++++ -->
<div class="tournament-reg_container">
    <div class="right-container">
        <h2>Set Up Tournament</h2>
        <form action="start_game.php" method="POST">
        <input type="hidden" name="tournament_id" value="<?php echo htmlspecialchars($tournament_id); ?>">

            <div class="form-group">
                <label for="game_id">Game ID</label>
                <input type="text" id="game_id" name="game_id" placeholder="Enter Game ID" required>
            </div>
            <div class="form-group">
                <label for="password">Game Password</label>
                <input type="text" id="password" name="password" placeholder="Enter Game Password" required>
            </div>
            <div class="form-group">
                <label for="expire_time">Tournament Expiration Time</label>
                <input type="datetime-local" id="expire_time" name="expire_time" required>
            </div>
            <div class="form-group">
                <button type="button" class="cancel-btn" onclick="window.history.back();">Cancel</button>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <button type="submit" value="submit" class="start-btn">Start Tournament</button>
            </div>
        </form>
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
               <a class="text-primary-hover" href="disclaimer.php">Disclaimer</a>
            </div>
            <div class="s-12 m-6 l-3 xl-3">
               <h4 class="text-white text-strong margin-m-top-30">Contact Us</h4>
                <a class="text-primary-hover" href="tel:+977 8888888888"><i class="icon-sli-screen-smartphone text-primary"></i> +977 8888888888</a><br>
                <a class="text-primary-hover" href="mailto:contact@InfiKnight.com"><i class="fa-solid fa-envelope text-primary"></i> contact@InfiKnight.com</a><br>
                <a class="text-primary-hover" href="https://maps.app.goo.gl/QGesNa3t51KtP1Vt7"><i class="fa-solid fa-map-marker-alt text-primary"></i> Pradarshani Marg, Kathmandu 44600</a>
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
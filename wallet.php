<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

// Get the messages from the URL query string
$success_message = isset($_GET['success_message']) ? $_GET['success_message'] : '';
$error_message = isset($_GET['error_message']) ? $_GET['error_message'] : '';

// Display success or error message
if ($success_message) {
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}

if ($error_message) {
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($error_message)."', 'error'); }</script>";
}

// Check if the user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

// Get the logged-in user ID
$user_id = $_SESSION['user_id'];
// Fetch current user data (cover photo and profile picture)
$sql = "SELECT cover_photo, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($cover_photo, $profile_pic);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
} else {
    die("Error: User not found.");
}
// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

// Handle the update email form
if (isset($_POST['update_email'])) {
    $newEmail = filter_var($_POST['newEmail'], FILTER_SANITIZE_EMAIL);

    // Validate the new email
    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
        // Prepare and execute the update
        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param('si', $newEmail, $user_id);

        if ($stmt->execute()) {
            $success_message = 'Email updated successfully.';
        } else {
            $error_message = 'Error updating email: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = 'Invalid email format.';
    }
}

// Prepare query string for redirect
$query_string = '';
if (!empty($success_message)) {
    $query_string .= 'success_message=' . urlencode($success_message);
}
if (!empty($error_message)) {
    if (!empty($query_string)) $query_string .= '&';
    $query_string .= 'error_message=' . urlencode($error_message);
}

// Redirect the user back to the change email page with messages
header('Location: change_email.php?' . $query_string);
exit;
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
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   


    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
<style>
/* Wallet Wrapper - One row, three columns */
.wallet-wrapper {
    display: flex;
    justify-content: space-between; /* Distributes wallets evenly */
    gap: 20px; /* Adds spacing between wallets */
    flex-wrap: nowrap; /* Ensures one row only */
    width: 100%; /* Ensures full width usage */
    padding: 10px;
    overflow: hidden; /* Prevents extra content from breaking layout */
}

/* Wallet Container */
.wallet-cnt {
    background-color:rgba(227, 227, 227, 0);
    /* border: 1px solid #ddd; */
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    flex: 1 1 calc(33.333% - 20px); /* Three wallets in one row */
    max-width: calc(33.333% - 20px); /* Prevents shrinking */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
/* Wallet Header */
.wallet-header {
    font-size: 1.2rem; /* Larger font size */
    font-weight: 600; /* Semi-bold */
    color: white; /* Darker text color */
    margin-bottom: 15px; /* Spacing below the header */
    text-transform: uppercase; /* Uppercase text */
    letter-spacing: 1px; /* Slight letter spacing */
}

/* Hover effect */
.wallet-cnt:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
}

/* Ensure one row with three columns at all breakpoints */
@media (max-width: 768px) {
    .wallet-wrapper {
        gap: 15px; /* Slightly reduce gap for smaller screens */
    }
    .wallet-cnt {
        flex: 1 1 calc(33.333% - 15px); /* Adjust for smaller gap */
        max-width: calc(33.333% - 15px);
    }
    .wallet-header {
        font-size: 1.1rem; /* Smaller font size for mobile */
    }
}

@media (max-width: 480px) {
    .wallet-wrapper {
        gap: 10px; /* Further reduce gap for very small screens */
    }
    .wallet-cnt {
        flex: 1 1 calc(33.333% - 10px); /* Adjust for smallest gap */
        max-width: calc(33.333% - 10px);
    }
    .wallet-header {
        font-size: 1.0rem; /* Smaller font size for mobile */
    }
}
/* Responsive Styles */
        /* Toggle Button */
        .toggle-btn {
            display: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .profile-container {
                display: none; /* Hide profile and cover on small devices */
            }

            .btn-container {
                display: none; /* Initially hide buttons on small devices */
                flex-direction: column;
                width: 100%;
            }

            .btn-container.active {
                display: flex; /* Show buttons when active */
            }

            .toggle-btn {
                display: block; /* Show toggle button on small devices */
            }

            .btn-cnt {
                width: 100%;
                justify-content: center;
            }

            .wallet-cnt {
                width: 100%;
                max-width: 150px;
            }
        }

        @media (max-width: 480px) {
            .wallet-cnt {
                width: 100%;
                max-width: 120px;
            }
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
                  <?php if ($isSignin): ?>
                      <li><a href="dashboard.php">Profile</a></li>
                      <li><a href="logout.php">Signout</a></li>
                  <?php else: ?>
                      <li><a href="signin.php"><i class='fa fa-sign-out'></i>Signin</a></li>
                      <li><a href="signup.php">Signup</a></li>
                  <?php endif; ?>
              </ul>
          </li>
          </li>
        </div>
      </nav>
    </header>
    
    <!-- Popup Message -->
    <div class="popup-message" id="popup-message"></div>

    <div class="profile-cont">
        <!-- Toggle Button -->
        <button class="toggle-btn" onclick="toggleButtons()">
            <i class="fa fa-bars"></i> Menu
        </button>

        <!-- Buttons Container -->
    <div class="btn-container">
            <button id="walletBtn" class="btn-cnt"><i class='fa fa-money'></i>Wallet</button>
            <button id="updateProfileBtn" class="btn-cnt"><i class='fas fa-user-edit'></i>Profile</button>
            <!-- <button id="teamProfileBtn" class="btn-cnt"><i class='fa fa-group'></i>Teams</button> -->
            <button id="myTournamentBtn" class="btn-cnt"><i class='fa fa-group'></i>My Tournaments</button>
            <button id="changeEmailBtn" class="btn-cnt"><i class='fa fa-envelope'></i>Change Email</button>
            <button id="changePasswordBtn" class="btn-cnt"><i class='fa fa-key'></i>Change Password</button>
            <button id="signoutBtn" class="btn-cnt"><i class='fa fa-sign-out'></i>Sign Out</button>
        </div>

        <div class="profile-container">
              <div class="cover-photo-container">
                  <div class="cover-photo">
                      <!-- <input type="file" name="cover_photo" id="cover_photo" accept="image/*" onchange="loadCoverPhoto(event)" class="file-input" />
                      <label for="cover_photo" class="cover-photo-label">change cover
                      </label> -->
                      <!-- Display user's cover photo or default cover photo -->
                      <img id="coverPhoto" 
                          name="coverPhoto" 
                          src="<?php echo isset($cover_photo) && !empty($cover_photo) 
                                      ? 'data:image/jpeg;base64,' . base64_encode($cover_photo) 
                                      : './img/dash-cover.png'; ?>" 
                          alt="Cover Photo" 
                          class="cover-photo-img" />
                      <div class="cover-overlay"></div>
                  </div>
              </div>
              <div class="profile-pic">
                  <!-- <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="loadProfilePic(event)" class="file-input" />
                  <label for="profile_pic" class="profile-pic-label">
                  </label> -->
                  <!-- Display user's profile picture or default profile picture -->
                  <img id="profilePic" 
                      name="profilePic" 
                      src="<?php echo isset($profile_pic) && !empty($profile_pic) 
                                  ? 'data:image/jpeg;base64,' . base64_encode($profile_pic) 
                                  : './img/dash-logo.png'; ?>" 
                      alt="Profile Picture" 
                      class="profile-pic-img" />
              </div>
          </div>

        <div class="unique-container">
          <div class="wallet-container">
            <h2 class="unique-header">Wallet</h2>
              <!-- <button class="link-wallet">
                <span class="link-icon">🔗</span>
                <span class="link-text">Link Wallet</span>
              </button> -->
          </div>
                      
          <div class="wallet-wrapper">
              <div class="wallet-cnt">
                  <h2 class="wallet-header">Esewa</h2>
                  <a href="https://esewa.com.np" target="_blank">
                      <img src="./img/wallet/esewa-logo.png" alt="Esewa" class="wallet-logo">
                  </a>
              </div>

              <div class="wallet-cnt">
                  <h2 class="wallet-header">Khalti</h2>
                  <a href="https://khalti.com" target="_blank">
                      <img src="./img/wallet/khalti-logo.png" alt="Khalti" class="wallet-logo">
                  </a>
              </div>

              <div class="wallet-cnt">
                  <h2 class="wallet-header">Fone Pay</h2>
                  <a href="https://fonepay.com" target="_blank">
                      <img src="./img/wallet/fonepay-logo.png" alt="Fone Pay" class="wallet-logo">
                  </a>
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
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="./js/template-scripts.js"></script> 
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
    <!-- Popup page Scripts -->
<script>
   document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

  document.addEventListener('DOMContentLoaded', () => {
    // Get all buttons in the button container
    const buttons = document.querySelectorAll('.btn-cnt');
  
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove 'active' class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
  
            // Add 'active' class to the clicked button
            this.classList.add('active');
  
            // Determine the URL to redirect based on button ID
            let redirectUrl = '';
            switch (this.id) {
                case 'walletBtn':
                    redirectUrl = 'wallet.php';
                    break;
                case 'updateProfileBtn':
                    redirectUrl = 'dashboard.php';
                    break;
                case 'teamProfileBtn':
                    redirectUrl = 'teams.php';
                    break;
                case 'myTournamentBtn':
                    redirectUrl = 'mytournaments.php';
                break;
                case 'changeEmailBtn':
                    redirectUrl = 'change_email.php';
                    break;
                case 'changePasswordBtn':
                    redirectUrl = 'change_password.php';
                    break;
                case 'signoutBtn':
                    redirectUrl = 'logout.php';
                    break;
                default:
                    redirectUrl = 'dashboard.php'; // Default fallback URL
            }
  
            // Redirect to the appropriate page
            window.location.href = redirectUrl;
        });
    });
  });

function loadCoverPhoto(event) {
    const coverPhoto = document.getElementById('coverPhoto');
    coverPhoto.src = URL.createObjectURL(event.target.files[0]);
}

function loadProfilePic(event) {
    const profilePic = document.getElementById('profilePic');
    profilePic.src = URL.createObjectURL(event.target.files[0]);
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
    popup.style.display = 'block';
    setTimeout(() => {
      popup.style.display = 'none';
    }, 3000); // Hide after 3 seconds
  }

  // Example usage for PHP error and success messages
  document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($success_message)): ?>
      showPopupMessage("<?php echo $success_message; ?>", 'success');
    <?php elseif (!empty($error_message)): ?>
      showPopupMessage("<?php echo $error_message; ?>", 'error');
    <?php endif; ?>
  });

    // Check if there's a success message and display it
    <?php if (!empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        showPopupMessage("<?php echo $success_message; ?>", 'success');
      });
    <?php endif; ?>
</script>
<script>
    // Toggle Button Functionality
    function toggleButtons() {
        const btnContainer = document.querySelector('.btn-container');
        btnContainer.classList.toggle('active');
    }
</script>

  </body>
</html>

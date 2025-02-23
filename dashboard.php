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
// Show success message for signup
if (isset($_GET['success_signin'])) {
  $success_message = htmlspecialchars($_GET['success_signin']);
  echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}

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

$user_id = $_SESSION['user_id'];

// Fetch current user data
$sql = "SELECT role, full_name, dob, country, city, cover_photo, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($role, $full_name, $dob, $country, $city, $cover_photo, $profile_pic);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
} else {
    die("Error: User not found.");
}
$stmt->close();

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    // Sanitize and validate inputs
    $role = trim($_POST['role']);
    $full_name = trim($_POST['full_name']);
    $dob_month = (int)$_POST['dob-month'];
    $dob_day = (int)$_POST['dob-day'];
    $dob_year = (int)$_POST['dob-year'];
    $country = trim($_POST['country']);
    $city = trim($_POST['city']);

    // Validate Date of Birth
    if (!checkdate($dob_month, $dob_day, $dob_year)) {
        $error_message = 'Invalid date provided.';
    } else {
        $dob = sprintf('%04d-%02d-%02d', $dob_year, $dob_month, $dob_day);

        // Process file uploads
        $cover_photo = null;
        $profile_pic = null;

        if (isset($_FILES['cover_photo']) && $_FILES['cover_photo']['error'] === UPLOAD_ERR_OK) {
            $cover_photo_tmp = $_FILES['cover_photo']['tmp_name'];
            $cover_photo = file_get_contents($cover_photo_tmp);
        }

        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $profile_pic_tmp = $_FILES['profile_pic']['tmp_name'];
            $profile_pic = file_get_contents($profile_pic_tmp);
        }

        // Prepare the SQL query
        $stmt = $conn->prepare("
            UPDATE users 
            SET role = ?, full_name = ?, dob = ?, country = ?, city = ?, cover_photo = ?, profile_pic = ?
            WHERE id = ?
        ");

        if (!$stmt) {
            die('Prepare failed: ' . $conn->error);
        }

        // Bind parameters
        $stmt->bind_param('sssssbbi', $role, $full_name, $dob, $country, $city, $null, $null, $user_id);

        // Send binary data
        if ($cover_photo) {
            $stmt->send_long_data(5, $cover_photo);
        }
        if ($profile_pic) {
            $stmt->send_long_data(6, $profile_pic);
        }

        // Execute the query
        if ($stmt->execute()) {
            $success_message = 'Profile updated successfully.';
        } else {
            $error_message = 'Error updating profile: ' . $stmt->error;
        }

        $stmt->close();
    }

    // Redirect with messages
    $query_string = '';
    if (!empty($success_message)) {
        $query_string .= 'success_message=' . urlencode($success_message);
    }
    if (!empty($error_message)) {
        $query_string .= '&error_message=' . urlencode($error_message);
    }
    header('Location: dashboard.php?' . $query_string);
    exit;
}

// Close the database connection
$conn->close();
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
    <link rel="stylesheet" href="./css/dashboard.css">
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

        <form id="update_profile" action="dashboard.php" method="post" enctype="multipart/form-data">
          <div class="profile-container">
              <div class="cover-photo-container">
                  <div class="cover-photo">
                      <input type="file" name="cover_photo" id="cover_photo" accept="image/*" onchange="loadCoverPhoto(event)" class="file-input" />
                      <label for="cover_photo" class="cover-photo-label">
                          <span class="icon-wrapper">
                              <i class="fas fa-camera"></i>
                          </span>
                          <span>Change Cover</span>
                      </label>
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
                  <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="loadProfilePic(event)" class="file-input" />
                  <label for="profile_pic" class="profile-pic-label">
                      <span class="icon-wrapper">
                          <i class="fas fa-camera"></i>
                      </span>
                      <span>Change Profile</span>
                  </label>
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

          <!-- Update Profile -->
          <div id="profileUpdateSection" class="profile-section">
              <div class="unique-container">
                  <h2 class="unique-header">Role Selection</h2>
                  <div class="unique-input-field">
                      <div class="slider-radio-group">
                        <!-- Hidden Radio Buttons -->
                        <input type="radio" id="role-player" name="role" value="player" class="unique-radio" 
                              <?php echo ($role === 'player') ? 'checked' : ''; ?> style="visibility: hidden;" required>
                        <input type="radio" id="role-organizer" name="role" value="organizer" class="unique-radio" 
                              <?php echo ($role === 'organizer') ? 'checked' : ''; ?> style="visibility: hidden;" required>
                    
                        <!-- Slider and Labels -->
                        <label class="f">
                                          Player
                      <input class="f__input" type="checkbox" id="role-toggle" name="role-toggle" 
                            <?php echo ($role === 'organizer') ? 'checked' : ''; ?>>
                          <span class="f__switch">
                            <span class="f__handle">
                              <span class="f__1"></span>
                              <span class="f__2">
                                <span class="f__2a"></span>
                                <span class="f__2b"></span>
                                <span class="f__2c"></span>
                                <span class="f__2d"></span>
                                <span class="f__2e"></span>
                              </span>
                              <span class="f__3"></span>
                              <span class="f__4"></span>
                              <span class="f__5"></span>
                              <span class="f__6"></span>
                              <span class="f__7"></span>
                              <span class="f__8"></span>
                              <span class="f__9"></span>
                              <span class="f__10"></span>
                              <span class="f__11"></span>
                              <span class="f__12"></span>
                              <span class="f__13"></span>
                              <span class="f__14"></span>
                              <span class="f__15"></span>
                              <span class="f__16"></span>
                              <span class="f__17"></span>
                            </span>
                          </span>
                          Organizer
                        </label>
                      </div>
                    </div>



                  <h2 class="unique-header">Personal Information</h2>
                  <div class="unique-input-field">
                      <label for="full_name" class="unique-label">Full Name</label>
                      <input type="text" id="full_name" name="full_name" class="unique-input" placeholder="Fname Lname" 
                            value="<?php echo htmlspecialchars($full_name); ?>" required>
                  </div>
                  <div class="unique-input-field">
                      <label for="dob" class="unique-label">Date Of Birth</label>
                      <div class="dob-row">
                          <select id="dob-year" name="dob-year" class="unique-select" required>
                              <!-- Generate year options -->
                              <?php for ($year = date('Y'); $year >= 1900; $year--): ?>
                                  <option value="<?php echo $year; ?>" <?php echo ($year == (int)date('Y', strtotime($dob))) ? 'selected' : ''; ?>>
                                      <?php echo $year; ?>
                                  </option>
                              <?php endfor; ?>
                          </select>
                          <select id="dob-month" name="dob-month" class="unique-select" required>
                              <!-- Generate month options -->
                              <?php for ($month = 1; $month <= 12; $month++): ?>
                                  <option value="<?php echo $month; ?>" <?php echo ($month == (int)date('m', strtotime($dob))) ? 'selected' : ''; ?>>
                                      <?php echo $month; ?>
                                  </option>
                              <?php endfor; ?>
                          </select>
                          <select id="dob-day" name="dob-day" class="unique-select" required>
                              <!-- Generate day options -->
                              <?php for ($day = 1; $day <= 31; $day++): ?>
                                  <option value="<?php echo $day; ?>" <?php echo ($day == (int)date('d', strtotime($dob))) ? 'selected' : ''; ?>>
                                      <?php echo $day; ?>
                                  </option>
                              <?php endfor; ?>
                          </select>
                      </div>
                  </div>

                  <h2 class="unique-header">Location</h2>
                  <div class="unique-input-field">
                      <label for="country" class="unique-label">Country</label>
                      <select id="country" name="country" class="unique-select" required>
                          <!-- Populate with existing countries -->
                          <option value="<?php echo htmlspecialchars($country); ?>" selected><?php echo htmlspecialchars($country); ?></option>
                      </select>
                  </div>
                  <div class="unique-info">
                      <i class="unique-info-icon"></i> You can change your country once every 6 months.
                  </div>
                  <div class="unique-input-field">
                      <label for="city" class="unique-label">City</label>
                      <input type="text" id="city" name="city" class="unique-input" placeholder="City" 
                            value="<?php echo htmlspecialchars($city); ?>" required>
                  </div>
                  <button type="button" class="unique-button" onclick="showSection('profileUpdateSection')">CANCEL</button>
                  <button type="submit" name="update_profile" value="submit" class="unique-button">SAVE CHANGES</button>
              </div>
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
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="js/template-scripts.js"></script> 

    <!-- Popup page Scripts -->
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

// ========================================== Dahboard Js =====================================================
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
  
// Function to handle cover photo preview
function loadCoverPhoto(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('coverPhoto');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}

function loadProfilePic(event) {
    var reader = new FileReader();
    reader.onload = function() {
        var output = document.getElementById('profilePic');
        output.src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
}


// ----------------------Dob Javascript ----------------------------
document.addEventListener('DOMContentLoaded', function() {
    const months = [
        { name: "January", value: 1 },
        { name: "February", value: 2 },
        { name: "March", value: 3 },
        { name: "April", value: 4 },
        { name: "May", value: 5 },
        { name: "June", value: 6 },
        { name: "July", value: 7 },
        { name: "August", value: 8 },
        { name: "September", value: 9 },
        { name: "October", value: 10 },
        { name: "November", value: 11 },
        { name: "December", value: 12 }
    ];
    const days = Array.from({ length: 31 }, (_, i) => i + 1);
    const currentYear = new Date().getFullYear();
    const years = Array.from({ length: 100 }, (_, i) => currentYear - i);

    const monthSelect = document.getElementById('dob-month');
    const daySelect = document.getElementById('dob-day');
    const yearSelect = document.getElementById('dob-year');

    // Populate months
    months.forEach(month => {
        const option = document.createElement('option');
        option.value = month.value; // Use numerical value
        option.textContent = month.name;
        monthSelect.appendChild(option);
    });

    // Populate days
    days.forEach(day => {
        const option = document.createElement('option');
        option.value = day;
        option.textContent = day;
        daySelect.appendChild(option);
    });

    // Populate years
    years.forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    });
});

// ----------------------------------JS for Countries ----------------------------
document.addEventListener('DOMContentLoaded', function() {
    const countries = [
        "Afghanistan", "Albania", "Algeria", "Andorra", "Angola", "Antigua and Barbuda",
        "Argentina", "Armenia", "Australia", "Austria", "Azerbaijan", "Bahamas", "Bahrain",
        "Bangladesh", "Barbados", "Belarus", "Belgium", "Belize", "Benin", "Bhutan", "Bolivia",
        "Bosnia and Herzegovina", "Botswana", "Brazil", "Brunei", "Bulgaria", "Burkina Faso",
        "Burundi", "Cabo Verde", "Cambodia", "Cameroon", "Canada", "Central African Republic",
        "Chad", "Chile", "China", "Colombia", "Comoros", "Congo, Democratic Republic of the",
        "Congo, Republic of the", "Costa Rica", "Croatia", "Cuba", "Cyprus", "Czechia", "Denmark",
        "Djibouti", "Dominica", "Dominican Republic", "Ecuador", "Egypt", "El Salvador",
        "Equatorial Guinea", "Eritrea", "Estonia", "Eswatini", "Ethiopia", "Fiji", "Finland",
        "France", "Gabon", "Gambia", "Georgia", "Germany", "Ghana", "Greece", "Grenada",
        "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Honduras", "Hungary",
        "Iceland", "India", "Indonesia", "Iran", "Iraq", "Ireland", "Israel", "Italy", "Jamaica",
        "Japan", "Jordan", "Kazakhstan", "Kenya", "Kiribati", "Korea, North", "Korea, South",
        "Kosovo", "Kuwait", "Kyrgyzstan", "Laos", "Latvia", "Lebanon", "Lesotho", "Liberia",
        "Libya", "Liechtenstein", "Lithuania", "Luxembourg", "Madagascar", "Malawi", "Malaysia",
        "Maldives", "Mali", "Malta", "Marshall Islands", "Mauritania", "Mauritius", "Mexico",
        "Micronesia", "Moldova", "Monaco", "Mongolia", "Montenegro", "Morocco", "Mozambique",
        "Myanmar", "Namibia", "Nauru", "Nepal", "Netherlands", "New Zealand", "Nicaragua",
        "Niger", "Nigeria", "North Macedonia", "Norway", "Oman", "Pakistan", "Palau", "Panama",
        "Papua New Guinea", "Paraguay", "Peru", "Philippines", "Poland", "Portugal", "Qatar",
        "Romania", "Russia", "Rwanda", "Saint Kitts and Nevis", "Saint Lucia",
        "Saint Vincent and the Grenadines", "Samoa", "San Marino", "Sao Tome and Principe",
        "Saudi Arabia", "Senegal", "Serbia", "Seychelles", "Sierra Leone", "Singapore",
        "Slovakia", "Slovenia", "Solomon Islands", "Somalia", "South Africa", "South Sudan",
        "Spain", "Sri Lanka", "Sudan", "Suriname", "Sweden", "Switzerland", "Syria", "Taiwan",
        "Tajikistan", "Tanzania", "Thailand", "Timor-Leste", "Togo", "Tonga", "Trinidad and Tobago",
        "Tunisia", "Turkey", "Turkmenistan", "Tuvalu", "Uganda", "Ukraine", "United Arab Emirates",
        "United Kingdom", "United States", "Uruguay", "Uzbekistan", "Vanuatu", "Vatican City",
        "Venezuela", "Vietnam", "Yemen", "Zambia", "Zimbabwe"
    ];

    const countrySelect = document.getElementById('country');

    // Populate countries
    countries.forEach(country => {
        const option = document.createElement('option');
        option.value = country;
        option.textContent = country;
        countrySelect.appendChild(option);
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


document.addEventListener('DOMContentLoaded', function() {
  // Get the checkbox (toggle) element
  const roleToggle = document.getElementById('role-toggle');

  // Get the hidden radio buttons for Player and Organizer
  const playerRadio = document.getElementById('role-player');
  const organizerRadio = document.getElementById('role-organizer');

  // Function to update the role based on the toggle state
  function updateRole() {
    if (roleToggle.checked) {
      // If checked, set 'organizer' radio button as selected
      organizerRadio.checked = true;
      playerRadio.checked = false;
    } else {
      // If unchecked, set 'player' radio button as selected
      playerRadio.checked = true;
      organizerRadio.checked = false;
    }

    // Send the role data to the server via AJAX (example using fetch)
    sendRoleToDatabase();
  }

  // Event listener for toggle state change
  roleToggle.addEventListener('change', updateRole);

  // Initial update on page load (in case the role is already set)
  updateRole();

  // Function to send the selected role to the server (example using fetch)
  function sendRoleToDatabase() {
    const role = playerRadio.checked ? 'player' : 'organizer';

    // Example: Use Fetch API to send role to the server (assuming a POST endpoint)
    fetch('/update-role', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({ role: role }), // Send role data as JSON
    })
    .then(response => response.json())
    .then(data => {
      console.log('Role updated successfully:', data);
    })
    .catch(error => {
      console.error('Error updating role:', error);
    });
  }
});
</script>

<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
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

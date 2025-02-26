<?php
require_once 'config.php';
session_start();

// Define $isSignin based on the session variable
$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

// Check if the user is signed in
if (!$isSignin) {
  $error_message = "Please Login to Access This Page!";
  header("Location: index.php?error_signin=" . urlencode($error_message));
  exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$tournaments = [];

// Fetch username
$stmt_uname = $conn->prepare("SELECT uname FROM users WHERE id = ?");
if ($stmt_uname) {
    $stmt_uname->bind_param("i", $user_id);
    $stmt_uname->execute();
    $stmt_uname->bind_result($uname);
    if ($stmt_uname->fetch()) {
        $_SESSION['uname'] = $uname;
    } else {
        // $error_message = "Error: Username not found for the user ID.";
    }
    $stmt_uname->close();
} else {
    $error_message = "Error preparing the statement: " . $conn->error;
}


$selected_game = isset($_GET['selected_game']) ? $_GET['selected_game'] : '';
$match_type = isset($_GET['match_type']) ? $_GET['match_type'] : '';
$sdate = isset($_GET['sdate']) ? $_GET['sdate'] : '';


$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS host_username 
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id
        WHERE 1=1";


if (!empty($selected_game)) {
    $sql .= " AND t.selected_game = ?";
}

if (!empty($match_type)) {
    $sql .= " AND b.match_type = ?";
}

if (!empty($sdate)) {
    $sql .= " AND t.sdate = ?";
}

$sql .= " ORDER BY t.id";

$stmt = $conn->prepare($sql);

$params = [];
$types = ''; 

if (!empty($selected_game)) {
    $params[] = $selected_game;
    $types .= 's'; 
}

if (!empty($match_type)) {
    $params[] = $match_type;
    $types .= 's'; 
}

if (!empty($sdate)) {
    $params[] = $sdate;
    $types .= 's'; 
}


if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
    } else {
        $error_message = "No tournaments found.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament statement: " . $conn->error;
}

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
    <link rel="stylesheet" href="./css/tour_org.css">
    <link rel="stylesheet" href="./css/tournaments.css?v=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    <style>
          /* General Form Styling */
          .filter-form {
              font-family: Arial, sans-serif;
              display: flex;
              gap: 20px;
              flex-wrap: wrap;
          }

          .filter-form div {
              display: flex;
              flex-direction: column;
              position: relative;
              margin-right: 10px;
          }

          .filter-form label {
              font-size: 14px;
              font-weight: bold;
              color: white;
              margin-bottom: 5px;
              margin-right: 10px;
          }

          /* Input and Select Styles */
          .filter-form select,
          .filter-form input[type="date"] {
              padding: 10px;
              font-size: 14px;
              border: 1px solid #ccc;
              border-radius: 5px;
              outline: none;
              transition: all 0.3s ease;
          }

          .filter-form select:focus,
          .filter-form input[type="date"]:focus {
              border-color: #007BFF;
              box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
          }

          /* Button Styling */
          .filter-form button {
              padding: 10px 20px;
              font-size: 14px;
              color: #fff;
              background-color: #007BFF;
              border: none;
              border-radius: 5px;
              cursor: pointer;
              transition: all 0.3s ease;
              align-self: center;
          }

          .filter-form button:hover {
              background-color: #0056b3;
              transform: scale(1.05);
              box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
          }

    /* Responsive Styles */
    @media screen and (max-width: 1024px) {
        .filter-form {
            flex-direction: column;
            align-items: center;
        }

        .filter-form div {
            width: 100%;
        }

        .ut-header__button {
            width: 100%;
            text-align: center;
        }

        .ut-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

    @media screen and (max-width: 768px) {
        .ut-container {
            padding: 10px;
        }

        .ut-table thead {
            display: none;
        }

        .ut-table tbody,
        .ut-table tr {
            display: block;
            width: 100%;
        }

        .ut-table tr {
            margin-bottom: 10px;
            border: 1px solid #ccc;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .ut-table__cell {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            font-size: 14px;
        }

        .ut-table__cell--first {
            flex-direction: column;
            text-align: center;
        }

        .ut-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .ut-info {
            margin-top: 5px;
        }

        .ut-info__name,
        .ut-info__host {
            font-size: 16px;
        }
    }

    @media screen and (max-width: 480px) {
        .ut-header {
            text-align: center;
        }

        .filter-form div {
            width: 100%;
            display: block;
        }

        .filter-form select,
        .filter-form input {
            width: 100%;
        }

        .ut-table tr {
            padding: 8px;
        }

        .ut-table__cell {
            font-size: 12px;
        }

        .filter-form button,
        .ut-header__button {
            font-size: 14px;
            padding: 8px 12px;
        }

        .ut-info__name,
        .ut-info__host {
            font-size: 14px;
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
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              Tournaments
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>



    <!-- Popup Message -->
    <div class="popup-message" id="popup-message"></div>

    <div id="myTournamentsSection" class="profile-section">
          <!-- Filtering Form -->
      <div class="ut-container">
        <h2 class="unique-header">Available Tournaments</h2>
            <div class="ut-header">
              <form method="GET" action="" class="filter-form">
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="selected_game">Game:</label>
                      <select name="selected_game" id="selected_game">
                          <option value="">All</option>
                          <option value="PUBG" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'PUBG' ? 'selected' : ''; ?>>PUBG</option>
                          <option value="Call of Duty: Mobile" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'Call of Duty: Mobile' ? 'selected' : ''; ?>>Call of Duty: Mobile</option>
                          <option value="Free Fire" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'Free Fire' ? 'selected' : ''; ?>>Free Fire</option>
                      </select>
                  </div>
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="match_type">Match Type:</label>
                      <select name="match_type" id="match_type">
                          <option value="">All</option>
                          <option value="solo" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'solo' ? 'selected' : ''; ?>>Solo</option>
                          <option value="duo" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'duo' ? 'selected' : ''; ?>>Duo</option>
                          <option value="squad" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'squad' ? 'selected' : ''; ?>>Squad</option>
                      </select>
                  </div>
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="sdate">Date:</label>
                      <input type="date" name="sdate" id="sdate" value="<?php echo isset($_GET['sdate']) ? htmlspecialchars($_GET['sdate']) : ''; ?>">
                  </div>
                  <div style="display: inline-block;">
                      <button type="submit">FILTER</button>
                  </div>
              </form>
              <a href="tournaments.php" class="ut-header__button">EXPLORE TOURNAMENTS</a>
            </div>
            <table class="ut-table">
                <thead>
                    <tr>
                        <th class="ut-table__head">
                            <i class='fa fa-trophy' style='color:#00d696'></i> TOURNAMENTS
                        </th>
                        <th class="ut-table__head ut-table__head--game">
                            <i class='fa fa-flag-checkered' style='color:#00d696'></i> GAME
                        </th>
                        <th class="ut-table__head ut-table__cell--brackets">
                            <i class='fa fa-calendar' style='color:#00d696'></i> Brackets
                        </th>
                        <th class="ut-table__head ut-table__cell--date">
                            <i class='fa fa-calendar' style='color:#00d696'></i> DATE
                        </th>
                        <th class="ut-table__head ut-table__cell--prize" style="padding-left: 20px;">
                            <i class='fas fa-medal' style='color:#00d696'></i> PRIZE
                        </th>
                        <th class="ut-table__head"></th>
                    </tr>
                </thead>           
                <tbody>
                    <?php if (!empty($tournaments)): ?>
                        <?php foreach ($tournaments as $tournament): ?>
                        <tr class="ut-row" onclick="window.location.href='tour_freg.php?tournament_id=<?php echo $tournament['id']; ?>'" style="cursor: pointer;">
                            <td class="ut-table__cell ut-table__cell--first">
                                <div class="ut-image">
                                    <?php if (!empty($tournament['bannerimg'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tournament['bannerimg']); ?>" alt="Tournament Banner">
                                    <?php else: ?>
                                        <img src="./img/dash-logo.png" alt="Default Tournament Banner">
                                    <?php endif; ?>
                                </div>
                                <div class="ut-info">
                                    <div class="ut-info__name"><?php echo htmlspecialchars($tournament['tname']); ?></div>
                                    <div class="ut-info__host">Hosted by 
                                        <span style="color: #00d696;">
                                            <?php echo htmlspecialchars($tournament['host_username']); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="ut-table__cell ut-table__cell--game">
                                <?php echo htmlspecialchars($tournament['selected_game']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--brackets">
                                <?php echo htmlspecialchars($tournament['bracket_type']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--date">
                                <?php echo htmlspecialchars($tournament['sdate']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--prize">
                                <?php echo htmlspecialchars($tournament['prizes']); ?>
                            </td>
                            <td class="ut-table__cell">
                                <a href="tour_freg.php?tournament_id=<?php echo $tournament['id']; ?>">
                                    <i class='fa fa-eye ut-row__icon-eye'></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No tournaments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
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
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="./js/template-scripts.js"></script> 
    <script src="./js/tour_org.js"></script>
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
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


<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

</body>
</html>
<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();


$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

if (!$isSignin) {
  $error_message = "Please Login to Access This Page!";
  header("Location: index.php?error_signin=" . urlencode($error_message));
  exit();
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
    
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

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
        <header class="section-head background-image" style="background-image:url(img/ful_bg.gif)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              Games
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>



  <!-- Popup Message -->
  <div class="popup-message" id="popup-message"></div>

  <div class="game-tournament">
      <h2 class="section-heading" style="float: left;padding-left : 100px">Select a Game</h2>
      <div id="search" style="float: right;">
        <svg viewBox="0 0 420 60" xmlns="http://www.w3.org/2000/svg">
          <rect class="bar"/>
          
          <g class="magnifier">
            <circle class="glass"/>
            <line class="handle" x1="32" y1="32" x2="44" y2="44"></line>
          </g>
      
          <g class="sparks">
            <circle class="spark"/>
            <circle class="spark"/>
            <circle class="spark"/>
          </g>
      
          <g class="burst pattern-one">
            <circle class="particle circle"/>
            <path class="particle triangle"/>
            <circle class="particle circle"/>
            <path class="particle plus"/>
            <rect class="particle rect"/>
            <path class="particle triangle"/>
          </g>
          <g class="burst pattern-two">
            <path class="particle plus"/>
            <circle class="particle circle"/>
            <path class="particle triangle"/>
            <rect class="particle rect"/>
            <circle class="particle circle"/>
            <path class="particle plus"/>
          </g>
          <g class="burst pattern-three">
            <circle class="particle circle"/>
            <rect class="particle rect"/>
            <path class="particle plus"/>
            <path class="particle triangle"/>
            <rect class="particle rect"/>
            <path class="particle plus"/>
          </g>
        </svg>
        <input type="search" name="q" id="searchInput" aria-label="Search for inspiration" placeholder="Search games..."/>
      </div>
      
      <div id="results">
        
      </div>
      
        <div class="games-container">
          <div class="game-card available" id="game-pubg">
              <img src="img/game/pubg.png" alt="PUBG">
              <h3>PUBG</h3>
          </div>
          <div class="game-card available" id="game-cod">
              <img src="img/game/csGo.png" alt="Call of Duty: Mobile">
              <h3>Call of Duty: Mobile</h3>
          </div>
          <div class="game-card available" id="game-freefire">
              <img src="img/game/ff_game.jpg" alt="Free Fire">
              <h3>Free Fire</h3>
          </div>
      </div>
      
      <div class="games-container">
        <!-- Featured Games -->
        <div class="game-card featured">
          <img src="img/game/lol.png" alt="League of Legends">
          <div class="featured-tag">FEATURED</div>
          <h3>League of Legends</h3>
          <div class="overlay">Available Soon</div>
      </div>
      <div class="game-card featured">
          <img src="img/game/dota.png" alt="Dota 2">
          <div class="featured-tag">FEATURED</div>
          <h3>Dota 2</h3>
          <div class="overlay">Available Soon</div>
      </div>
      <div class="game-card featured">
          <img src="img/game/valorant.png" alt="VALORANT">
          <div class="featured-tag">FEATURED</div>
          <h3>VALORANT</h3>
          <div class="overlay">Available Soon</div>
      </div>
      <div class="game-card featured">
          <img src="img/game/overwatch.png" alt="Overwatch">
          <div class="featured-tag">FEATURED</div>
          <h3>Overwatch</h3>
          <div class="overlay">Available Soon</div>
      </div>
      <div class="game-card featured">
          <img src="img/game/fortnite.png" alt="Fortnite">
          <div class="featured-tag">FEATURED</div>
          <h3>Fortnite</h3>
          <div class="overlay">Available Soon</div>
      </div>
      <div class="game-card featured">
          <img src="img/game/eFootball.jpg" alt="eFootball">
          <div class="featured-tag">FEATURED</div>
          <h3>eFootball</h3>
          <div class="overlay">Available Soon</div>
      </div>
        <!-- Add other featured games similarly -->
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
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
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

document.getElementById('searchInput').addEventListener('input', function() {
    var searchTerm = this.value.toLowerCase();
    var gameCards = document.querySelectorAll('.game-card');

    gameCards.forEach(function(card) {
        var gameName = card.querySelector('h3').textContent.toLowerCase();
        if (gameName.includes(searchTerm)) {
            card.style.display = ''; // Show the card if it matches the search
        } else {
            card.style.display = 'none'; // Hide the card if it doesn't match
        }
    });
});

</script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
</body>
</html>
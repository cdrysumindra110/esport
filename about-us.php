<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();
$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;


?>


<!DOCTYPE html>
<html lang="en-US">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Esports Website</title>
    <link rel="stylesheet" href="./css/components.css">
    <link rel="stylesheet" href="./css/icons.css">
    <link rel="stylesheet" href="./css/responsee.css">
    <link rel="stylesheet" href="./owl-carousel/owl.carousel.css">
    <link rel="stylesheet" href="./owl-carousel/owl.theme.css">
    <!-- CUSTOM STYLE -->      
    <link rel="stylesheet" href="./css/template-style.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

  </head>

  <body class="size-1280 primary-color-red">

    <!-- HEADER -->
    <header role="banner" class="position-absolute">
      <!-- Top Bar -->
      <div class="top-bar full-width hide-s hide-m">
        <div class="right">
            <a href="tel:080055544444444" class="text-white text-primary-hover">Phone : +977 8888888888 </a> 
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
        <header class="section-head background-image" style="background-image:url(img/battleground.gif);">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              About InfiKnight
            </h1>
          
          </div>
    
        </header>



        <!-- Section 1 -->
        <section class="section-top-padding">      
          <div class="line">
            <div class="margin2x">         
              <div class="m-12 l-6">
                <h2 class="text-extra-strong text-size-80 text-m-size-40">What makes us unique?</h2>
                
                <p class="text-dark text-size-20 margin-bottom-30">
                Top-Tier Experience with Sponsors
                </p>
                <p>Enjoy cutting-edge technology and thrilling gameplay designed for all skill levels.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                24/7 Support
                </p>
                <p>Get prompt and professional assistance whenever you need it. You can access remote support for T3/T2 players.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                Competitive Tournaments for LAN
                </p>
                <p>Hosting local-level tournaments to promote culture and provide opportunities for underdog players.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                Brand Endorsement
                </p>
                <p>Any national and international brand promotion.</p>

              </div>
              
              <div class="m-12 l-6 margin-m-top-30">
                <!-- Image --> 
                <img src="./img/about1.jpg" alt="">
              </div> 
            </div>    
          </div>      
        </section>
        
        <!-- Section 2 -->
        <section class="section">      
          <div class="line">
            <div class="margin2x">              
              <div class="m-12 l-6 margin-m-top-30">
                <!-- Image --> 
                <img src="./img/mission.jpg" alt="">
              </div> 
                       
              <div class="m-12 l-6">
                <h2 class="text-extra-strong text-size-80 text-m-size-40">Our Vision</h2>
                <p>At InfiKnight, we are driven by a passion for competitive gaming. 
                  Our mission is to create a platform where gamers from all backgrounds can showcase 
                  their skills, compete at the highest levels, and connect with a global community of 
                  like-minded individuals. We believe in the power of esports to bring people together, 
                  inspire greatness, and push the boundaries of what's possible in the gaming world.
                </p>
                
                <h2 class="text-extra-strong text-size-80 text-m-size-40">Join Us</h2>
                <p class="padding background-orange text-white">
                  Whether you're a seasoned pro or just starting out, 
                  InfiKnight welcomes you to be part of our growing community. 
                  Stay tuned for upcoming events, exclusive content, and opportunities 
                  to connect with fellow gamers. Let's make history together in the world of esports!
                </p>
              </div>
            </div>    
          </div>      
        </section>
        
        <!-- Section 3 -->
        
        
        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(./img/contact_us.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Welcome to our esports hub!<br> Dive into the latest tournaments, team updates, and gaming news. Join the action and be part of our gaming community. </p>
          </div>            
          <div class="line">  
            <div class="s-12 m-12 l-3 center">
              <a href="our-services.php" class="s-12 button border-radius background-primary text-size-20 text-white">Contact Us</a>
            </div>
          </div>
            
          <!-- red full width arrow object -->
          <img class="arrow-object" src="img/object-red.svg" alt="">
        </section>
      </article>  

    </main>
    
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
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
    
  </body>
</html>
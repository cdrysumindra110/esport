<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();


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
            <span class="sep text-white">|</span> <a href="mailto:info@InfiKnight.com" class="text-white text-primary-hover"><i ></i>Email : info@InfiKnight.com</a>
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
           <li><a href="#">Tournaments</a>
              <ul>
                <li><a href="#">Upcoming Tournaments</a>
                  <ul class="game_container">
                    <a href="#"><li class="ga_me"> <img src="img/logo/pubg_logo.png" alt="Pubg Logo" class="ga_me-icon">Pubg Mobile</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/ff_logo.png" alt="FF Logo" class="ga_me-icon">Free Fire</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/cs_logo.png" alt="COD Logo" class="ga_me-icon">COD Mobile</li></a>
                    <a href="tour_reg.php" class="all-games"><li class="all-games-text">All Tournaments<i class="fas fa-arrow-right"></i></li></a>
                  </ul>
              </li>
                <li><a>Ongoing Tournaments</a></li>
                </ul>
            </li>
            <li><a href="news.php">News</a></li>
            <li><a href="our-services.php">Our Services</a></li>
             
            <li><a href="organize.php">Organize</a></li>
            <li><a href="about-us.php">About</a></li>
            <li><a href="#"><i class="fas fa-user"></i></a>
              <ul>
                <li><a href="signin.php">Signin</a></li>
                <li><a href="signup.php">Signup</a></li>
                <li><a href="index.php">Logout</a></li>
              </ul>
            </li>
            <!-- <li>
              <a href="#">
              <label class="plane-switch">
              <input type="checkbox">
              <div>
                  <div>
                      <svg viewBox="0 0 13 13">
                          <path d="M1.55989957,5.41666667 L5.51582215,5.41666667 L4.47015462,0.108333333 L4.47015462,0.108333333 C4.47015462,0.0634601974 4.49708054,0.0249592654 4.5354546,0.00851337035 L4.57707145,0 L5.36229752,0 C5.43359776,0 5.50087375,0.028779451 5.55026392,0.0782711996 L5.59317877,0.134368264 L7.13659662,2.81558333 L8.29565964,2.81666667 C8.53185377,2.81666667 8.72332694,3.01067661 8.72332694,3.25 C8.72332694,3.48932339 8.53185377,3.68333333 8.29565964,3.68333333 L7.63589819,3.68225 L8.63450135,5.41666667 L11.9308317,5.41666667 C12.5213171,5.41666667 13,5.90169152 13,6.5 C13,7.09830848 12.5213171,7.58333333 11.9308317,7.58333333 L8.63450135,7.58333333 L7.63589819,9.31666667 L8.29565964,9.31666667 C8.53185377,9.31666667 8.72332694,9.51067661 8.72332694,9.75 C8.72332694,9.98932339 8.53185377,10.1833333 8.29565964,10.1833333 L7.13659662,10.1833333 L5.59317877,12.8656317 C5.55725264,12.9280353 5.49882018,12.9724157 5.43174295,12.9907056 L5.36229752,13 L4.57707145,13 L4.55610333,12.9978962 C4.51267695,12.9890959 4.48069792,12.9547924 4.47230803,12.9134397 L4.47223088,12.8704208 L5.51582215,7.58333333 L1.55989957,7.58333333 L0.891288881,8.55114605 C0.853775374,8.60544678 0.798421006,8.64327676 0.73629202,8.65879796 L0.672314689,8.66666667 L0.106844414,8.66666667 L0.0715243949,8.66058466 L0.0715243949,8.66058466 C0.0297243066,8.6457608 0.00275502199,8.60729104 0,8.5651586 L0.00593007386,8.52254537 L0.580855011,6.85813984 C0.64492547,6.67265611 0.6577034,6.47392717 0.619193545,6.28316421 L0.580694768,6.14191703 L0.00601851064,4.48064746 C0.00203480725,4.4691314 0,4.45701613 0,4.44481314 C0,4.39994001 0.0269259152,4.36143908 0.0652999725,4.34499318 L0.106916826,4.33647981 L0.672546853,4.33647981 C0.737865848,4.33647981 0.80011301,4.36066329 0.848265401,4.40322477 L0.89131128,4.45169723 L1.55989957,5.41666667 Z" fill="currentColor"></path>
                      </svg>
                  </div>
                  <span class="street-middle"></span>
                  <span class="cloud"></span>
                  <span class="cloud two"></span>
              </div>
              </label>
             </a>
          </li> -->
          </li>
        </div>
      </nav>
    </header>
    
    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              About Us
            </h1>
          
          </div>
    
        </header>
        
        <!-- Section 1 -->
        <section class="section-top-padding">      
          <div class="line">
            <div class="margin2x">         
              <div class="m-12 l-6">
                <h2 class="text-extra-strong text-size-80 text-m-size-40">Why choose us?</h2>
                
                <p class="text-dark text-size-20 margin-bottom-30">
                  Top-Tier Experience
                </p>
                <p>Enjoy cutting-edge technology and thrilling gameplay designed for all skill levels.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                  24/7 Support
                </p>
                <p>Get prompt and professional assistance whenever you need it.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                  Competitive Play
                </p>
                <p>Compete in exciting tournaments and showcase your skills.</p>

                <p class="text-dark text-size-20 margin-bottom-30">
                  Active Community
                </p>
                <p>Connect with fellow gamers and stay engaged through our vibrant community.</p>

              </div>
              
              <div class="m-12 l-6 margin-m-top-30">
                <!-- Image --> 
                <img src="img/about.jpeg" alt="">
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
                <img src="img/img-15.jpg" alt="">
              </div> 
                       
              <div class="m-12 l-6">
                <h2 class="text-extra-strong text-size-80 text-m-size-40">Our Mission</h2>
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
        <section class="section text-center background-primary">  
          <div class="line">
            <p class="text-white text-handwrite text-size-90 margin-bottom-20 text-line-height-1">Play hard. Win harder.</p>
            <p class="text-white">
              Join the next generation of esports champions.<br>
              Compete, conquer, and elevate your game to the highest level. The arena is waiting for you.
            </p>
          </div>
        </section>
        
        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(img/parallax-06.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Welcome to our esports hub!<br> Dive into the latest tournaments, team updates, and gaming news. Join the action and be part of our gaming community. </p>
          </div>            
          <div class="line">  
            <div class="s-12 m-12 l-3 center">
              <a href="our-services.html" class="s-12 button border-radius background-primary text-size-20 text-white">Contact Us</a>
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
        <a href="/"><i class="icon-facebook_circle text-size-30 text-white"></i></a> 
        <a href="/"><i class="icon-twitter_circle text-size-30 text-white"></i></a>
        <a href="/"><i class="icon-google_plus_circle text-size-30 text-white"></i></a>
        <a href="/"><i class="icon-instagram_circle text-size-30 text-white"></i></a> 
        <a href="/"><i class="icon-linked_in_circle text-size-30 text-white"></i></a>                                                                       
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
            <button class="animated-btn right-button">&nbsp;&nbsp;Become our Client&nbsp;&nbsp;</button>
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
               <p>
                To create a thriving esports ecosystem where players can showcase their skills, 
                teams can compete at the highest level, and fans can experience the excitement 
                of world-class gaming events.
               </p>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Useful Links</h4>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">FAQ</a><br>      
               <a class="text-primary-hover" href="contact-1.html">Contact Us</a><br>
               <a class="text-primary-hover" href="blog.html">Blog</a>
            </div>
            <div class="s-12 m-6 l-3 xl-2">
               <h4 class="text-white text-strong margin-m-top-30">Term of Use</h4>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Terms and Conditions</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Refund Policy</a><br>
               <a class="text-primary-hover" href="sample-post-without-sidebar.html">Disclaimer</a>
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
            <p class="text-size-12 margin-bottom-0">Copyright 2024, &Sigma;Indra65 - BCA 2k22</p>
            <p class="text-size-12 margin-bottom-0">Copyright 2024, MK38 - BCA 2k22</p>
            <p class="text-size-12">© 2024 InfiKnight Esports. All Rights Reserved.</p>
          </div>
          <div class="s-12 l-6">
            <a class="right text-size-12 text-primary-hover" href="#" title="Esports Website">Design and coded by <br> Team &infin; </a>
          </div>
        </div>  
      </section>
    </footer>
    <script type="text/javascript" src="js/responsee.js"></script>
    <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="js/template-scripts.js"></script> 
    
  </body>
</html>
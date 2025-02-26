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
    <link rel="stylesheet" href="./css/template-style.css?ver=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@200;300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Mrs+Saint+Delafield&display=swap" rel="stylesheet">  
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    

    <script type="text/javascript" src="js/jquery-1.8.3.min.js"></script>
    <script type="text/javascript" src="js/jquery-ui.min.js"></script>   

    <style>
  .section ul {
    list-style-type: disc; /* Use bullet points */
    margin-left: 50px; /* Indent list items */
    padding-left: 10px; /* Add some padding inside the list */
    color: black; /* Set text color */
  }

  .section li {
    color: black; /* Set text color */
    font-size: 18px; /* Adjust font size */
    font-weight: 800;
    line-height: 1.6; /* Increase line spacing for readability */
  }

  .section ul li {
    margin-bottom: 10px; /* Add space between list items */
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
        <header class="section-head background-image" style="width: 100%; height: 100%; object-fit: cover;background-image:url(img/battleground.gif)">
          <div class="line">
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline" style="text-align: center;">
              Terms of Use
            </h1>
          </div>
        </header>

        
        <!-- Section 2 -->
        <!-- Section 3 -->
        <section class="section" style="width: 80%; margin: 0 auto; text-align: justify;">    
          <h2 class="text-extra-strong text-size-20 text-m-size-20">Terms of Use</h2>
          <h2 class="text-extra-strong text-size-20 text-m-size-20">Effective Date: 12/17/2024</h2>
          
          <h5 class="text-extra-strong text-size-20 text-m-size-20">Introduction</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px;">
            These Terms of Use govern your access to and use of the InfiKnight website and services. By accessing or using our website, participating in tournaments, or using our services, you agree to comply with these terms. If you do not agree to these terms, please do not use our website or services.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">1. User Responsibilities</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            By using InfiKnight’s services, you agree to:
            <ul>
              <li>Provide accurate, current, and complete information when creating an account or participating in any service</li>
              <li>Maintain the confidentiality of your account and password</li>
              <li>Comply with all applicable laws and regulations in relation to the use of our services</li>
              <li>Not engage in any activities that could harm the functionality or security of our website or disrupt other users' experience</li>
            </ul>
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">2. Prohibited Activities</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            You are prohibited from:
            <ul>
              <li>Using the website or services for any unlawful purpose</li>
              <li>Attempting to gain unauthorized access to our services or any related systems</li>
              <li>Distributing harmful code or engaging in any form of hacking or phishing</li>
              <li>Violating any intellectual property rights associated with our content</li>
            </ul>
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">3. Tournament Rules</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            Participants in InfiKnight tournaments must adhere to the official rules and guidelines provided. Failure to comply with tournament rules may result in disqualification or other penalties, at our sole discretion.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">4. Intellectual Property</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            All content on the InfiKnight website, including but not limited to text, graphics, logos, images, and software, is the property of InfiKnight or its licensors and is protected by copyright and intellectual property laws. You may not use, modify, distribute, or reproduce any content without express written permission.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">5. Disclaimers</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            The website and services are provided "as is" and "as available" without any warranties of any kind, either express or implied. We do not guarantee that the website or services will be error-free, secure, or uninterrupted. You use our website at your own risk.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">6. Limitation of Liability</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            InfiKnight will not be liable for any indirect, incidental, special, or consequential damages arising out of your use or inability to use our website or services. Our total liability to you, whether in contract, tort, or otherwise, will be limited to the amount you have paid, if any, for using the services.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">7. Termination of Account</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            We reserve the right to suspend or terminate your account or access to our services at any time, without notice, for violations of these terms or any other conduct that we deem inappropriate.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">8. Changes to Terms of Use</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            We may update these Terms of Use from time to time. Any changes will be posted on this page, and the effective date will be revised accordingly. We encourage you to review these terms periodically to stay informed about any updates.
          </p>

          <h5 class="text-extra-strong text-size-20 text-m-size-20">9. Governing Law</h5>
          <p class="text-extra-strong text-size-10" style="color: black; font-size : 18px; margin-left: 20px;">
            These Terms of Use will be governed by and construed in accordance with the laws of the jurisdiction in which InfiKnight operates, without regard to its conflict of law principles.
          </p>

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
    <script type="text/javascript" src="js/template-scripts.js"></script> 
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });

    // Accordian js
    let accordDT = jQuery(".accordion dt");
    accordDT.on("click", function () {
      $(this).toggleClass("expand");
      // jQuery(this).next('dd').slideDown(300).siblings('dd').slideUp(500);// only single toggle
      $(this).next("dd").slideToggle(300); //best for responsive toggle
    });
  </script>
    
  </body>
</html>
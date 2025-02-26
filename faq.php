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
      /* Accordion CSS */
      .accordion {
          max-width: 100%;
          margin: 0 auto;
      }
      .accordion dd {
          display: none;
          padding: 10px;
          font-size: large;
          border: 1px solid goldenrod;
      }
      .accordion dd:first-of-type {
          display: block;
      }
      .accordion dt {
          position: relative;
          background-color: aliceblue;
          box-shadow: 0 1px 4px 0 gray;
          margin-top: 15px;
          padding: 4px 10px;
          cursor: pointer;
          font-size: 18px;
      }
      .accordion dt:hover,
      .accordion dt.expand {
          background-color: darkcyan;
          color: #fff;
      }
      .accordion dt span {
          position: absolute;
          left: 10px;
      }
      .accordion dt::before,
      .accordion dt::after {
          content: "";
          display: inline-block;
          width: 16px;
          height: 3px;
          background-color: #000;
          position: absolute;
          top: 50%;
          right: 10px;
          transform: translate(0, -50%);
          transition: 0.3s;
      }
      .accordion dt::after {
          width: 4px;
          height: 16px;
          right: 16px;
      }
      .accordion dt.expand::after {
          right: 10px;
          width: 16px;
          height: 3px;
      }
      .accordion dt.expand::before,
      .accordion dt.expand::after {
          background-color: white;
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
              FAQ
            </h1>
          </div>
        </header>

        
        <!-- Section 2 -->
        <section class="section" style="width: 80%; margin: 0 auto;">    
          <h2 class="text-extra-strong text-size-40 text-m-size-40" style="text-align : center">Frequently Asked Questions (FAQs)</h2>
          <p>Welcome to the InfiKnight FAQ page. Here you’ll find answers to the most common questions about our Esports tournaments, registration process, rules, and services.</p>
          <dl class="accordion">
            <dt>1. How do I register for a tournament?</dt>
            <dd>
                To register, simply visit the tournament page, click on the "Register Now" button, and fill out the required details. Ensure all your team or individual information is accurate before submitting.
            </dd>

            <dt>2. Is there a registration fee for participating in tournaments?</dt>
            <dd>
                Yes, some tournaments may require a registration fee, while others are free. The fee will be clearly mentioned on the tournament details page.
            </dd>

            <dt>3. What payment methods do you accept?</dt>
            <dd>
                We accept payments via:<br>
                • Credit/Debit Cards<br>
                • PayPal<br>
                • Online Payment Gateways<br>
                All transactions are secure and processed through trusted providers.
            </dd>

            <dt>4. Can I participate as an individual, or do I need a team?</dt>
            <dd>
                It depends on the tournament format:<br>
                • Solo Tournaments: You can participate individually.<br>
                • Team-Based Tournaments: A team is required to participate.<br>
                Tournament details will specify whether the event is solo, duo, or team-based.
            </dd>

            <dt>5. How do I know if my registration is successful?</dt>
            <dd>
                After registering, you will receive a confirmation email with all the necessary details, including your registration ID and tournament information. Be sure to check your inbox (and spam folder).
            </dd>

            <dt>6. What are the rules for the tournaments?</dt>
            <dd>
                Each tournament has its own set of rules and guidelines, which are listed on the respective tournament page. These include game-specific rules, team behavior, penalties, and disqualification conditions.
            </dd>

            <dt>7. What should I do if I face technical issues during a tournament?</dt>
            <dd>
                If you encounter any technical issues, immediately contact our Support Team via:<br>
                • Live Chat on the website<br>
                • Email: [insert email address]<br>
                • Hotline: [insert phone number]
            </dd>

            <dt>8. How do I get the tournament schedule and updates?</dt>
            <dd>
                You will receive regular updates, including schedules, match timings, and results, via:<br>
                • Your registered email<br>
                • Notifications on our website<br>
                • SMS (if opted-in during registration)
            </dd>

            <dt>9. How are prizes distributed?</dt>
            <dd>
                Winners will be contacted via email or phone, and prize distribution will occur through:<br>
                • Direct bank transfer<br>
                • PayPal or other online payment methods<br>
                Ensure you provide accurate payment details during registration.
            </dd>

            <dt>10. Can I cancel my registration? Will I get a refund?</dt>
            <dd>
                Yes, you can cancel your registration before the tournament begins. Refund policies vary depending on the tournament. Refer to our Refund Policy for more details.
            </dd>

            <dt>11. How do I report unfair gameplay or cheating?</dt>
            <dd>
                If you witness any cheating, hacking, or unfair practices, report it immediately to our support team. Provide evidence such as screenshots, match IDs, or video recordings. We take cheating seriously and enforce strict penalties.
            </dd>

            <dt>12. How can I contact InfiKnight for support?</dt>
            <dd>
                You can reach us through:<br>
                • Email: [insert email address]<br>
                • Live Chat: Available on our website<br>
                • Phone: [insert contact number]<br>
                • Social Media: Follow us on [Facebook | Twitter | Instagram | Discord]
            </dd>

            <dt>13. Do you stream the tournaments live?</dt>
            <dd>
                Yes, selected tournaments are streamed live on our official platforms, including YouTube, Twitch, or Facebook. Check the tournament details for live streaming links.
            </dd>

            <dt>14. Can I partner or sponsor a tournament with InfiKnight?</dt>
            <dd>
                Absolutely! We welcome sponsorship and partnerships. Please contact us at [insert email address] to discuss collaboration opportunities.
            </dd>

            <dt>15. How do I stay updated about upcoming tournaments?</dt>
            <dd>
                To stay in the loop:<br>
                • Subscribe to our newsletter<br>
                • Follow us on social media platforms<br>
                • Check the Upcoming Tournaments section on our website.
            </dd>
          </dl>   
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
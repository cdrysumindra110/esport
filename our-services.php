<?php
// Include the config file
require_once 'config.php';

// Start the session
session_start();

// Initialize messages
$error_message = '';
$success_message = '';

$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  // Sanitize and validate form inputs
  $name = mysqli_real_escape_string($conn, $_POST['name']);
  $email = mysqli_real_escape_string($conn, $_POST['email']);
  $subject = mysqli_real_escape_string($conn, $_POST['subject']);
  $message = mysqli_real_escape_string($conn, $_POST['message']);

  // Insert data into the database
  $sql = "INSERT INTO contact (name, email, subject, message) 
          VALUES ('$name', '$email', '$subject', '$message')";

  if ($conn->query($sql) === TRUE) {
    $success_message = "Thank you for contacting us.";
  } else {
    $error_message = "Error: " . $sql . "<br>" . $conn->error;
  }
}

// Close connection
$conn->close();

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
        <header class="section-head background-image" style="background-image:url(img/services.gif);">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              <center>Our Services</center>
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>


        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>
        <!-- Section 1 -->
        <section class="full-width background-white">
          <div class="s-12 m-12 l-4">
            <!-- Change the background image -->  
            <div style="background-image: url(img/our_service.jpg);" class="contact-image" ></div>
          </div>
          <div class="s-12 m-12 l-4 text-center">
            <div class="padding-2x">
              <i class="icon-sli-location-pin text-primary text-size-30 center"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Company Address</h2>                
              <p>
                 Bafal Marga,<br>
                 Kathmandu, Nepal
              </p> 
              
              <i class="icon-sli-envelope text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">E-mail</h2>                
              <a class="text-primary-hover" href="mailto:infiknightesports@gmail.com">infiknightesports@gmail.com</a><section>
              <a class="text-primary-hover" href="mailto:contact@infiknightesports.com">contact@infiknightesports.com</a>
              
              <i class="icon-sli-earphones-alt text-primary text-size-30 center margin-top-20"></i>
              <h2 class="text-size-20 margin-bottom-0 text-strong">Phone Numbers</h2>                
              <p>
                +977 77777777<br>
                +977 88888888
              </p> 
            </div>
          </div>
          <div class="s-12 m-12 l-4">
            <!-- <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d1459734.5702753505!2d16.91089086619977!3d48.577103681657675!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1ssk!2ssk!4v1457640551761" width="100%" height="600" frameborder="0" style="border:0"></iframe> -->
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3224.2555422694672!2d85.27984737496206!3d27.704231625629888!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19e90cf303c5%3A0x7e0ccc224c0c39ae!2sInfiKnight%20Esports!5e1!3m2!1sen!2snp!4v1739861973629!5m2!1sen!2snp" width="100%" height="600" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>

          </div>
        </section>
        
        <!-- Section 3 -->
        <section class="section background-image" style="background-image:url(img/contact_bg.jpg)">
          <div class="s-12 m-12 l-4 center">
            <h3 class="text-white text-size-60 margin-bottom-20 text-center">Contact Form</h3>

            <form id="contactForm" name="contactForm" class="customform text-white" method="post" enctype="multipart/form-data" action="our-services.php">
              <div class="line">
                <div class="margin">
                  <div class="s-12 m-12 l-6">
                    <input name="email" id="email" class="required email" placeholder="Your e-mail" title="Your e-mail" type="text" required />
                  </div>
                  <div class="s-12 m-12 l-6">
                    <input name="name" id="name" class="name" placeholder="Your name" title="Your name" type="text" required />
                  </div>
                </div>
              </div>            

              <div class="line">       
                <div class="s-12">
                  <input name="subject" id="subject" class="required subject" placeholder="Subject" title="Subject" type="text" required />
                  <p class="subject-error form-error">Please enter your subject.</p>
                </div>
                <div class="s-12">
                  <textarea name="message" id="message" class="required message" placeholder="Your message" rows="3" required></textarea>
                  <p class="message-error form-error">Please enter your message.</p>
                </div>
                <div class="s-12">
                  <button class="button border-radius text-white background-primary" type="submit">Submit</button>
                </div>
              </div>    
            </form>
          </div>  
                
        </section>

     <!-- Section Services -->
     <section class="section">      
      <div class="line">
        <div class="margin2x">
           
           <!-- Image 1 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/team-management.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Team Management</h3>
                <p>
                  Our team management services help you build and maintain a strong, 
                  competitive team. We offer support in player recruitment, training 
                  schedules, and performance analysis. Our goal is to help your team
                  reach its full potential by providing the resources and guidance 
                  needed to succeed in the competitive esports landscape.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 2 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/event-planning.jpg"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Event Planning</h3>
                <p>
                  From small-scale events to large-scale tournaments, our event planning
                  services ensure every detail is covered. We manage venue selection, 
                  logistics, and on-site operations to create a memorable and smooth experience
                   for participants and spectators. Our experienced team coordinates every aspect
                    of your event, so you can focus on the action.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 3 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/sponsored.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Sponsorship</h3>
                <p>
                  We connect your esports events with potential sponsors to enhance their visibility 
                  and financial support. Our sponsorship services include identifying suitable partners, 
                  negotiating terms, and managing sponsorship agreements. We aim to create mutually 
                  beneficial relationships that provide value to both sponsors and your event.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
           
           <!-- Image 4 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/media-coverage.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Media Coverage</h3>
                <p>
                  Effective media coverage is crucial for promoting esports events. We offer comprehensive 
                  media services, including press releases, social media management, and live streaming. 
                  goal is to maximize your event’s exposure and reach a broader audience, ensuring that
                  your event garners the attention it deserves.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 5 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/custom-service.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Custom Services</h3>
                <p>
                  We understand that every esports event is unique, and we offer custom services tailored 
                  to your specific needs. Whether you require bespoke event features, specialized tournament 
                  formats, or unique promotional strategies, our team is here to design and deliver solutions 
                  that align with your vision and objectives.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>

           <!-- Image 6 -->
           <div class="s-12 m-6 l-6 margin-bottom-30">
              <!-- Photo -->
              <img src="img/tournaments.png"/>                                                                                                                                                                                                           
              <div class="margin-top">                          
                <!-- Title -->
                <h3 class="text-strong">Tournaments</h3>
                <p>
                  We specialize in organizing and executing esports tournaments that bring together players and 
                  fans from around the world. Our tournaments are meticulously planned to ensure a seamless experience, 
                  from initial registration to the final matches. We handle all aspects, including scheduling, match management, 
                  and prize distribution, ensuring a professional and exciting event for everyone involved.
                </p>                                                                                                                                                                                                                                                                                                                                                                                
              </div>
           </div>
        </div>                                                                                                
      </div>     
    </section>
    <!-- Section 9 -->
    <section>
      <!-- red full width arrow object -->
      <img class="arrow-object" src="img/object-red.svg" alt="">
    </section>
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
  document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
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
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
</body>
</html>
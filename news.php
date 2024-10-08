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
           <li><a href="tournaments.php">Tournaments</a>
              <ul>
                <li><a href="#">Upcoming Tournaments</a>
                  <ul class="game_container">
                    <a href="#"><li class="ga_me"> <img src="img/logo/pubg_logo.png" alt="Pubg Logo" class="ga_me-icon">Pubg Mobile</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/ff_logo.png" alt="FF Logo" class="ga_me-icon">Free Fire</li></a>
                    <a href="#"><li class="ga_me"> <img src="img/logo/cs_logo.png" alt="COD Logo" class="ga_me-icon">COD Mobile</li></a>
                    <a href="tournaments.php" class="all-games"><li class="all-games-text">All Tournaments<i class="fas fa-arrow-right"></i></li></a>
                  </ul>
              </li>
                <li><a>Ongoing Tournaments</a></li>
                </ul>
            </li>
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
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              ALL Games
            </h1>
          
          </div>
  
        </header>
        
        <div class="news-container">
          <header class="news-header">
              <h1>Latest News</h1>
              <div class="search-container">
                  <input type="search" placeholder="Search" class="search-input" />
                  <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <circle cx="11" cy="11" r="8"></circle>
                      <path d="m21 21-4.3-4.3"></path>
                  </svg>
              </div>
          </header>
      
          <div class="tab-menu">
              <button class="tab-button active" data-tab="latest">Latest</button>
              <button class="tab-button" data-tab="changelogs">Changelogs</button>
              <button class="tab-button" data-tab="web3">Web3</button>
              <button class="tab-button" data-tab="esports">Esports</button>
              <button class="tab-button" data-tab="features">Features</button>
              <button class="tab-button" data-tab="community">Community</button>
          </div>
      
          <div class="tab-content">
              <!-- Latest News Tab -->
              <div class="tab active" data-tab="latest">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="POL" class="news-image" />
                      <div class="news-details">
                          <p>by Community Gaming in <a href="#" class="category-link">Web3</a> · 2024-09-04</p>
                          <h2>More on the MATIC - POL migration for CG users</h2>
                          <p>Polygon is migrating MATIC token to the new POL token on September 4th. Here's more information for CG users!</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
      
              <!-- Changelogs Tab -->
              <div class="tab" data-tab="changelogs">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="Change" class="news-image" />
                      <div class="news-details">
                          <p>by Dev Team in <a href="#" class="category-link">Changelogs</a> · 2024-09-05</p>
                          <h2>New Feature Release: Version 2.0</h2>
                          <p>We have just released version 2.0 with exciting new features and improvements.</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
      
              <!-- Web3 Tab -->
              <div class="tab" data-tab="web3">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="Web3" class="news-image" />
                      <div class="news-details">
                          <p>by Web3 Insights in <a href="#" class="category-link">Web3</a> · 2024-09-06</p>
                          <h2>Understanding Web3: A Comprehensive Guide</h2>
                          <p>Explore the latest trends and developments in the Web3 space with our detailed guide.</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
      
              <!-- Esports Tab -->
              <div class="tab" data-tab="esports">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="Esports" class="news-image" />
                      <div class="news-details">
                          <p>by Esports Daily in <a href="#" class="category-link">Esports</a> · 2024-09-07</p>
                          <h2>Upcoming Esports Tournaments in 2024</h2>
                          <p>Get ready for the most anticipated esports tournaments of 2024 with our full schedule.</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
      
              <!-- Features Tab -->
              <div class="tab" data-tab="features">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="Feature" class="news-image" />
                      <div class="news-details">
                          <p>by Feature Highlights in <a href="#" class="category-link">Features</a> · 2024-09-08</p>
                          <h2>Feature Spotlight: Enhanced UI</h2>
                          <p>Check out the new UI enhancements that make your experience smoother and more intuitive.</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
      
              <!-- Community Tab -->
              <div class="tab" data-tab="community">
                  <div class="news-card">
                      <img src="/placeholder.svg" alt="Community" class="news-image" />
                      <div class="news-details">
                          <p>by Community News in <a href="#" class="category-link">Community</a> · 2024-09-09</p>
                          <h2>Community Events and Meetups</h2>
                          <p>Join our upcoming community events and meetups to connect with other enthusiasts.</p>
                          <a href="#" class="read-more-link">Read More</a>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      
      
      
      
        <!-- Section Videos Section --> 
        <section class="section line-full-width">      
          <div class="margin">
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
                <!-- YouTube Video Embed -->
                <iframe width="800" height="400" src="https://www.youtube.com/embed/ColfvV3PGvc?si=tDtjUanZC0PMu136" 
                title="Dota 2 The International 2024" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
                <!-- YouTube Video Embed -->
                <iframe width="800" height="400" src="https://www.youtube.com/embed/u1oqfdh4xBY?si=vhWBHZT9TCSuW0Mi" 
                title="Pubg Tournaments" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
                <!-- YouTube Video Embed -->
                <iframe width="800" height="400" src="https://www.youtube.com/embed/oq2Rz2I11l0?si=SMtxcxt0eeu_LMoK" 
                title="Free Fire Tournament" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
            <div class="s-12 m-6">
              <a class="image-with-hover-overlay image-hover-zoom margin-bottom">
                <!-- YouTube Video Embed -->
                <iframe width="800" height="400" src="https://www.youtube.com/embed/IS4uCwQ26po?si=6tHwitMYEMoHN8M8" 
                title="Esport Tournament 2024" frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen></iframe>
              </a>	
            </div>       
          </div>
        </section>
        
        <!-- Section 8 -->
      <section class="section background-grey">      
        <div class="line">
          <div class="margin2x">
            <div class="s-12 m-12 l-6">
               
              <h3 class="text-size-40 text-m-size-25"><b>Satisfied</b> Clients</h3>
              <div class="carousel-default owl-carousel carousel-hide-arrows text-left">
                <div class="item">
                  <div class="s-12">
                    <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                    <p class="margin-bottom">InfiKnight Esports exceeded our expectations in every way. 
                      Their commitment to quality and their ability to engage fans is unparalleled. 
                      We couldn't be happier with the results.</p>
                    <p class="text-primary text-size-16"><strong>Maria Garcia</strong> / Event Coordinator / GameFest</p>
                  </div>
                </div>
                
                <div class="item">
                  <div class="s-12">
                    <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                    <p class="margin-bottom">Working with InfiKnight Esports has been a game-changer for us. Their innovative approach and dedication to excellence truly set them apart. Our gaming events have never been more exciting or professionally managed.</p>
                    <p class="text-primary text-size-16"><strong>Alex Johnson</strong> / Marketing Director / eSports Central</p>
                  </div>
                </div>
                
                <div class="item">
                  <div class="s-12">
                    <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                    <p class="margin-bottom">The team at InfiKnight Esports is incredible. They bring a level of passion and expertise that’s unmatched in the industry. Their support has been invaluable to our esports initiatives.</p>
                    <p class="text-primary text-size-16"><strong>Jordan Smith </strong> / Team Manager / ProGamer League </p>
                  </div>
                </div>

                <div class="item">
                  <div class="s-12">
                    <div class="text-yellow margin-bottom-10"><i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i> <i class="icon-star text-size-12"></i></div>
                    <p class="margin-bottom">InfiKnight Esports has transformed how we approach competitive gaming. Their cutting-edge solutions and exceptional service have made them a key partner in our success.</p>
                    <p class="text-primary text-size-16"><strong> Taylor Brown </strong> / CEO / Ultimate Gaming Arena </p>
                  </div>
                </div>
                
              </div>
               
            </div>
            <div class="s-12 m-12 l-6">
              <h3 class="text-size-40 text-m-size-25 margin-bottom-30">The Latest From <b>Our Blog</b></h3> 
              <div class="carousel-default owl-carousel carousel-hide-arrows text-left">
                
                <div class="item">
                  <div class="margin margin-bottom-30">
                    <div class="s-12 m-3 l-3">
                      <a class="image-hover-zoom margin-m-bottom-30" href="/">
                        <img src="img/img-04.jpg" alt="">
                      </a>  
                    </div>
                    <div class="s-12 m-9 l-9">
                      <h4><a class="text-dark text-primary-hover text-strong" href="/">Unlocking the Secrets of a Successful Esports Event</a></h4>
                      <p>Alex Johnson shares insider tips on what makes a gaming event truly unforgettable. Discover how to captivate your audience and manage logistics like a pro.</p>
                      <a class="text-more-info text-primary" href="/">Read more</a>
                    </div>  
                  </div> 
                </div>

                <div class="item">
                  <div class="margin margin-bottom-30">
                    <div class="s-12 m-3 l-3">
                      <a class="image-hover-zoom margin-m-bottom-30" href="/">
                        <img src="img/img-03.jpg" alt="">
                      </a>  
                    </div>
                    <div class="s-12 m-9 l-9">
                      <h4><a class="text-dark text-primary-hover text-strong" href="/">Maximizing Your Esports Brand</a></h4>
                      <p>Maria Garcia explores cutting-edge marketing strategies that can elevate your esports brand. Learn how to create impactful campaigns and connect with your target audience.</p>
                      <a class="text-more-info text-primary" href="/">Read more</a>
                    </div>  
                  </div> 
                </div>
                
                <div class="item">
                  <div class="margin margin-bottom-30">
                    <div class="s-12 m-3 l-3">
                      <a class="image-hover-zoom margin-m-bottom-30" href="/">
                        <img src="img/img-15.jpg" alt="">
                      </a>  
                    </div>
                    <div class="s-12 m-9 l-9">
                      <h4><a class="text-dark text-primary-hover text-strong" href="/">The Future of Esports Arenas</a></h4>
                      <p>Taylor Brown discusses the latest trends in esports arenas and what to expect in the coming years. Get a glimpse into the innovations shaping the future of competitive gaming spaces.</p>
                      <a class="text-more-info text-primary" href="/">Read more</a>
                    </div>  
                  </div> 
                </div>

                <div class="item">
                  <div class="margin margin-bottom-30">
                    <div class="s-12 m-3 l-3">
                      <a class="image-hover-zoom margin-m-bottom-30" href="/">
                        <img src="img/img-11.jpg" alt="">
                      </a>  
                    </div>
                    <div class="s-12 m-9 l-9">
                      <h4><a class="text-dark text-primary-hover text-strong" href="/">Forging Strong Partnerships in Esports</a></h4>
                      <p>Casey Lee provides advice on building successful partnerships and collaborations within the esports industry. Discover how strategic alliances can drive growth and success.”</p>
                      <a class="text-more-info text-primary" href="/">Read more</a>
                    </div>  
                  </div> 
                </div>
                
              </div>
            </div>
          </div>
        </div>                                                                                     
      </section>

        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(img/parallax-06.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Deleniti pertinacia eu est, te his soluta quaestio pericula illum facilisis.<br> Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat. </p>
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
            <a class="right text-size-12 text-primary-hover" href="#" title="Esports Website">Developed by Team <span style="font-size: 25px;">&infin;</span>
            </a>
          </div>
      </div>  
    </section>
  </footer>
  <script type="text/javascript" src="js/responsee.js"></script>
  <script type="text/javascript" src="owl-carousel/owl.carousel.js"></script>
  <script type="text/javascript" src="js/template-scripts.js"></script> 
  
</body>
</html>
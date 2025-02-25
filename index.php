<?php
// Include the config file
require_once 'config.php';
// Start the session
session_start();
// Initialize messages
$error_message = '';
$success_message = '';

$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;

if (isset($_GET['error_signin'])) {
  $error_message = urldecode($_GET['error_signin']);
}
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
    

        <!-- Popup Message -->
        <div class="popup-message" id="popup-message"></div>

   <!-- MAIN -->
    <main role="main">    
      <!-- Main Carousel -->
      <header class="carousel-default owl-carousel carousel-main carousel-nav-white background-dark">
        
        <div class="item">
          <div class="s-12 center">
            <div class="section background-image-zoom-out">
              <!-- ZOOMED Carousel Image -->
              <div class="background-image background-image-object" style="background-image:url(img/1homebg3.png)"></div>
              
              <div class="line">
                <p class="animated-carousel-element text-strong text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-80 text-line-height-1 margin-bottom-40 margin-top-130">
                  Where gaming meets <span class="text-orange animated-carousel-element">Greatness</span> so<br>
                  Compete. Conquer. Repeat.
                </p>
                <div class="m-12 l-8">
                  <p class="animated-carousel-element text-white text-size-20 margin-bottom-30">
                    Where gaming meets greatness, champions are forged. The bravest gamers gather to test their skills and claim their place among the elite. Compete. Conquer. Repeat. The battle for greatness never ends.
                  </p>
                  <a class="button text-white background-primary margin-bottom-60" href="about-us-1.html">About Us</a> <a class="button text-white background-orange margin-bottom-60" href="contact-1.html">Contact Us</a>
                </div>
              </div>

            </div>
          </div>
        </div>
        
        <div class="item">
          <div class="s-12 center">
            <!-- For ZOOM effect add classes "background-image-zoom-out" or "background-image-zoom-in" -->
            <div class="section background-image-zoom-out">
              <!-- ZOOMED Carousel Image -->
              <div class="background-image background-image-object" style="background-image:url(img/1homebg.jpg)"></div>
                            
              <div class="line">
                <p class="animated-carousel-element text-strong text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-80 text-line-height-1 margin-bottom-40 margin-top-130">
                  Become the <span class="text-orange animated-carousel-element">Ultimate</span> Street<br>
                  Street Fighter
                </p>
                <div class="m-12 l-8">
                  <p class="animated-carousel-element text-white text-size-20 margin-bottom-30">
                    Unleash your power, dominate the arena, and become the ultimate Street Fighter. It’s not just about winning—it’s about proving you’re the best. Step up, fight hard, and claim your crown.
                  </p>
                  <a class="button text-white background-primary margins-bottom-60" href="about-us.php">About Us</a> <a class="button text-white background-orange margin-bottom-60" href="our-services.php">Contact Us</a>
                </div>
              </div>

            </div>
          </div>
        </div>
        
        <div class="item">
          <div class="s-12 center">
            <!-- For ZOOM effect add classes "background-image-zoom-out" or "background-image-zoom-in" -->
            <div class="section background-image-zoom-out">
              <!-- ZOOMED Carousel Image -->
              <div class="background-image background-image-object" style="background-image:url(img/1homebg2.jpg)"></div>
              
              <div class="line">
                <p class="animated-carousel-element text-strong text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-80 text-line-height-1 margin-bottom-40 margin-top-130">
                  Forge Your Legacy as the <span class="text-orange animated-carousel-element">Legendary</span>Champion
                </p>
                <div class="m-12 l-8">
                  <p class="animated-carousel-element text-white text-size-20 margin-bottom-30">
                    Champion, where every move you make carves your name into the history of champions. It’s not just about victory; it’s about defining who you are in the heat of battle. Rise above the rest, let your skills speak for themselves, and leave a mark that will never fade. The title of Ultimate Champion is yours for the taking—are you ready to claim it?
                  </p>
                  <a class="button text-white background-primary margin-bottom-60" href="about-us-1.html">About Us</a> <a class="button text-white background-orange margin-bottom-60" href="contact-1.html">Contact Us</a>
                </div>
              </div>

            </div>
          </div>
        </div>          
      </header>
        
      <!-- News Section  -->
      
      <section class="section-top-bottom-padding">  
        <div class="line">
          <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Latest News</h2>
        </div>
        
        <!-- Image / Text Carousel -->
        <div class="carousel-center owl-carousel carousel-main carousel-hide-pagination nav-bottom text-center">
        
          <div class="item">
            <div class="image-with-text-overlay">
              <div class="image-text-overlay"> 
                <div class="image-text-overlay-content padding-2x">
                  <!-- Text -->
                  <p class="text-orange text-size-30 margin-bottom-10">Dota 2 The International 2024</p>
                  <h3 class="text-white text-size-30 text-strong">Champions Crowned</h3>
                  <p class="text-white">The International 2024 concluded with a stunning victory by Team Secret. 
                    After an intense series of matches, they clinched the title, securing their place as one of 
                    the most dominant teams in the Dota 2 scene. The grand final showcased extraordinary gameplay 
                    and strategic depth, thrilling fans worldwide.</p> 
                </div> 
              </div>  
              <!-- Photo -->
              <img src="img/dota2.png"/>
            </div>                                                                                                                                                                                                              
          </div>
          
          <div class="item">
            <div class="image-with-text-overlay">
              <div class="image-text-overlay"> 
                <div class="image-text-overlay-content padding-2x">
                  <!-- Text -->
                  <p class="text-orange text-size-30 margin-bottom-10">PUBG Global Championship 2024</p>
                  <h3 class="text-white text-size-30 text-strong">Record-Breaking Viewership</h3>
                  <p class="text-white">The PUBG Global Championship 2024 set new records for viewership, 
                    with fans around the world tuning in to watch the intense battles. The championship 
                    was highlighted by an unexpected underdog victory, showing the unpredictable nature of PUBG esports.</p>  
                </div> 
              </div>  
              <!-- Photo -->
              <img src="img/pubg-viewership.png"/>
            </div>                                                                                                                                                                                                              
          </div>
          
          <div class="item">
            <div class="image-with-text-overlay">
              <div class="image-text-overlay"> 
                <div class="image-text-overlay-content padding-2x">
                  <!-- Text -->
                  <p class="text-orange text-size-30 margin-bottom-10">League of Legends Worlds 2024</p>
                  <h3 class="text-white text-size-30 text-strong">New Champions Emerge</h3>
                  <p class="text-white">The 2024 League of Legends World Championship saw an unexpected 
                    twist as a new team, Dragon's Claw, emerged victorious. Their innovative strategies 
                    and impeccable teamwork took the esports world by surprise, marking a new era in 
                    competitive League of Legends.</p>  
                </div> 
              </div>  
              <!-- Photo -->
              <img src="img/leagueoflegends.png"/>
            </div>                                                                                                                                                                                                              
          </div>
          
          <div class="item">
            <div class="image-with-text-overlay">
              <div class="image-text-overlay"> 
                <div class="image-text-overlay-content padding-2x">
                  <!-- Text --> 
                  <p class="text-orange text-size-30 margin-bottom-10">Valorant Champions Tour 2024</p>
                  <h3 class="text-white text-size-30 text-strong">International Teams Clash</h3>
                  <p class="text-white">The Valorant Champions Tour 2024 showcased electrifying matches between 
                    top international teams. The final saw Sentinels taking home the trophy after a fierce battle 
                    against Fnatic, highlighting the growing global competitiveness in Valorant esports.</p>
                </div> 
              </div>  
              <!-- Photo -->
              <img src="img/valorant.png"/>
            </div>                                                                                                                                                                                                              
          </div>
          
          <div class="item">
            <div class="image-with-text-overlay">
              <div class="image-text-overlay"> 
                <div class="image-text-overlay-content padding-2x">
                  <!-- Text --> 
                  <p class="text-orange text-size-30 margin-bottom-10">The CS :Major 2024</p>
                  <h3 class="text-white text-size-30 text-strong">A New Era of Dominance</h3>
                  <p class="text-white">Major 2024 ended with Astralis proving their dominance once again. Their tactical 
                    prowess and refined skills led them to victory, cementing their legacy as one of the greatest teams 
                    in Counter-Strike history.</p>
                </div> 
              </div>  
              <!-- Photo -->
              <img src="img/theCS.png"/>
            </div>                                                                                                                                                                                                              
          </div>
                       
        </div>
      </section>  


      <!-- Section 3 -->
      <section class="section background-white">
        <div class="line">
          <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Who We Are?</h2>
        </div>
        <div class="line">
          <div class="margin2x">
            <div class="m-12 l-6">
              <h3 class="text-strong">Passionate Gamers, Exceptional Talent</h3>
              <p class="margin-bottom-30">At InfiKnight, we’re more than just an esports 
                organization—we’re a community of dedicated gamers and industry professionals. 
                Our mission is to elevate the esports experience by combining top-tier talent with
                 cutting-edge technology and an unrelenting passion for gaming.
              </p>
              
              <!-- Icons -->              
              <div class="line">
                <div class="float-left">
                  <i class="fa-solid fa-chart-line icon2x text-primary"></i>
                </div>
                <div class="margin-left-50 margin-bottom-30">
                  <h3 class="text-strong">Esports Evolution</h3>
                  <p>At InfiKnight, we're a community of gamers, innovators, and game-changers dedicated 
                    to evolving the esports landscape through talent, technology, and passion.</p>            
                </div>
              </div>
              
              <div class="line">
                <div class="float-left">
                  <i class="fa-solid fa-handshake icon2x text-primary"></i>
                </div>
                <div class="margin-left-50 margin-bottom-30">
                  <h3 class="text-strong">United by Gaming</h3>
                  <p>We're a team of passionate gamers, talented professionals, and innovative thinkers 
                    united by our love for gaming and our drive to push the boundaries of esports.</p>            
                </div>
              </div>

              <div class="line">
                <div class="float-left">
                  <i class="fa-solid fa-flask icon2x text-primary"></i>
                </div>
                <div class="margin-left-50 margin-bottom-30">
                  <h3 class="text-strong">Gaming DNA</h3>
                  <p>We're a team of gamers at heart, driven by a shared passion for competition, 
                    innovation, and community. Our mission is to create an unparalleled esports 
                    experience that showcases the best of gaming.</p>            
                </div>
              </div>

            </div>
          
            <div class="m-12 l-6 margin-m-bottom-30">
              <!-- Image --> 
              <img src="img/who_we_are.jpg" alt="">
            </div>          
            
          </div>    
        </div>  
      </section>
      
      <!-- Section 4 -->
      <section class="full-width background-grey">
        <div class="s-12 m-12 l-6">
          <!-- Image -->  
          <img src="img/gaming_arena.jpg" alt="">
        </div>
        <div class="s-12 m-12 l-6">
          <!-- Texts -->
          <div class="padding-2x">
            <div class="line"> 
                <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">What about our Gaming Environments?</h2>
                <p class="text-dark text-size-20 margin-bottom-30">Claritas est etiam processus dynamicus, qui sequitur mutationem consuetudium lectorum. Mirum est notare quam littera gothica. Dolor in hendrerit in vulputate velit esse molestie consequat.</p>
                
                <!-- Accordion -->
                <div class="accordion">
                  <!-- Accordion section -->    
                  <div class="accordion-section">		
                      <h2 class="accordion-title background-primary text-white">Tournaments & Events</h2>								
                      <div class="accordion-content"> 
                        <p class="text-dark">Stay up-to-date on our upcoming tournaments and events. 
                          We compete in a variety of online and offline events throughout the year.</p>								                     	    		
                      </div>
                  </div>
                  <!-- Accordion section -->
                  <div class="accordion-section">		
                      <h2 class="accordion-title background-primary text-white">Player Profiles</h2>								
                      <div class="accordion-content"> 
                        <p class="text-dark">Meet our talented team of gamers and learn about their skills and experience.</p>								                     	    		
                      </div>
                  </div>
                  <!-- Accordion section -->
                  <div class="accordion-section">		
                      <h2 class="accordion-title background-primary text-white">Game Guides & Tips</h2>								
                      <div class="accordion-content"> 
                        <p class="text-dark">Improve your gaming skills with our expert guides and tips. We regularly post 
                          new content to help you improve your gameplay.</p>								                     	    		
                      </div>
                  </div>
               </div>
              
            </div>
          </div>
        </div>
      </section>
      
      <!-- Section 5 -->
      <section class="section background-dark">  
        <div class="line">
          <h2 class="text-white text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Our Registered Users</h2>
        </div>
        <div class="line">
          <div class="margin">
            <div class="s-12 m-12 l-3">
              <div class="block">
                <div class="count-to">
                   <span class="timer h1 text-size-60 text-red">3</span> <span class="h1 text-size-60 text-red">K+</span>
                  <p class="h1 text-size-20 margin-top-10 text-white text-thin">Tournament & Events</p> 
                </div>
              </div>
            </div>
            <div class="s-12 m-12 l-3">
              <div class="block">
                <div class="count-to">
                  <span class="timer h1 text-size-60 text-green">6+</span> <span class="h1 text-size-60 text-green">K+</span>
                  <p class="h1 text-size-20 margin-top-10 text-white text-thin">Matches</p> 
                </div>
              </div>
            </div>
            <div class="s-12 m-12 l-3">
              <div class="block">
                <div class="count-to">
                   <span class="timer h1 text-size-60 text-red">3K</span> <span class="h1 text-size-60 text-red">K+</span>
                  <p class="h1 text-size-20 margin-top-10 text-white text-thin">Teams</p> 
                </div>
              </div>
            </div>
            <div class="s-12 m-12 l-3">
              <div class="block">
                <div class="count-to">
                  <span class="timer h1 text-size-60 text-red">40K</span> <span class="h1 text-size-60 text-red">K+</span>
                  <p class="h1 text-size-20 margin-top-10 text-white text-thin">Players</p> 
                </div>
              </div>
            </div> 
          </div>
        </div>
      </section>
      
      
      <!-- Section 6 -->
      <section class="section-top-bottom-padding background-grey">      
        <div class="line">
          <h2 class="text-extra-strong text-size-80 text-m-size-40 margin-bottom-40">Systems Developer</h2>
        </div>
        
        <!-- Team Carousel -->                                                                                    
        <div class="carousel-blocks owl-carousel text-center">                                                                                              
          <div class="item">                                                                                                                                                                                                     
            <!-- Team Member 1 -->
            <div class="image-with-hover-overlay">
              <div class="image-hover-overlay background-primary"> 
                <div class="image-hover-overlay-content padding">
                  <!-- Team Member Bio -->
                  <p>Maintains System Standard</p>
                </div> 
              </div>  
              <!-- Team Member Photo -->
              <img src="img/team-01.jpg"/>
            </div>                                                                                                                                                                                                              
            <div class="margin-top">                          
              <!-- Team Member Description -->
              <h4 class="text-strong margin-bottom-10">Mohan Khatri</h4>                        
              <p class="margin-bottom-10 text-primary text-uppercase">CEO</p>                                                                                                                                          
              <div class="line">
                <a href="https://www.linkedin.com/in/igl-horaa-ab2859333/"><i class="icon-linked_in_circle text-primary-hover text-size-25"></i></a>
                 <a href="https://x.com/_Prabeen"><i class="icon-google_plus_circle text-primary-hover text-size-25"></i></a> 
                 <a href="https://www.facebook.com/profile.php?id=100090083115854"><i class="icon-twitter_circle text-primary-hover text-size-25"></i></a>
              </div>                                                                                                                                                                                                                                          
            </div>                                                                                                                                                            
          </div> 
                       
          <div class="item">                                                                                                                                                                                                     
            <!-- Team Member 2 -->                                                                                                                                                                                                                 
            <div class="image-with-hover-overlay">
              <div class="image-hover-overlay background-primary"> 
                <div class="image-hover-overlay-content padding">
                  <!-- Team Member Bio -->
                  <p>The overall Controller</p>
                </div> 
              </div>  
              <!-- Team Member Photo -->
              <img src="img/team-02.png"/>
            </div>                                                                                                                                                                                                              
            <div class="margin-top">                          
              <!-- Team Member Description -->
              <h4 class="text-strong margin-bottom-10">Sumindra Chaudhary</h4>                        
              <p class="margin-bottom-10 text-primary text-uppercase">BOD</p>                                                                                                                                          
              <div class="line">
                <a href="https://www.linkedin.com/in/sumindra-chaudhary-00769130b/"><i class="icon-linked_in_circle text-primary-hover text-size-25"></i></a> <a href="https://x.com/SumindraChaudh2"><i class="icon-twitter_circle text-primary-hover text-size-25"></i></a> <a href="https://www.facebook.com/sumindra.UCH"><i class="icon-facebook_circle text-primary-hover text-size-25"></i></a>
              </div>                                                                                                                                                                                                                                          
            </div>                                                                                                                                                                                                                                                                                                                                                                                    
          </div> 
                                                                                                                                   
        </div>                                                                                                                
      </section>

      
      <!-- Section 7 -->
      <section class="section background-image" style="background-image:url(img/full_bg.jpg);background-size: cover;background-repeat: no-repeat;">  
        <div class="line">
          <h2 class="text-white text-extra-strong text-size-80 text-m-size-40 margin-bottom-40"><span class="text-orange">InfiKnight Esports</span> Unleashing the Future of Gaming</h2>
          <p class="text-white text-size-20 margin-bottom-30">
            Experience the future of esports with InfiKnight. Our cutting-edge platform brings together top players and 
            fans in a vibrant community, setting new standards in competitive gaming. Join us and be part of the revolution.
          </p>
          <p class="text-white text-handwrite text-size-50 margin-bottom-20">Do you need help?</p>
          <a class="button text-white background-orange" href="our-services.php">Contact Our Team</a>  
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
                        <img src="img/parallax-04.jpg" alt="">
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
      
      <!-- Section 9 -->
      <section class="section background-white">
        <!-- red full width arrow object -->
        <img class="arrow-object" src="img/object-red.svg" alt="">
      </section>
      
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
            <p class="text-size-16 margin-bottom-0">Copyright 2024 &Sigma;Indra65 , MK38 - BCA 2022</p>
            <p class="text-size-12">Copyright 2025 InfiKnight Esports. All Rights Reserved.</p>
          </div>
          <div class="s-12 l-6">
            <a class="right text-size-12 text-primary-hover" href="#" title="Team InfiKnight">Developed by Team <span style="font-size: 25px;">&infin;</span>
            </a>
          </div>
        </div>  
      </section>
    </footer>
    <script type="text/javascript" src="./js/responsee.js"></script>
    <script type="text/javascript" src="./owl-carousel/owl.carousel.js"></script>
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

  // Function to show the popup message
function showPopupMessage(message, type) {
  const popup = document.getElementById('popup-message');
  popup.textContent = message;
  popup.className = 'popup-message'; 
  if (type === 'success') {
    popup.classList.add('success');
  } else if (type === 'error') {
    popup.classList.add('error');
  }
  popup.style.display = 'block'; 
  setTimeout(() => {
    popup.style.display = 'none'; 
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
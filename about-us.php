<?php
include('header.php');
$isSignin = isset($_SESSION['isSignin']) ? $_SESSION['isSignin'] : false;
?>

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
    
    <?php include('footer.php'); ?>
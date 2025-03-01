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
            <img src="img/logo/UCH.jpg" alt="UCH" class="image">
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
</body>
</html>
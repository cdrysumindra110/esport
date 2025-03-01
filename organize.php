<?php
include('header.php');

if (!$isSignin) {
  $error_message = "Please Login to Access This Page!";
  header("Location: index.php?error_signin=" . urlencode($error_message));
  exit();
}

?>

<link rel="stylesheet" href="./css/tour_org.css">
<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

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
      
      <div id="results">
      <p id="noGamesFound" style="display:none; text-align:center;">No Games Found</p>
      </div>
  </div>

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
var noGamesFoundMessage = document.getElementById('noGamesFound');

let isAnyCardVisible = false; // To track if any card is visible

gameCards.forEach(function(card) {
    var gameName = card.querySelector('h3').textContent.toLowerCase();
    if (gameName.includes(searchTerm)) {
        card.style.display = ''; // Show the card if it matches the search
        isAnyCardVisible = true; // At least one card is visible
    } else {
        card.style.display = 'none'; // Hide the card if it doesn't match
    }
});

// Show the "No Games Found" message if no cards are visible
if (isAnyCardVisible) {
    noGamesFoundMessage.style.display = 'none';
} else {
    noGamesFoundMessage.style.display = 'block'; // Show the message if no game matches
}
});

</script>
<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php include('footer.php'); ?>
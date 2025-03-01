<?php 
include('header.php');

// Define $isSignin based on session variable
$isSignin = isset($_SESSION['isSignin']) && $_SESSION['isSignin'] === true;

// Redirect if not logged in
if (!$isSignin) {
    header('Location: signin.php');
    exit();
}

// Get the tournament ID from the URL
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : null;

if (!$tournament_id) {
    die("<p style='color: red;'>Error: Missing tournament ID. Please provide a valid tournament ID in the URL.</p>");
}

// Fetch leaderboard data for the given tournament
$sql = "SELECT rank, name, prize FROM leaderboard WHERE tournament_id = ? ORDER BY rank ASC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

// Initialize leaderboard array
$leaderboard = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $leaderboard[] = $row;
    }
} else {
    $error_message = "No leaderboard data found for this tournament.";
}
$stmt->close();
$conn->close();
?>

<link rel="stylesheet" href="./css/result.css?ver=1.0">

    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
          <div class="line">
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
               Leaderboard
            </h1>
          </div>
        </header>
        
        <div class="news-container">
            <main class="app-container">
                <div class="app-header">
                    <h1>
                        <i class="fa fa-trophy fa-3x" style="color:#ff9633"></i> Placement
                    </h1>
                </div>
                <div class="app-leaderboard">
                    <div class="app-ribbon"></div>
                    <table>
                        <?php if (!empty($leaderboard)): ?>
                            <?php foreach ($leaderboard as $row): ?>
                                <tr>
                                    <td class="rank"><?php echo $row['rank']; ?></td>
                                    <td class="participant-name"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td class="score">
                                        $<?php echo number_format($row['prize']); ?>
                                        <?php if ($row['rank'] == 1): ?>
                                            <img class="award-icon" src="https://github.com/malunaridev/Challenges-iCodeThis/blob/master/4-leaderboard/assets/gold-medal.png?raw=true" alt="gold medal" />
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" style="text-align: center; color: red;">
                                    <?php echo isset($error_message) ? htmlspecialchars($error_message) : "No leaderboard data available. (N/A)"; ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </div>
            </main>
        </div>

        <!-- Section 4 -->
        <section class="section background-image" style="background-image:url(./img/contact_us.jpg)">
          <div class="line text-center">
            <h2 class="text-white text-extra-strong text-size-80 text-m-size-40">Do you need help?</h2>
            <p class="text-white">Welcome to our esports hub!<br>
            Dive into the latest tournaments, team updates, and gaming news. Join the action and be part of our gaming community.</p>
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
    
<?php include('footer.php'); ?>
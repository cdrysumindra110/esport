<?php
include('header.php');

// Check if the user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

$tournament_id = isset($_GET['tournament_id']) ? $_GET['tournament_id'] : null;
if (!$tournament_id) {
    die("Error: Missing tournament ID.");
    exit();
}

// SQL Query to fetch tournament and game data
$sql = "SELECT 
            t.id, 
            t.selected_game, 
            t.tname, 
            t.sdate, 
            t.stime, 
            t.about, 
            t.bannerimg, 
            t.user_id,
            b.bracket_type, 
            b.match_type, 
            u.uname AS creator_name,
            g.id as game_record_id,
            g.game_id, 
            g.password, 
            g.expire_time
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN game g ON t.id = g.tournament_id
        WHERE t.id = ?";

// Prepare and execute the query
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the tournament detail statement: " . $conn->error);
}

$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $tournament = $result->fetch_assoc();

    // Assign variables from the $tournament array
    $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
    $tname = $tournament['tname'] ?? 'Unknown Tournament';
    $sdate = $tournament['sdate'] ?? '';
    $stime = $tournament['stime'] ?? '';
    $about = $tournament['about'] ?? '';
    $bannerimg = $tournament['bannerimg'] ?? '';
    $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';

    // Game-related data
    $game_id = isset($tournament['game_id']) && !is_null($tournament['game_id']) && $tournament['game_id'] !== '' 
               ? $tournament['game_id'] : 'N/A';
    $password = isset($tournament['password']) && !is_null($tournament['password']) && $tournament['password'] !== '' 
                ? $tournament['password'] : 'N/A';
    $expire_time = isset($tournament['expire_time']) && !is_null($tournament['expire_time']) 
                   ? $tournament['expire_time'] : 'N/A';

    // Format the date to show month and day in words
    if ($sdate && $sdate != '') {
        try {
            $date = new DateTime($sdate);
            $sdate = $date->format('F j, Y');
        } catch (Exception $e) {
            $sdate = 'Invalid Date';
        }
    }

    // FIXED: Check if the expiration date has passed
    $current_time = new DateTime();
    $is_expired = false;
    $expire_time_obj = null;
    $time_remaining = 0;
    
    if ($expire_time !== 'N/A' && $expire_time != '') {
        try {
            $expire_time_obj = new DateTime($expire_time);
            
            // Compare dates properly
            if ($current_time > $expire_time_obj) {
                $is_expired = true;
            } else {
                $is_expired = false;
                // Calculate time remaining for countdown
                $time_remaining = $expire_time_obj->getTimestamp() - $current_time->getTimestamp();
            }
        } catch (Exception $e) {
            $expire_time_obj = null;
            $is_expired = false;
        }
    }

    // Set values based on expiration status
    $game_id_value = $is_expired ? "Expired" : $game_id;
    $password_value = $is_expired ? "Expired" : $password;
    
} else {
    die("No tournament data found with that ID.");
}
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tournament Game Details - <?php echo htmlspecialchars($tname); ?></title>
    <link rel="stylesheet" href="css/tour_org.css">
    <!-- popup -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-alpha1/dist/css/bootstrap.min.css">
    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        /* Overall Container Styling */
        .tournament-reg_container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
            background: linear-gradient(135deg, #3498db, #8e44ad);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        /* Left Container (Countdown and Text) */
        .left-container {
            flex: 1;
            padding: 40px;
            color: white;
            font-family: 'Arial', sans-serif;
            position: relative;
            overflow: hidden;
        }

        /* Heading styles */
        .left-container h1, 
        .left-container h2, 
        .left-container h3 {
            margin: 10px 0;
            font-family: 'Helvetica', sans-serif;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }

        .left-container h1 {
            font-size: 2rem;
            color: #ecf0f1;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .left-container h2 {
            font-size: 1.5rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }

        /* Countdown Timer */
        .countdown {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-top: 40px;
            font-size: 1.5rem;
            font-weight: bold;
            text-align: center;
        }

        .time-section {
            padding: 10px 20px;
            margin: 0 10px;
            background-color: rgba(0, 0, 0, 0.3);
            border-radius: 10px;
            min-width: 80px;
        }

        .separator {
            font-size: 1.8rem;
            margin: 0 15px;
            color: #f39c12;
        }

        .countdown #days,
        .countdown #hours,
        .countdown #minutes,
        .countdown #seconds {
            font-size: 2rem;
            color: #ecf0f1;
            font-weight: bold;
            text-shadow: 1px 1px 5px rgba(0, 0, 0, 0.4);
        }

        /* Right Container (Form) */
        .right-container {
            flex: 1;
            padding: 20px;
        }

        .right-container h2 {
            font-size: 2.5rem;
            color: #f39c12;
            text-align: center;
            margin-bottom: 10px;
        }
        
        .right-container p {
            font-size: 1.5rem;
            color: #f39c12;
            text-align: center;
            margin-top: 10px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            font-weight: bold;
            color: #fff;
            display: block;
            margin-bottom: 5px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
            color: #333;
            box-sizing: border-box;
        }

        .form-group input:read-only {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        /* Expired message styling */
        .expired-message {
            color: #ff6b6b !important;
            font-size: 1.8rem !important;
            font-weight: bold;
        }

        .active-message {
            color: #51cf66 !important;
        }

        @media (max-width: 768px) {
            .tournament-reg_container {
                flex-direction: column;
                align-items: center;
            }

            .left-container, .right-container {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .right-container h2 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- MAIN -->
    <main role="main"> 
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(img/full_bg.jpg)">
            <div class="line">
                <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
                    Game ID & Password
                </h1>
            </div>
        </header>
    </main>

    <!-- Tournament Game Details Container -->
    <div class="tournament-reg_container">
        <div class="left-container">
            <h1>WELCOME TO THE EXCLUSIVE eSPORTS TOURNAMENT</h1>
            <h2><?php echo htmlspecialchars($tname); ?></h2>
            <h1>Expiry Date and Time: 
                <?php 
                if ($expire_time_obj) {
                    echo htmlspecialchars($expire_time_obj->format('F j, Y \a\t g:i A'));
                    
                    // Show status badge
                    if ($is_expired) {
                        echo ' <span style="color: #ff6b6b; font-size: 1.2rem;">(Expired)</span>';
                    } else {
                        echo ' <span style="color: #51cf66; font-size: 1.2rem;">(Active)</span>';
                    }
                } else {
                    echo 'Not set yet'; 
                }
                ?>
            </h1>
            
            <?php if ($expire_time_obj && !$is_expired): ?>
            <h2>TIME REMAINING:</h2>
            <div class="countdown">
                <div class="time-section">
                    <div id="days">0</div>
                    <span>Days</span>
                </div>
                <div class="separator">:</div>
                <div class="time-section">
                    <div id="hours">0</div>
                    <span>Hours</span>
                </div>
                <div class="separator">:</div>
                <div class="time-section">
                    <div id="minutes">0</div>
                    <span>Minutes</span>
                </div>
                <div class="separator">:</div>
                <div class="time-section">
                    <div id="seconds">0</div>
                    <span>Seconds</span>
                </div>
            </div>
            <?php elseif ($expire_time_obj && $is_expired): ?>
            <h2 style="color: #ff6b6b;">THIS TOURNAMENT HAS EXPIRED</h2>
            <?php endif; ?>
        </div>
        
        <div class="right-container">
            <h2>Game ID and Password</h2>
            
            <?php if ($game_id == 'N/A' || $password == 'N/A'): ?>
                <p>Game ID and password will be available soon</p>
                
            <?php elseif ($is_expired): ?>
                <p class="expired-message">⏰ The Game ID and password have expired</p>
                <p style="font-size: 1rem; color: #fff;">Please contact the tournament organizer for more information.</p>
                
            <?php else: ?>
                <p class="active-message">✓ Game credentials are active</p>
                <div class="form-group">
                    <label for="game_id">Game ID</label>
                    <input type="text" id="game_id" name="game_id" value="<?php echo htmlspecialchars($game_id); ?>" readonly onclick="copyText(this)">
                </div>
                <div class="form-group">
                    <label for="password">Game Password</label>
                    <input type="text" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" readonly onclick="copyText(this)">
                </div>
                <p style="font-size: 0.9rem; color: #ccc; text-align: left;">Click on the field to copy the value</p>
            <?php endif; ?>
        </div>
    </div>

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

    <script type="text/javascript" src="./js/responsee.js"></script>
    <script type="text/javascript" src="./owl-carousel/owl.carousel.js"></script>
    <script type="text/javascript" src="./js/template-scripts.js"></script>

    <script>
    // FIXED: Countdown timer with proper expiration handling
    <?php if ($expire_time_obj && !$is_expired): ?>
    
    var expireTime = new Date("<?php echo $expire_time_obj->format('Y-m-d H:i:s'); ?>").getTime();
    
    // Update the countdown every second
    var x = setInterval(function() {
        var now = new Date().getTime();
        var distance = expireTime - now;

        // Calculate days, hours, minutes, and seconds
        var days = Math.floor(distance / (1000 * 60 * 60 * 24));
        var hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        var minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        var seconds = Math.floor((distance % (1000 * 60)) / 1000);

        // Display the results
        document.getElementById("days").innerHTML = days;
        document.getElementById("hours").innerHTML = hours;
        document.getElementById("minutes").innerHTML = minutes;
        document.getElementById("seconds").innerHTML = seconds;

        // If the countdown is finished, reload the page to show expired status
        if (distance < 0) {
            clearInterval(x);
            location.reload(); // Reload to show expired status
        }
    }, 1000);
    
    <?php endif; ?>

    // Copy text function with validation
    function copyText(element) {
        if (element.value && element.value !== 'Expired' && element.value !== 'N/A') {
            element.select();
            element.setSelectionRange(0, 99999); // For mobile devices
            document.execCommand('copy');
            
            // Show visual feedback
            var originalBg = element.style.backgroundColor;
            element.style.backgroundColor = '#d4edc9';
            setTimeout(function() {
                element.style.backgroundColor = originalBg;
            }, 200);
            
            // Optional: Show toast message instead of alert
            alert('✅ Copied: ' + element.value);
        } else {
            alert('❌ No valid text to copy');
        }
    }
    </script>

    <!-- jQuery and Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
</body>
</html>
<?php include('footer.php'); ?>
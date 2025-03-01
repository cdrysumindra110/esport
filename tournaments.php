<?php
include('header.php');

// Check if the user is signed in
if (!$isSignin) {
  $error_message = "Please Login to Access This Page!";
  header("Location: index.php?error_signin=" . urlencode($error_message));
  exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$tournaments = [];

// Fetch username
$stmt_uname = $conn->prepare("SELECT uname FROM users WHERE id = ?");
if ($stmt_uname) {
    $stmt_uname->bind_param("i", $user_id);
    $stmt_uname->execute();
    $stmt_uname->bind_result($uname);
    if ($stmt_uname->fetch()) {
        $_SESSION['uname'] = $uname;
    } else {
        // $error_message = "Error: Username not found for the user ID.";
    }
    $stmt_uname->close();
} else {
    $error_message = "Error preparing the statement: " . $conn->error;
}


$selected_game = isset($_GET['selected_game']) ? $_GET['selected_game'] : '';
$match_type = isset($_GET['match_type']) ? $_GET['match_type'] : '';
$sdate = isset($_GET['sdate']) ? $_GET['sdate'] : '';


$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS host_username 
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id
        WHERE 1=1";


if (!empty($selected_game)) {
    $sql .= " AND t.selected_game = ?";
}

if (!empty($match_type)) {
    $sql .= " AND b.match_type = ?";
}

if (!empty($sdate)) {
    $sql .= " AND t.sdate = ?";
}

$sql .= " ORDER BY t.id";

$stmt = $conn->prepare($sql);

$params = [];
$types = ''; 

if (!empty($selected_game)) {
    $params[] = $selected_game;
    $types .= 's'; 
}

if (!empty($match_type)) {
    $params[] = $match_type;
    $types .= 's'; 
}

if (!empty($sdate)) {
    $params[] = $sdate;
    $types .= 's'; 
}


if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tournaments[] = $row;
        }
    } else {
        $error_message = "No tournaments found.";
    }
    $stmt->close();
} else {
    $error_message = "Error preparing the tournament statement: " . $conn->error;
}

$conn->close();

?>

<link rel="stylesheet" href="./css/tour_org.css">
<link rel="stylesheet" href="./css/tournaments.css?v=1.0">

    <!-- Font Awesome CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 
    <style>
          /* General Form Styling */
          .filter-form {
              font-family: Arial, sans-serif;
              display: flex;
              gap: 20px;
              flex-wrap: wrap;
          }

          .filter-form div {
              display: flex;
              flex-direction: column;
              position: relative;
              margin-right: 10px;
          }

          .filter-form label {
              font-size: 14px;
              font-weight: bold;
              color: white;
              margin-bottom: 5px;
              margin-right: 10px;
          }

          /* Input and Select Styles */
          .filter-form select,
          .filter-form input[type="date"] {
              padding: 10px;
              font-size: 14px;
              border: 1px solid #ccc;
              border-radius: 5px;
              outline: none;
              transition: all 0.3s ease;
          }

          .filter-form select:focus,
          .filter-form input[type="date"]:focus {
              border-color: #007BFF;
              box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
          }

          /* Button Styling */
          .filter-form button {
              padding: 10px 20px;
              font-size: 14px;
              color: #fff;
              background-color: #007BFF;
              border: none;
              border-radius: 5px;
              cursor: pointer;
              transition: all 0.3s ease;
              align-self: center;
          }

          .filter-form button:hover {
              background-color: #0056b3;
              transform: scale(1.05);
              box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
          }

    /* Responsive Styles */
    @media screen and (max-width: 1024px) {
        .filter-form {
            flex-direction: column;
            align-items: center;
        }

        .filter-form div {
            width: 100%;
        }

        .ut-header__button {
            width: 100%;
            text-align: center;
        }

        .ut-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
    }

    @media screen and (max-width: 768px) {
        .ut-container {
            padding: 10px;
        }

        .ut-table thead {
            display: none;
        }

        .ut-table tbody,
        .ut-table tr {
            display: block;
            width: 100%;
        }

        .ut-table tr {
            margin-bottom: 10px;
            border: 1px solid #ccc;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        .ut-table__cell {
            display: flex;
            justify-content: space-between;
            padding: 5px 10px;
            font-size: 14px;
        }

        .ut-table__cell--first {
            flex-direction: column;
            text-align: center;
        }

        .ut-image img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }

        .ut-info {
            margin-top: 5px;
        }

        .ut-info__name,
        .ut-info__host {
            font-size: 16px;
        }
    }

    @media screen and (max-width: 480px) {
        .ut-header {
            text-align: center;
        }

        .filter-form div {
            width: 100%;
            display: block;
        }

        .filter-form select,
        .filter-form input {
            width: 100%;
        }

        .ut-table tr {
            padding: 8px;
        }

        .ut-table__cell {
            font-size: 12px;
        }

        .filter-form button,
        .ut-header__button {
            font-size: 14px;
            padding: 8px 12px;
        }

        .ut-info__name,
        .ut-info__host {
            font-size: 14px;
        }
    }
    </style>


    <!-- MAIN -->
    <main role="main">    
      <article>
        <!-- Header -->
        <header class="section-head background-image" style="background-image:url(./img/full_bg.jpg); background-size: cover; ">
          <div class="line">
  
            <h1 class="text-white text-s-size-30 text-m-size-40 text-l-size-50 text-size-70 headline">
              Tournaments
            </h1>
          
          </div>
  
        </header>
        
      </article>  

    </main>

    <div id="myTournamentsSection" class="profile-section">
          <!-- Filtering Form -->
      <div class="ut-container">
        <h2 class="unique-header">Available Tournaments</h2>
            <div class="ut-header">
              <form method="GET" action="" class="filter-form">
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="selected_game">Game:</label>
                      <select name="selected_game" id="selected_game">
                          <option value="">All</option>
                          <option value="PUBG" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'PUBG' ? 'selected' : ''; ?>>PUBG</option>
                          <option value="Call of Duty: Mobile" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'Call of Duty: Mobile' ? 'selected' : ''; ?>>Call of Duty: Mobile</option>
                          <option value="Free Fire" <?php echo isset($_GET['selected_game']) && $_GET['selected_game'] == 'Free Fire' ? 'selected' : ''; ?>>Free Fire</option>
                      </select>
                  </div>
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="match_type">Match Type:</label>
                      <select name="match_type" id="match_type">
                          <option value="">All</option>
                          <option value="solo" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'solo' ? 'selected' : ''; ?>>Solo</option>
                          <option value="duo" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'duo' ? 'selected' : ''; ?>>Duo</option>
                          <option value="squad" <?php echo isset($_GET['match_type']) && $_GET['match_type'] == 'squad' ? 'selected' : ''; ?>>Squad</option>
                      </select>
                  </div>
                  <div style="display: inline-block; margin-right: 15px;">
                      <label for="sdate">Date:</label>
                      <input type="date" name="sdate" id="sdate" value="<?php echo isset($_GET['sdate']) ? htmlspecialchars($_GET['sdate']) : ''; ?>">
                  </div>
                  <div style="display: inline-block;">
                      <button type="submit">FILTER</button>
                  </div>
              </form>
              <a href="tournaments.php" class="ut-header__button">EXPLORE TOURNAMENTS</a>
            </div>
            <table class="ut-table">
                <thead>
                    <tr>
                        <th class="ut-table__head">
                            <i class='fa fa-trophy' style='color:#00d696'></i> TOURNAMENTS
                        </th>
                        <th class="ut-table__head ut-table__head--game">
                            <i class='fa fa-flag-checkered' style='color:#00d696'></i> GAME
                        </th>
                        <th class="ut-table__head ut-table__cell--brackets">
                            <i class='fa fa-calendar' style='color:#00d696'></i> Brackets
                        </th>
                        <th class="ut-table__head ut-table__cell--date">
                            <i class='fa fa-calendar' style='color:#00d696'></i> DATE
                        </th>
                        <th class="ut-table__head ut-table__cell--prize" style="padding-left: 20px;">
                            <i class='fas fa-medal' style='color:#00d696'></i> PRIZE
                        </th>
                        <th class="ut-table__head"></th>
                    </tr>
                </thead>           
                <tbody>
                    <?php if (!empty($tournaments)): ?>
                        <?php foreach ($tournaments as $tournament): ?>
                        <tr class="ut-row" onclick="window.location.href='tour_freg.php?tournament_id=<?php echo $tournament['id']; ?>'" style="cursor: pointer;">
                            <td class="ut-table__cell ut-table__cell--first">
                                <div class="ut-image">
                                    <?php if (!empty($tournament['bannerimg'])): ?>
                                        <img src="data:image/jpeg;base64,<?php echo base64_encode($tournament['bannerimg']); ?>" alt="Tournament Banner">
                                    <?php else: ?>
                                        <img src="./img/dash-logo.png" alt="Default Tournament Banner">
                                    <?php endif; ?>
                                </div>
                                <div class="ut-info">
                                    <div class="ut-info__name"><?php echo htmlspecialchars($tournament['tname']); ?></div>
                                    <div class="ut-info__host">Hosted by 
                                        <span style="color: #00d696;">
                                            <?php echo htmlspecialchars($tournament['host_username']); ?>
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td class="ut-table__cell ut-table__cell--game">
                                <?php echo htmlspecialchars($tournament['selected_game']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--brackets">
                                <?php echo htmlspecialchars($tournament['bracket_type']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--date">
                                <?php echo htmlspecialchars($tournament['sdate']); ?>
                            </td>
                            <td class="ut-table__cell ut-table__cell--prize">
                                <?php echo htmlspecialchars($tournament['prizes']); ?>
                            </td>
                            <td class="ut-table__cell">
                                <a href="tour_freg.php?tournament_id=<?php echo $tournament['id']; ?>">
                                    <i class='fa fa-eye ut-row__icon-eye'></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">No tournaments found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
   
    <script src="./js/tour_org.js"></script>
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
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
</script>

<!-- Accordian jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<?php include('footer.php'); ?>
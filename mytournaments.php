<?php
include('header.php');

// Check if the user is signed in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

if (!isset($_SESSION['user_id'])) {
    die("Error: User ID not set in session.");
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$tournaments = [];

$stmt_user = $conn->prepare("SELECT uname, cover_photo, profile_pic FROM users WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $stmt_user->bind_result($uname, $cover_photo, $profile_pic);
    if ($stmt_user->fetch()) {
        $_SESSION['uname'] = $uname;
        $_SESSION['cover_photo'] = $cover_photo;
        $_SESSION['profile_pic'] = $profile_pic;
    } else {
        $error_message = "Error: User not found.";
    }
    $stmt_user->close();
} else {
    $error_message = "Error preparing the statement: " . $conn->error;
}


// Fetch username
$stmt_uname = $conn->prepare("SELECT uname FROM users WHERE id = ?");
if ($stmt_uname) {
    $stmt_uname->bind_param("i", $user_id);
    $stmt_uname->execute();
    $stmt_uname->bind_result($uname);
    if ($stmt_uname->fetch()) {
        $_SESSION['uname'] = $uname;
    } else {
        $error_message = "Error: Username not found for the user ID.";
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
        WHERE t.user_id = ?";  

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
$types = 'i'; 

$params[] = $user_id;  

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

<link rel="stylesheet" href="./css/mytournament.css?v=1.0">
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

          /* Responsive Adjustments */
          @media (max-width: 768px) {
              .filter-form {
                  flex-direction: column;
                  gap: 15px;
              }

              .filter-form div {
                  margin-right: 0;
              }
          }
          /* Responsive Styles */
        /* Toggle Button */
        .toggle-btn {
            display: none;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-bottom: 20px;
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

    <div class="profile-cont">
        <!-- Toggle Button -->
        <button class="toggle-btn" onclick="toggleButtons()">
            <i class="fa fa-bars"></i> Menu
        </button>
        <div class="btn-container">
            <button id="walletBtn" class="btn-cnt"><i class='fa fa-money'></i>Wallet</button>
            <button id="updateProfileBtn" class="btn-cnt"><i class='fas fa-user-edit'></i>Profile</button>
            <!-- <button id="teamProfileBtn" class="btn-cnt"><i class='fa fa-group'></i>Teams</button> -->
            <button id="myTournamentBtn" class="btn-cnt"><i class='fa fa-group'></i>My Tournaments</button>
            <button id="changeEmailBtn" class="btn-cnt"><i class='fa fa-envelope'></i>Change Email</button>
            <button id="changePasswordBtn" class="btn-cnt"><i class='fa fa-key'></i>Change Password</button>
            <button id="signoutBtn" class="btn-cnt"><i class='fa fa-sign-out'></i>Sign Out</button>
        </div>

        <div class="profile-container">
              <div class="cover-photo-container">
                  <div class="cover-photo">
                      <!-- <input type="file" name="cover_photo" id="cover_photo" accept="image/*" onchange="loadCoverPhoto(event)" class="file-input" />
                      <label for="cover_photo" class="cover-photo-label">change cover
                      </label> -->
                      <!-- Display user's cover photo or default cover photo -->
                      <img id="coverPhoto" 
                          name="coverPhoto" 
                          src="<?php echo isset($cover_photo) && !empty($cover_photo) 
                                      ? 'data:image/jpeg;base64,' . base64_encode($cover_photo) 
                                      : './img/dash-cover.png'; ?>" 
                          alt="Cover Photo" 
                          class="cover-photo-img" />
                      <div class="cover-overlay"></div>
                  </div>
              </div>
              <div class="profile-pic">
                  <!-- <input type="file" name="profile_pic" id="profile_pic" accept="image/*" onchange="loadProfilePic(event)" class="file-input" />
                  <label for="profile_pic" class="profile-pic-label">
                  </label> -->
                  <!-- Display user's profile picture or default profile picture -->
                  <img id="profilePic" 
                      name="profilePic" 
                      src="<?php echo isset($profile_pic) && !empty($profile_pic) 
                                  ? 'data:image/jpeg;base64,' . base64_encode($profile_pic) 
                                  : './img/dash-logo.png'; ?>" 
                      alt="Profile Picture" 
                      class="profile-pic-img" />
              </div>
          </div>

            <div id="myTournamentsSection" class="profile-section">
              <div class="unique-container">
                <h2 class="unique-header">Organized Tournaments</h2> 
                <div class="ut-container">
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
                      <a href="organize.php" class="ut-header__button"><i class="fas fa-plus" style="color: white;"></i> CREATE TOURNAMENTS</a>
                    </div>
                    <table class="ut-table">
                        <thead>
                            <tr>
                                <th class="ut-table__head">
                                    <i class='fa fa-trophy' style='color:#00d696'></i> TOURNAMENTS
                                </th>
                                <th class="ut-table__head ut-table__head--status">
                                    <i class='fa fa-flag-checkered' style='color:#00d696'></i> STATUS
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
                                    <div class="ut-container">
                                        <table class="ut-table">
                                            <tr class="ut-row" data-id="<?php echo $tournament['id']; ?>" data-match-type="<?php echo htmlspecialchars($tournament['match_type']); ?>" onclick="redirectToDetails(this)" style="cursor: pointer;">
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
                                                            <span style="color: #00f7ff;">
                                                                <?php echo htmlspecialchars($_SESSION['uname']); ?>
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="ut-table__cell ut-table__cell--status">
                                                    <div class="ut-status--new">NEW</div>
                                                </td>
                                                <td class="ut-table__cell ut-table__cell--date"><?php echo htmlspecialchars($tournament['sdate']); ?></td>
                                                <td class="ut-table__cell ut-table__cell--prize" style="padding-left: 20px;">
                                                    <?php echo html_entity_decode($tournament['prizes']); ?>
                                                </td>
                                                <td class="ut-table__cell">
                                                    <a href="tournament_details.php?tournament_id=<?php echo $tournament['id']; ?>">
                                                        <i class='fa fa-eye ut-row__icon-eye'></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5"><?php echo htmlspecialchars($error_message); ?></td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
              </div>
          </div>
    </div>

<!-- Popup page Scripts -->
<script>
   document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

  document.addEventListener('DOMContentLoaded', () => {
    // Get all buttons in the button container
    const buttons = document.querySelectorAll('.btn-cnt');
  
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            // Remove 'active' class from all buttons
            buttons.forEach(btn => btn.classList.remove('active'));
  
            // Add 'active' class to the clicked button
            this.classList.add('active');
  
            // Determine the URL to redirect based on button ID
            let redirectUrl = '';
            switch (this.id) {
                case 'walletBtn':
                    redirectUrl = 'wallet.php';
                    break;
                case 'updateProfileBtn':
                    redirectUrl = 'dashboard.php';
                    break;
                case 'teamProfileBtn':
                    redirectUrl = 'teams.php';
                    break;
                case 'myTournamentBtn':
                    redirectUrl = 'mytournaments.php';
                break;
                case 'changeEmailBtn':
                    redirectUrl = 'change_email.php';
                    break;
                case 'changePasswordBtn':
                    redirectUrl = 'change_password.php';
                    break;
                case 'signoutBtn':
                    redirectUrl = 'logout.php';
                    break;
                default:
                    redirectUrl = 'dashboard.php'; // Default fallback URL
            }
  
            // Redirect to the appropriate page
            window.location.href = redirectUrl;
        });
    });
  });

function loadCoverPhoto(event) {
    const coverPhoto = document.getElementById('coverPhoto');
    coverPhoto.src = URL.createObjectURL(event.target.files[0]);
}

function loadProfilePic(event) {
    const profilePic = document.getElementById('profilePic');
    profilePic.src = URL.createObjectURL(event.target.files[0]);
}


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
    popup.style.display = 'block';
    setTimeout(() => {
      popup.style.display = 'none';
    }, 3000); // Hide after 3 seconds
  }

  // Example usage for PHP error and success messages
  document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($success_message)): ?>
      showPopupMessage("<?php echo $success_message; ?>", 'success');
    <?php elseif (!empty($error_message)): ?>
      showPopupMessage("<?php echo $error_message; ?>", 'error');
    <?php endif; ?>
  });

    // Check if there's a success message and display it
    <?php if (!empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        showPopupMessage("<?php echo $success_message; ?>", 'success');
      });
    <?php endif; ?>
    
</script>
<script>
function redirectToDetails(row) {
    var tournamentId = row.getAttribute('data-id');
    var matchType = row.getAttribute('data-match-type');
    window.location.href = 'tournament_details.php?tournament_id=' + tournamentId + '&match_type=' + matchType;
}
</script>
<script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
  </script>
    <script>
    // Toggle Button Functionality
    function toggleButtons() {
        const btnContainer = document.querySelector('.btn-container');
        btnContainer.classList.toggle('active');
    }
</script>

<?php include('footer.php'); ?>
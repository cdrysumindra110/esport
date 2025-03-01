<?php
include('header.php');

if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch current user data (cover photo and profile picture)
$sql = "SELECT cover_photo, profile_pic FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($cover_photo, $profile_pic);

if ($stmt->num_rows > 0) {
    $stmt->fetch();
} else {
    die("Error: User not found.");
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {


if (isset($_POST['update_email'])) {
    $newEmail = filter_var($_POST['newEmail'], FILTER_SANITIZE_EMAIL);

    if (filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {

        $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->bind_param('si', $newEmail, $user_id);

        if ($stmt->execute()) {
            $success_message = 'Email updated successfully.';
        } else {
            $error_message = 'Error updating email: ' . $stmt->error;
        }
        $stmt->close();
    } else {
        $error_message = 'Invalid email format.';
    }
}


$query_string = '';
if (!empty($success_message)) {
    $query_string .= 'success_message=' . urlencode($success_message);
}
if (!empty($error_message)) {
    if (!empty($query_string)) $query_string .= '&';
    $query_string .= 'error_message=' . urlencode($error_message);
}

header('Location: change_email.php?' . $query_string);
exit;
}
?>

<link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'> 

    <div class="profile-cont">
    <div class="btn-container">
            <button id="walletBtn" class="btn-cnt"><i class='fa fa-money'></i>Wallet</button>
            <button id="updateProfileBtn" class="btn-cnt"><i class='fas fa-user-edit'></i>Profile</button>
            <button id="teamProfileBtn" class="btn-cnt"><i class='fa fa-group'></i>Teams</button>
            <button id="myTournamentBtn" class="btn-cnt"><i class='fa fa-group'></i>My Tournaments</button>
            <button id="changeEmailBtn" class="btn-cnt"><i class='fa fa-envelope'></i>Change Email</button>
            <button id="changePasswordBtn" class="btn-cnt"><i class='fa fa-key'></i>Change Password</button>
            <button id="signoutBtn" class="btn-cnt"><i class='fa fa-sign-out'></i>Sign Out</button>
        </div>

        <div class="profile-container">
              <div class="cover-photo-container">
                  <div class="cover-photo">
                      <!-- <input type="file" name="cover_photo" id="cover_photo" accept="image/*" onchange="loadCoverPhoto(event)" class="file-input" />
                      <label for="cover_photo" class="cover-photo-label">
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
                      <span class="icon-wrapper">
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

        <div class="unique-container">
          <div class="team-container">
          <h2 class="unique-header">Team</h2>
            <button class="create-team"><i class='fa fa-plus' style='color:#000000'></i>
              <span class="team-text">Create Team</span>
            </button>
          </div>
          
          <h2 class="unique-header">Team</h2>
          
          <h2 class="unique-header">Team</h2>
          
          <h2 class="unique-header">Team</h2>
          
          <h2 class="unique-header">Team</h2>
          
          <h2 class="unique-header">Team</h2>
          
          <h2 class="unique-header">Team</h2>
        </div>
    </div>

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

  document.addEventListener('DOMContentLoaded', () => {
    
    const buttons = document.querySelectorAll('.btn-cnt');
  
    buttons.forEach(button => {
        button.addEventListener('click', function() {
            
            buttons.forEach(btn => btn.classList.remove('active'));
  
            
            this.classList.add('active');
  
            
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
                    redirectUrl = 'dashboard.php'; 
            }
  
            
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

  
  document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($success_message)): ?>
      showPopupMessage("<?php echo $success_message; ?>", 'success');
    <?php elseif (!empty($error_message)): ?>
      showPopupMessage("<?php echo $error_message; ?>", 'error');
    <?php endif; ?>
  });

    
    <?php if (!empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        showPopupMessage("<?php echo $success_message; ?>", 'success');
      });
    <?php endif; ?>
</script>

<?php include('footer.php'); ?>
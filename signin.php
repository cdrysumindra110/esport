<?php
session_start();
require_once 'config.php';

$error_message = '';
$success_message = '';

// Check if the user is already logged in
if (isset($_SESSION['username'])) {
    $uname = $_SESSION['username'];
}

if (isset($_SESSION['success_message'])) {
  echo "<script type='text/javascript'>
          window.onload = function() { 
              showPopupMessage('".addslashes($_SESSION['success_message'])."', 'success'); 
          }
        </script>";
  unset($_SESSION['success_message']); // Clear message after displaying
}

// Display success/error messages
if (isset($_GET['success_signup'])) {
    $success_message = htmlspecialchars($_GET['success_signup']);
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}
if (isset($_GET['error_signin'])) {
    $error_message = htmlspecialchars($_GET['error_signin']);
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($error_message)."', 'error'); }</script>";
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Fetch user details from the database
    $stmt = $conn->prepare("SELECT id, uname, password, is_suspended, is_verified FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // Verify the user's password
        if (password_verify($password, $user['password'])) {
            // Check if the account is suspended
            if ($user['is_suspended'] == 1) {
                $error_message = "Your account has been suspended. Please contact support.";
            } elseif ($user['is_verified'] == 0) {
                $error_message = "Your account is not verified. Please check your email.";
            } else {
                // Successful login
                $_SESSION['isSignin'] = true;
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['uname'];

                $_SESSION['success_message'] = "Successfully logged in!";
                header("Location: dashboard.php?success_signin=" . urlencode($success_message));
                exit();
            }
        } else {
            $error_message = "Invalid email or password. Please try again.";
        }
    } else {
        $error_message = "User not registered. Please sign up.";
    }

    $stmt->close();
}

$conn->close();

// Handle error messages
if (!empty($error_message)) {
    echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($error_message)."', 'error'); }</script>";
}
?>






<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Sign In Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
  <link rel="stylesheet" href="./css/signin.css?v=1.0">
  <style>
    .popup-message {
      display: none;
      padding: 15px;
      margin: 20px;
      border-radius: 5px;
      color: white;
      position: fixed;
      top: 15px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
    }
    .popup-message.success {
      background-color: #4CAF50; /* Green */
    }
    .popup-message.error {
      background-color: #f44336; /* Red */
    }
    .password-container {
      position: relative;
      width: 100%;
    }

    #toggle-password {
      position: absolute;
      right: -2rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
    }
    #toggle-icon {
      font-size: 1rem;  /* Adjust the size as needed */
    }
  </style>
</head>
<body>

  <div class="popup-message" id="popup-message"></div>
  
  <div class="container" id="container">
    <div class="form-container sign-in-container">
      <form action="signin.php" method="post">
        <h1>Sign in</h1>
        <div class="social-container">
          <a class="social-icon" id="google-signin" title="Sign with Google"><i class="fab fa-google"></i></a>
          <a class="social-icon" id="facebook-signin" title="Sign with Facebook"><i class="fab fa-facebook-f"></i></a>
          <a class="social-icon" id="twitch-signin" title="Sign with Twitch"><i class="fab fa-twitch"></i></a>
          <a class="social-icon" id="discord-signin" title="Sign with Discord"><i class="fab fa-discord"></i></a>
        </div>
        <span>| or |</span>
        <input type="email" id="email" name="email" placeholder="Enter your Email id" required />
        <div class="password-container">
          <input type="password" id="password" name="password" placeholder="Enter your Password" required />
            <button type="button" id="toggle-password">
              <span id="toggle-icon">👁️</span>
            </button>
        </div>
        <a href="forgot-password.php" id="forgot-password">Forgot your password?</a>
        <button type="submit" id="signin-button" name="signin-button">Sign In</button>
      </form>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-right">
          <div class="logo-container">
            <a href="./index.php"><img src="./img/logo.png" alt="Logo"></a>
          </div>
          <h1>Welcome !</h1>
          <p>Don't have an account?</p>
          <button class="ghost" id="signUp">Sign Up</button>
        </div>
      </div>
    </div>
  </div>
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
    myModal.show();
  });

    // Function to toggle password visibility
    function togglePasswordVisibility(passwordFieldId, toggleButtonId, toggleIconId) {
        const passwordField = document.getElementById(passwordFieldId);
        const toggleButton = document.getElementById(toggleButtonId);
        const toggleIcon = document.getElementById(toggleIconId);
        
        if (passwordField.type === "password") {
            passwordField.type = "text";
            toggleIcon.textContent = "🙈"; // Change to 'Hide' icon when visible
        } else {
            passwordField.type = "password";
            toggleIcon.textContent = "👁️"; // Change to 'Show' icon when hidden
        }
    }

    // Add event listeners to toggle buttons
    document.getElementById('toggle-password').addEventListener('click', function() {
        togglePasswordVisibility('password', 'toggle-password', 'toggle-icon');
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
  popup.style.display = 'block';
  setTimeout(() => {
    popup.style.display = 'none';
  }, 3000); // Hide after 3 seconds
}

// Example usage for PHP error and success messages
document.addEventListener('DOMContentLoaded', function() {
  <?php if (!empty($success_message)): ?>
    showPopupMessage(<?php echo json_encode($success_message); ?>, 'success');
  <?php elseif (!empty($error_message)): ?>
    showPopupMessage(<?php echo json_encode($error_message); ?>, 'error');
  <?php endif; ?>
});

function toggleContainerAndRedirect() {
  const container = document.getElementById('container');
  container.classList.add('hidden'); 

  setTimeout(function() {
    window.location.href = 'signup.php';
  }, 300); 
}

document.getElementById('signUp').addEventListener('click', toggleContainerAndRedirect);
  </script>
    <script>
    var loader = document.getElementById("preloader");
    window.addEventListener("load", function () {
        loader.style.display = "none";
    });
    window.history.forward();

    setTimeout(() => {
    window.history.forward();
    }, 0);
  </script>
</body>
</html>

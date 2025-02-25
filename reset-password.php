<?php
session_start();
require_once 'config.php';

$error_message = '';
$success_message = '';

// Check if user is verified
$email = $_SESSION['verified_email'] ?? '';
if (empty($email)) {
    die("Unauthorized access!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($new_password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif ($new_password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Hash the new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password in database
        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_code = NULL, reset_expires = NULL WHERE email = ?");
        $stmt->bind_param("ss", $hashed_password, $email);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $success_message = "Password reset successful! You can now <a href='signin.php'>sign in</a>.";
            session_destroy(); // Destroy session after reset
        } else {
            $error_message = "Failed to reset password. Please try again.";
        }
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Reset Password</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
  <link rel="stylesheet" href="./css/signin.css">
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
    #toggle-password-confirm {
      position: absolute;
      right: -2rem;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
    }
    #toggle-icon-confirm {
      font-size: 1rem;  /* Adjust the size as needed */
    }
  </style>
</head>
<body>

  <div class="popup-message" id="popup-message"></div>
  
    <div class="container" id="container">
      <div class="form-container sign-in-container">
      <?php if (!empty($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
      <?php if (!empty($success_message)) echo "<p style='color:green;'>$success_message</p>"; ?>
        <form action="reset-password.php" method="post">
            <h1>Reset Your Password</h1>
            <div class="password-container">
                <input type="password" name="new_password" id="new_password" placeholder="New Password" required />
                <button type="button" id="toggle-password">
                    <span id="toggle-icon">👁️</span>
                </button>
            </div>
            <div class="password-container">
                <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm Password" required />
                <button type="button" id="toggle-password-confirm">
                    <span id="toggle-icon-confirm">👁️</span>
                </button>
            </div>
            <button type="submit" id="reset-button" name="reset-button">Reset Password</button>
        </form>
      </div>

      <div class="overlay-container">
          <div class="overlay">
              <div class="overlay-panel overlay-right">
                  <div class="logo-container">
                      <a href="./index.php"><img src="./img/logo.png" alt="Logo"></a>
                  </div>
                  <h1>Forgot Your Password?</h1>
                  <!-- <p>Enter the verification code we sent to your email to proceed with resetting your password.</p> -->
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
        togglePasswordVisibility('new_password', 'toggle-password', 'toggle-icon');
    });

    document.getElementById('toggle-password-confirm').addEventListener('click', function() {
        togglePasswordVisibility('confirm_password', 'toggle-password-confirm', 'toggle-icon-confirm');
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

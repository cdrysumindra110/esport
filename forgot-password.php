<?php
// session_start();
require_once 'config.php';
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$error_message = '';
$success_message = '';

// Display success/error messages
if (isset($_GET['success_signup'])) {
  $success_message = htmlspecialchars($_GET['success_signup']);
  echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($success_message)."', 'success'); }</script>";
}
if (isset($_GET['error_signin'])) {
  $error_message = htmlspecialchars($_GET['error_signin']);
  echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".addslashes($error_message)."', 'error'); }</script>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        // Check if the email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Generate verification code & token
            $verification_code = rand(100000, 999999);
            $reset_token = bin2hex(random_bytes(32));
            $hashed_code = password_hash($verification_code, PASSWORD_DEFAULT);
            $expires_at = date("Y-m-d H:i:s", strtotime("+30 minutes"));

            // Store reset token and code in the database
            $stmt = $conn->prepare("UPDATE users SET reset_token = ?, reset_code = ?, reset_expires = ? WHERE id = ?");
            $stmt->bind_param("sssi", $reset_token, $hashed_code, $expires_at, $user['id']);
            $stmt->execute();

            // Send the reset email
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'infiknightesports@gmail.com'; // Your email
                $mail->Password = 'ydln gvym ffji ioys'; // Your app password
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                $mail->setFrom('infiknightesports@gmail.com', 'InfiKnight');
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = "Password Reset Request";
                $mail->Body = "Use this code to reset your password: <b>$verification_code</b><br><br>
                    Or click the link below:<br>
                    <a href='http://localhost/esport/reset-password.php?token=$reset_token'>Reset Password</a>";

                $mail->send();
                $_SESSION['reset_email'] = $email;
                $_SESSION['success_message'] = "A verification link has been sent to your email. Please check your inbox.";
                header("Location: enter-reset-code.php"); // Redirect to code entry page
                exit();
            } catch (Exception $e) {
                $error_message = "Error sending email: " . $mail->ErrorInfo;
            }
        } else {
            $error_message = "No account found with this email.";
        }
    }
}


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
  </style>
</head>
<body>

  <div class="popup-message" id="popup-message"></div>
  
    <div class="container" id="container">
      <div class="form-container sign-in-container">
          <form action="forgot-password.php" method="post">
              <h1>Forgot Password</h1>
              <!-- <p>Enter your registered email to receive a password reset link.</p> -->
              <input type="email" id="email" name="email" placeholder="Enter your Email ID" required />
              <button type="submit" id="reset-button" name="reset-button">Submit</button>
          </form>
      </div>

      <div class="overlay-container">
          <div class="overlay">
              <div class="overlay-panel overlay-right">
                  <div class="logo-container">
                      <a href="./index.php"><img src="./img/logo.png" alt="Logo"></a>
                  </div>
                  <h1>Forgot Your Password?</h1>
                  <p>No worries! Enter your email, and we'll send you a reset link.</p>
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
    function togglePasswordVisibility(passwordFieldId, toggleButtonId) {
    const passwordField = document.getElementById(passwordFieldId);
    const toggleButton = document.getElementById(toggleButtonId);
    
    if (passwordField.type === "password") {
      passwordField.type = "text";
      toggleButton.textContent = "🙈"; // Change to 'Hide' icon when visible
    } else {
      passwordField.type = "password";
      toggleButton.textContent = "👁️"; // Change to 'Show' icon when hidden
    }
  }

  // Add event listeners to toggle buttons
  document.getElementById('toggle-password').addEventListener('click', function() {
    togglePasswordVisibility('password', 'toggle-password');
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

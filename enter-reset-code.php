<?php
session_start();
require_once 'config.php';

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_SESSION['reset_email'] ?? '';  // Get stored email from session
    $entered_code = $_POST['verification_code'];

    if (empty($email) || empty($entered_code)) {
        $error_message = "Invalid request. Please try again.";
    } else {
        // Fetch stored hashed code & expiration time from the database
        $stmt = $conn->prepare("SELECT reset_code, reset_expires FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $hashed_code = $user['reset_code'];
            $expires_at = strtotime($user['reset_expires']);

            // Check if the code is expired
            if (time() > $expires_at) {
                $error_message = "Verification code has expired. Please request a new one.";
            } elseif (password_verify($entered_code, $hashed_code)) {
                // Code is correct, allow password reset
                $_SESSION['verified_email'] = $email;
                header("Location: reset-password.php");
                exit();
            } else {
                $error_message = "Invalid verification code.";
            }
        } else {
            $error_message = "No reset request found for this email.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Enter Verification Code</title>
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
        <?php if (!empty($error_message)) echo "<p style='color:red;'>$error_message</p>"; ?>
        <form action="enter-reset-code.php" method="post">
        <h1>Enter Verification Code</h1>
        <input type="text" id="verification_code" name="verification_code" placeholder="Enter Verification Code" />
            <button type="submit" id="reset-button" name="reset-button">Verify Code</button>
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

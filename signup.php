<?php
// Database configuration
include_once('config.php');

// Initialize messages
$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Sanitize inputs
    $full_name = $conn->real_escape_string($full_name);
    $email = $conn->real_escape_string($email);
    $password = $conn->real_escape_string($password);
    $confirm_password = $conn->real_escape_string($confirm_password);

    // Validate form data
    if (empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error_message = "Passwords do not match.";
    } else {
        // Check if email already exists
        $sql = "SELECT * FROM users WHERE email = '$email'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $error_message = "Email already exists.";
        } else {
            // Hash the password
            $hashed_password = md5($password);

            // Insert new user into database
            $sql = "INSERT INTO users (full_name, email, password) VALUES ('$full_name', '$email', '$hashed_password')";
            if ($conn->query($sql) === TRUE) {
                $success_message = "Account created successfully!";
                // Redirect to sign-in page or another page
                echo "<script type='text/javascript'>window.onload = function() { showPopupMessage('".$success_message."', 'success'); }</script>";
                header("Location: signin.php");
                exit();
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }

    // Close the connection
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Signup Page</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:400,800">
  <link rel="stylesheet" href="css/signup.css">
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
  </style>
</head>
<body>
  <div class="popup-message" id="popup-message"></div>
  <div class="container" id="container">
    <div class="form-container sign-up-container">
      <form id="signup-form" action="signup.php" method="post">
        <h1>Sign Up</h1>
        <div class="social-container">
          <a class="social-icon" id="google-signup" title="Sign Up with Google"><i class="fab fa-google"></i></a>
          <a class="social-icon" id="facebook-signup" title="Sign Up with Facebook"><i class="fab fa-facebook-f"></i></a>
          <a class="social-icon" id="twitch-signup" title="Sign Up with Twitch"><i class="fab fa-twitch"></i></a>
          <a class="social-icon" id="discord-signup" title="Sign Up with Discord"><i class="fab fa-discord"></i></a>
        </div>
        <span>| or |</span>
        <input type="text" id="full_name" name="full_name" placeholder="Enter a valid Name" required />
        <input type="email" id="email" name="email" placeholder="Enter your Email" required />
        <input type="password" id="password" name="password" placeholder="Enter your Password" required />
        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" required />
        <button type="submit">Sign Up</button>
      </form>
    </div>
    <div class="overlay-container">
      <div class="overlay">
        <div class="overlay-panel overlay-left">
          <div class="logo-container">
            <a href="../index.html"><img src="img/logo.png" alt="Logo"></a>
          </div>
          <h1>Welcome!</h1>
          <p>Already have an account?</p>
          <button class="ghost" id="signIn">Sign In</button>
        </div>
      </div>
    </div>
  </div>
  <script>
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
    <?php if (!empty($error_message) || !empty($success_message)): ?>
      document.addEventListener('DOMContentLoaded', function() {
        <?php if (!empty($success_message)): ?>
          showPopupMessage("<?php echo $success_message; ?>", 'success');
        <?php elseif (!empty($error_message)): ?>
          showPopupMessage("<?php echo $error_message; ?>", 'error');
        <?php endif; ?>
      });
    <?php endif; ?>

    function toggleContainerAndRedirect() {
      const container = document.getElementById('container');
      container.classList.add('hidden'); 

      setTimeout(function() {
        window.location.href = 'signin.php';
      }, 300); 
    }

    document.getElementById('signIn').addEventListener('click', toggleContainerAndRedirect);

    // Client-side validation
    document.getElementById('signup-form').addEventListener('submit', function(event) {
      const fullName = document.getElementById('full_name').value.trim();
      const email = document.getElementById('email').value.trim();
      const password = document.getElementById('password').value;
      const confirmPassword = document.getElementById('confirm_password').value;

      if (fullName === '' || email === '' || password === '' || confirmPassword === '') {
        event.preventDefault();
        showPopupMessage('All fields are required.', 'error');
      } else if (!/\S+@\S+\.\S+/.test(email)) {
        event.preventDefault();
        showPopupMessage('Invalid email format.', 'error');
      } else if (password !== confirmPassword) {
        event.preventDefault();
        showPopupMessage('Passwords do not match.', 'error');
      }
    });
  </script>
</body>
</html>
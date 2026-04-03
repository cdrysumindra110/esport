<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Destroy all session data
session_unset();
session_destroy();

// Redirect to the login page or home page
header("Location: ../admin_login.php");
exit(); // Ensure no further code is executed
?>

<?php
// 1. Database Connection
include('../config.php'); 

// 2. The Data to Insert
$email = "admin@gmail.com";
$plain_password = "admin123";

// 3. SECURE HASHING (Crucial: Your login script must use password_verify)
$hashed_password = password_hash($plain_password, PASSWORD_BCRYPT);

// 4. The SQL Query
$sql = "INSERT INTO admin (email, password) VALUES ('$email', '$hashed_password')";

if (mysqli_query($conn, $sql)) {
    echo "<h3>Success!</h3>";
    echo "Admin account created.<br>";
    echo "<b>Email:</b> $email <br>";
    echo "<b>Password:</b> $plain_password";
} else {
    echo "Error: " . mysqli_error($conn);
}
?>
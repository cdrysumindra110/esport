<?php
// session_start(); 
include_once("config.php"); 
// admin1 is the password
// Initialize messages
$error_message = '';
$success_message = '';   


if (isset($_POST['email']) && isset($_POST['password'])) {

    $email = htmlspecialchars(trim($_POST["email"]));
    $password = trim($_POST["password"]);

    $stmt = $conn->prepare("SELECT * FROM admin WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email); 

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();


        if (password_verify($password, $user['password'])) {

            $_SESSION['isLogin'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email']; 
            $_SESSION['message'] = 'Successfully logged in!'; 


            header("Location: ./admin/admin.php");
            exit();  
        } else {
          
            $_SESSION['message'] = "Unauthorized Login!"; 
        }
    } else {

        $_SESSION['message'] = "Unauthorized Login!"; 
    }

    // Close the statement
    $stmt->close();
}
?>



<!DOCTYPE html>
<html>
<head>
    <title>InfiKnight Admin Login Form</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="keywords" content="Gaming Login Form Widget Tab Form,Login Forms,Sign up Forms,Registration Forms,News letter Forms,Elements"/>
    <script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
    <link href="./css/admin-page.css" rel="stylesheet" type="text/css" media="all" />
</head>

<body>
<div id="preloader" style="background: #1E1E2F url(./img/loader.gif) no-repeat center center; 
    background-size: 4.5%;height: 100vh;width: 100%;position: fixed;z-index: 999;">
</div>
<div class="popup-message" id="popup-message"></div>
    <div class="padding-all">
        <div class="header">
            <h1><a href="index.php"><img src="./img/dash-logo.png" alt=" "></a> Admin Login Form</h1>
        </div>

        <div class="design-w3l">
            <div class="mail-form-agile">
                <form action="admin_login.php" method="post">
                    <input type="text" name="email" id="email" placeholder="Enter email..." required=""/>
                    <input type="password"  name="password" id="password" class="padding" placeholder="Enter Password" required=""/>
                    <input type="submit" value="Login">
                </form>
            </div>
          <div class="clear"> </div>
        </div>
        
        <div class="footer">
        <p>© 2022 All Rights Reserved | Design by Team <a href="#" > &infin; InfiKnight </a></p>
        </div>
    </div>

    <script>
          document.addEventListener('DOMContentLoaded', function () {
            setTimeout(function() {
                var myModal = new bootstrap.Modal(document.getElementById('staticBackdrop'));
                myModal.show();
            }, 1000); // 1-second delay before modal appears
        });
        // Display popup message when page loads
        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($_SESSION['message'])): ?>
                showPopupMessage("<?php echo $_SESSION['message']; ?>", 'success');
                <?php unset($_SESSION['message']); ?> // Clear message after showing
            <?php endif; ?>
        });

        // Function to show the popup message
        function showPopupMessage(message, type) {
            var popup = document.getElementById('popup-message');
            popup.innerHTML = message;
            popup.classList.add(type);  // Add success or error class
            popup.style.display = 'block';  // Show the popup
            setTimeout(function() {
                popup.style.display = 'none';  // Hide the popup after 5 seconds
            }, 5000);
        }
    </script>
    <script>
        var loader = document.getElementById("preloader");
        window.addEventListener("load", function () {
            loader.style.display = "none";
        })
        window.history.forward();

        setTimeout(() => {
        window.history.forward();
        }, 0);
    </script>
</body>
</html>

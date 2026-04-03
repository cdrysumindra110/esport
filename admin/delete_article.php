<?php
include('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== true) {
    header('Location: ../admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $article_id = $_GET['id'];

    // SQL query to delete the article
    $sql = "DELETE FROM news_articles WHERE id = $article_id";
    
    if ($conn->query($sql) === TRUE) {
        // Redirect to the previous page after successful deletion
        header('Location: contents.php?success_message=Article deleted successfully');
        exit;
    } else {
        // Handle errors
        echo "Error deleting record: " . $conn->error;
    }
} else {
    echo "No article ID specified.";
}


// Handle deletion
if (isset($_GET['delete_rank'])) {
    $rank = intval($_GET['delete_rank']); // Ensure it's an integer

    $delete_query = "DELETE FROM leaderboard WHERE rank = ?";
    $stmt = $conn->prepare($delete_query);

    if ($stmt) {
        $stmt->bind_param("i", $rank);

        if ($stmt->execute()) {
            $_SESSION['message'] = "User deleted successfully.";
        } else {
            $_SESSION['error_message'] = "Error deleting user: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $_SESSION['error_message'] = "Database error: " . $conn->error;
    }

    header('Location: result.php?tournament_id=' . $tournament_id);
    exit();
}
?>

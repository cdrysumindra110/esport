<?php
include('../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['isLogin']) || $_SESSION['isLogin'] !== true) {
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit;
    }

    $user_id = mysqli_real_escape_string($conn, $_POST['user_id']);
    $current_status = (int) $_POST['is_suspended'];

    // Toggle the suspension status
    $new_status = $current_status ? 0 : 1;

    $query = "UPDATE users SET is_suspended = $new_status WHERE id = $user_id";
    if (mysqli_query($conn, $query)) {
        echo json_encode(['success' => true, 'is_suspended' => $new_status]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error updating user status']);
    }
}
?>

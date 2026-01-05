<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

// Get tournament ID from either POST or GET
if (isset($_POST['tournament_id'])) {
    $tournament_id = $_POST['tournament_id'];
} elseif (isset($_GET['id'])) {
    $tournament_id = $_GET['id'];
} else {
    die("Error: Tournament ID not provided.");
}

// Validate tournament ID
if (!is_numeric($tournament_id)) {
    die("Error: Invalid tournament ID format. ID: " . htmlspecialchars($tournament_id));
}

$tournament_id = intval($tournament_id);
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    die("Error: User not logged in properly. Please log in again.");
}

// First, verify the tournament exists and user owns it
$check_sql = "SELECT t.*, u.uname 
              FROM tournaments t 
              LEFT JOIN users u ON t.user_id = u.id 
              WHERE t.id = ?";
$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Error preparing check statement: " . $conn->error);
}

$check_stmt->bind_param("i", $tournament_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows === 0) {
    $check_stmt->close();
    die("Error: Tournament not found with ID: " . $tournament_id);
}

$tournament = $check_result->fetch_assoc();
$check_stmt->close();

// Check if user is the creator
if ($tournament['user_id'] != $user_id) {
    // Optional: Check if user is admin
    $is_admin = $_SESSION['is_admin'] ?? false;
    if (!$is_admin) {
        die("Error: You are not authorized to delete this tournament.");
    }
}

// Start transaction
$conn->begin_transaction();

try {
    // 1. Delete from streams table
    $delete_streams = "DELETE FROM streams WHERE tournament_id = ?";
    $stmt1 = $conn->prepare($delete_streams);
    if ($stmt1) {
        $stmt1->bind_param("i", $tournament_id);
        $stmt1->execute();
        $stmt1->close();
    }
    
    // 2. Delete from brackets table
    $delete_brackets = "DELETE FROM brackets WHERE tournament_id = ?";
    $stmt2 = $conn->prepare($delete_brackets);
    if ($stmt2) {
        $stmt2->bind_param("i", $tournament_id);
        $stmt2->execute();
        $stmt2->close();
    }
    
    // 3. Delete participants (handle all match types to be safe)
    
    // Delete solo participants
    $delete_solo = "DELETE FROM solo_registration WHERE tournament_id = ?";
    $stmt_solo = $conn->prepare($delete_solo);
    if ($stmt_solo) {
        $stmt_solo->bind_param("i", $tournament_id);
        $stmt_solo->execute();
        $stmt_solo->close();
    }
    
    // Delete duo participants and their players
    // First get all duo_ids for this tournament
    $get_duo_ids = "SELECT duo_id FROM duo_registration WHERE tournament_id = ?";
    $stmt_get_duo = $conn->prepare($get_duo_ids);
    if ($stmt_get_duo) {
        $stmt_get_duo->bind_param("i", $tournament_id);
        $stmt_get_duo->execute();
        $result_duo = $stmt_get_duo->get_result();
        
        while ($row = $result_duo->fetch_assoc()) {
            $duo_id = $row['duo_id'];
            $delete_duo_players = "DELETE FROM duo_players WHERE duo_id = ?";
            $stmt_dp = $conn->prepare($delete_duo_players);
            if ($stmt_dp) {
                $stmt_dp->bind_param("i", $duo_id);
                $stmt_dp->execute();
                $stmt_dp->close();
            }
        }
        $stmt_get_duo->close();
    }
    
    // Delete duo registrations
    $delete_duo_reg = "DELETE FROM duo_registration WHERE tournament_id = ?";
    $stmt_duo_reg = $conn->prepare($delete_duo_reg);
    if ($stmt_duo_reg) {
        $stmt_duo_reg->bind_param("i", $tournament_id);
        $stmt_duo_reg->execute();
        $stmt_duo_reg->close();
    }
    
    // Delete squad participants and their players
    // First get all squad_ids for this tournament
    $get_squad_ids = "SELECT squad_id FROM squad_registration WHERE tournament_id = ?";
    $stmt_get_squad = $conn->prepare($get_squad_ids);
    if ($stmt_get_squad) {
        $stmt_get_squad->bind_param("i", $tournament_id);
        $stmt_get_squad->execute();
        $result_squad = $stmt_get_squad->get_result();
        
        while ($row = $result_squad->fetch_assoc()) {
            $squad_id = $row['squad_id'];
            $delete_squad_players = "DELETE FROM squad_players WHERE squad_id = ?";
            $stmt_sp = $conn->prepare($delete_squad_players);
            if ($stmt_sp) {
                $stmt_sp->bind_param("i", $squad_id);
                $stmt_sp->execute();
                $stmt_sp->close();
            }
        }
        $stmt_get_squad->close();
    }
    
    // Delete squad registrations
    $delete_squad_reg = "DELETE FROM squad_registration WHERE tournament_id = ?";
    $stmt_squad_reg = $conn->prepare($delete_squad_reg);
    if ($stmt_squad_reg) {
        $stmt_squad_reg->bind_param("i", $tournament_id);
        $stmt_squad_reg->execute();
        $stmt_squad_reg->close();
    }
    
    // 4. Finally, delete the tournament itself
    $delete_tournament = "DELETE FROM tournaments WHERE id = ?";
    $stmt3 = $conn->prepare($delete_tournament);
    if ($stmt3) {
        $stmt3->bind_param("i", $tournament_id);
        $stmt3->execute();
        $stmt3->close();
    }
    
    // Commit transaction
    $conn->commit();
    
    // Success - redirect to tournaments page
    header('Location: mytournaments.php?success=Tournament+deleted+successfully');
    exit();
    
} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Log error and show message
    error_log("Delete tournament error: " . $e->getMessage());
    die("Error deleting tournament. Please try again or contact support.");
}

$conn->close();
?>
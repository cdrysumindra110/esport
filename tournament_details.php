<?php
include('header.php');

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if user is logged in
if (!isset($_SESSION['isSignin']) || !$_SESSION['isSignin']) {
    header('Location: signin.php');
    exit();
}

// Check if tournament_id is provided
if (!isset($_GET['tournament_id'])) {
    die("Error: Tournament ID not provided.");
}

$tournament_id = intval($_GET['tournament_id']);
$user_id = $_SESSION['user_id']; // Assuming you store user_id in session
$error_message = '';
$tournament = [];

// ========== CHECK IF USER IS TOURNAMENT CREATOR ==========
$sql_check = "SELECT user_id FROM tournaments WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
if (!$stmt_check) {
    die("Error preparing statement: " . $conn->error);
}
$stmt_check->bind_param("i", $tournament_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $tournament_data = $result_check->fetch_assoc();
    if ($tournament_data['user_id'] != $user_id) {
        // User is not the creator
        header('Location: mytournaments.php?error_message=Unauthorized+access');
        exit();
    }
} else {
    header('Location: mytournaments.php?error_message=Tournament+not+found');
    exit();
}
$stmt_check->close();

// ========== HANDLE TOURNAMENT DELETION ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_tournament']) && $_POST['delete_tournament'] === 'yes') {
    $delete_tournament_id = intval($_POST['tournament_id']);
    
    // Verify again that user owns this tournament
    $verify_sql = "SELECT user_id FROM tournaments WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    if (!$verify_stmt) {
        die("Error preparing verification: " . $conn->error);
    }
    $verify_stmt->bind_param("i", $delete_tournament_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_data = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    if ($verify_data['user_id'] != $user_id) {
        header('Location: mytournaments.php?error_message=Unauthorized+access');
        exit();
    }
    
    try {
        $conn->begin_transaction();
        
        // Delete in correct order to respect foreign key constraints
        $delete_queries = [
            "DELETE FROM solo_registration WHERE tournament_id = ?",
            "DELETE FROM duo_players WHERE duo_id IN (SELECT duo_id FROM duo_registration WHERE tournament_id = ?)",
            "DELETE FROM duo_registration WHERE tournament_id = ?",
            "DELETE FROM squad_players WHERE squad_id IN (SELECT squad_id FROM squad_registration WHERE tournament_id = ?)",
            "DELETE FROM squad_registration WHERE tournament_id = ?",
            "DELETE FROM brackets WHERE tournament_id = ?",
            "DELETE FROM leaderboard WHERE tournament_id = ?",
            "DELETE FROM organizer_predictions WHERE tournament_id = ?",
            "DELETE FROM streams WHERE tournament_id = ?",
            "DELETE FROM brackets WHERE tournament_id = ?",
            "DELETE FROM tournaments WHERE id = ?"
        ];
        
        foreach ($delete_queries as $query) {
            $stmt = $conn->prepare($query);
            if ($stmt) {
                $stmt->bind_param("i", $delete_tournament_id);
                $stmt->execute();
                $stmt->close();
            }
        }
        
        $conn->commit();
        
        header("Location: mytournaments.php?success_message=Tournament+deleted+successfully");
        exit();
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = "Error deleting tournament: " . $e->getMessage();
        error_log("Tournament deletion error: " . $e->getMessage());
    }
}

// ========== HANDLE TEAM/PARTICIPANT REMOVAL ==========
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove']) && $_POST['remove'] === 'yes') {
    $remove_tournament_id = intval($_POST['tournament_id']);
    $team_name = $conn->real_escape_string($_POST['team_name']);
    $match_type = $conn->real_escape_string($_POST['match_type']);

    // Verify user owns this tournament before removing team
    $verify_sql = "SELECT user_id FROM tournaments WHERE id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    if (!$verify_stmt) {
        die("Error preparing verification: " . $conn->error);
    }
    $verify_stmt->bind_param("i", $remove_tournament_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();
    $verify_data = $verify_result->fetch_assoc();
    $verify_stmt->close();
    
    if ($verify_data['user_id'] != $user_id) {
        header('Location: mytournaments.php?error_message=Unauthorized+access');
        exit();
    }

    $sql_remove = '';
    if ($match_type === 'solo') {
        $sql_remove = "DELETE FROM solo_registration WHERE tournament_id = ? AND player_name = ?";
    } elseif ($match_type === 'duo') {
        // First delete from duo_players, then from duo_registration
        $conn->begin_transaction();
        try {
            // Get duo_id first
            $get_duo_id = "SELECT duo_id FROM duo_registration WHERE tournament_id = ? AND team_name = ?";
            $stmt_get = $conn->prepare($get_duo_id);
            if (!$stmt_get) {
                throw new Exception("Error preparing get duo_id: " . $conn->error);
            }
            $stmt_get->bind_param("is", $remove_tournament_id, $team_name);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            if ($row = $result_get->fetch_assoc()) {
                $duo_id = $row['duo_id'];
                
                // Delete from duo_players
                $delete_players = "DELETE FROM duo_players WHERE duo_id = ?";
                $stmt_players = $conn->prepare($delete_players);
                if (!$stmt_players) {
                    throw new Exception("Error preparing delete duo_players: " . $conn->error);
                }
                $stmt_players->bind_param("i", $duo_id);
                $stmt_players->execute();
                $stmt_players->close();
                
                // Delete from duo_registration
                $delete_reg = "DELETE FROM duo_registration WHERE duo_id = ?";
                $stmt_reg = $conn->prepare($delete_reg);
                if (!$stmt_reg) {
                    throw new Exception("Error preparing delete duo_registration: " . $conn->error);
                }
                $stmt_reg->bind_param("i", $duo_id);
                $stmt_reg->execute();
                $stmt_reg->close();
                
                $conn->commit();
                header("Location: tournament_details.php?tournament_id=" . $remove_tournament_id . "&success_message=Team+removed+successfully");
                exit();
            }
            $stmt_get->close();
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error removing team: " . $e->getMessage();
        }
        return;
    } elseif ($match_type === 'squad') {
        // First delete from squad_players, then from squad_registration
        $conn->begin_transaction();
        try {
            // Get squad_id first
            $get_squad_id = "SELECT squad_id FROM squad_registration WHERE tournament_id = ? AND team_name = ?";
            $stmt_get = $conn->prepare($get_squad_id);
            if (!$stmt_get) {
                throw new Exception("Error preparing get squad_id: " . $conn->error);
            }
            $stmt_get->bind_param("is", $remove_tournament_id, $team_name);
            $stmt_get->execute();
            $result_get = $stmt_get->get_result();
            if ($row = $result_get->fetch_assoc()) {
                $squad_id = $row['squad_id'];
                
                // Delete from squad_players
                $delete_players = "DELETE FROM squad_players WHERE squad_id = ?";
                $stmt_players = $conn->prepare($delete_players);
                if (!$stmt_players) {
                    throw new Exception("Error preparing delete squad_players: " . $conn->error);
                }
                $stmt_players->bind_param("i", $squad_id);
                $stmt_players->execute();
                $stmt_players->close();
                
                // Delete from squad_registration
                $delete_reg = "DELETE FROM squad_registration WHERE squad_id = ?";
                $stmt_reg = $conn->prepare($delete_reg);
                if (!$stmt_reg) {
                    throw new Exception("Error preparing delete squad_registration: " . $conn->error);
                }
                $stmt_reg->bind_param("i", $squad_id);
                $stmt_reg->execute();
                $stmt_reg->close();
                
                $conn->commit();
                header("Location: tournament_details.php?tournament_id=" . $remove_tournament_id . "&success_message=Team+removed+successfully");
                exit();
            }
            $stmt_get->close();
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error removing team: " . $e->getMessage();
        }
        return;
    }

    if ($sql_remove) {
        $stmt_remove = $conn->prepare($sql_remove);
        if ($stmt_remove) {
            $stmt_remove->bind_param("is", $remove_tournament_id, $team_name);
            if ($stmt_remove->execute()) {
                header("Location: tournament_details.php?tournament_id=" . $remove_tournament_id . "&success_message=Team+removed+successfully");
                exit();
            } else {
                $error_message = "Error removing team: " . $stmt_remove->error;
            }
            $stmt_remove->close();
        } else {
            $error_message = "Error preparing removal query: " . $conn->error;
        }
    }
}

// ========== FETCH TOURNAMENT DETAILS ==========
$sql = "SELECT 
            t.id, t.selected_game, t.tname, t.sdate, t.stime, t.about, t.bannerimg, 
            b.bracket_type, b.match_type, b.solo_players, b.duo_teams, b.duo_players_per_team, 
            b.squad_teams, b.squad_players_per_team, b.rounds, b.placement, b.rules, b.prizes,
            s.provider, s.channel_name, s.social_media, s.social_media_input,
            u.uname AS creator_name
        FROM tournaments t
        LEFT JOIN brackets b ON t.id = b.tournament_id
        LEFT JOIN streams s ON t.id = s.tournament_id
        LEFT JOIN users u ON t.user_id = u.id  
        WHERE t.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Error preparing the tournament detail statement: " . $conn->error);
}
$stmt->bind_param("i", $tournament_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $tournament = $result->fetch_assoc();
    
    // Assign variables
    $selected_game = $tournament['selected_game'] ?? 'Unknown Game';
    $tname = $tournament['tname'] ?? 'Unknown Tournament';
    $sdate = $tournament['sdate'] ?? '';
    $stime = $tournament['stime'] ?? '';
    $bracket_type = $tournament['bracket_type'] ?? '';
    $match_type = $tournament['match_type'] ?? '';
    $about = $tournament['about'] ?? '';
    $rules = $tournament['rules'] ?? '';
    $prizes = $tournament['prizes'] ?? '';
    $social_media_input = $tournament['social_media_input'] ?? '';
    $bannerimg = $tournament['bannerimg'] ?? '';
    $creator_name = $tournament['creator_name'] ?? 'Unknown Creator';

    // Calculate registered count
    $registered_count = 0;
    $total_teams = 0;
    $registration_message = "Registered";

    if ($match_type == 'solo') {
        $sql_solo = "SELECT COUNT(*) AS registered FROM solo_registration WHERE tournament_id = ?";
        $stmt_solo = $conn->prepare($sql_solo);
        if ($stmt_solo) {
            $stmt_solo->bind_param("i", $tournament_id);
            $stmt_solo->execute();
            $result_solo = $stmt_solo->get_result();
            $row_solo = $result_solo->fetch_assoc();
            $registered_count = $row_solo['registered'];
            $total_teams = $tournament['solo_players'];
            $registration_message = "Players Registered";
            $stmt_solo->close();
        }
    } elseif ($match_type == 'duo') {
        $sql_duo = "SELECT COUNT(*) AS registered FROM duo_registration WHERE tournament_id = ?";
        $stmt_duo = $conn->prepare($sql_duo);
        if ($stmt_duo) {
            $stmt_duo->bind_param("i", $tournament_id);
            $stmt_duo->execute();
            $result_duo = $stmt_duo->get_result();
            $row_duo = $result_duo->fetch_assoc();
            $registered_count = $row_duo['registered'];
            $total_teams = $tournament['duo_teams'];
            $registration_message = "Teams Registered";
            $stmt_duo->close();
        }
    } elseif ($match_type == 'squad') {
        $sql_squad = "SELECT COUNT(*) AS registered FROM squad_registration WHERE tournament_id = ?";
        $stmt_squad = $conn->prepare($sql_squad);
        if ($stmt_squad) {
            $stmt_squad->bind_param("i", $tournament_id);
            $stmt_squad->execute();
            $result_squad = $stmt_squad->get_result();
            $row_squad = $result_squad->fetch_assoc();
            $registered_count = $row_squad['registered'];
            $total_teams = $tournament['squad_teams'];
            $registration_message = "Teams Registered";
            $stmt_squad->close();
        }
    }

    // Check if slots are full
    $slots_full_message = ($registered_count >= $total_teams) ? "Slots Full" : "";
    $teams_registered_message = "$registered_count / $total_teams $registration_message";

    // Fetch registered participants with player details
    $participants = [];
    if ($match_type == 'solo') {
        $sql_participants = "SELECT player_name AS team_name, NULL AS logo_path 
                           FROM solo_registration 
                           WHERE tournament_id = ?";
        $stmt_participants = $conn->prepare($sql_participants);
        if ($stmt_participants) {
            $stmt_participants->bind_param("i", $tournament_id);
            $stmt_participants->execute();
            $result_participants = $stmt_participants->get_result();
            while ($row = $result_participants->fetch_assoc()) {
                $participants[] = $row;
            }
            $stmt_participants->close();
        }
    } elseif ($match_type == 'duo') {
        // Fetch duo teams with their players - Using 'name' column for player names
        $sql_participants = "SELECT dr.team_name, dr.logo_path, 
                           GROUP_CONCAT(CONCAT(dp.name, ' (', COALESCE(dp.ign, ''), ')')) as players
                         FROM duo_registration dr
                         LEFT JOIN duo_players dp ON dr.duo_id = dp.duo_id
                         WHERE dr.tournament_id = ?
                         GROUP BY dr.duo_id";
        $stmt_participants = $conn->prepare($sql_participants);
        if ($stmt_participants) {
            $stmt_participants->bind_param("i", $tournament_id);
            $stmt_participants->execute();
            $result_participants = $stmt_participants->get_result();
            while ($row = $result_participants->fetch_assoc()) {
                $row['players'] = $row['players'] ? explode(',', $row['players']) : [];
                $participants[] = $row;
            }
            $stmt_participants->close();
        } else {
            error_log("Failed to prepare duo participants query: " . $conn->error);
        }
    } elseif ($match_type == 'squad') {
        // Fetch squad teams with their players - Using 'name' and 'ign' columns
        $sql_participants = "SELECT sr.team_name, sr.logo_path, 
                           GROUP_CONCAT(CONCAT(sp.name, ' (', COALESCE(sp.ign, ''), ')')) as players
                         FROM squad_registration sr
                         LEFT JOIN squad_players sp ON sr.squad_id = sp.squad_id
                         WHERE sr.tournament_id = ?
                         GROUP BY sr.squad_id";
        
        $stmt_participants = $conn->prepare($sql_participants);
        if ($stmt_participants) {
            $stmt_participants->bind_param("i", $tournament_id);
            if ($stmt_participants->execute()) {
                $result_participants = $stmt_participants->get_result();
                while ($row = $result_participants->fetch_assoc()) {
                    $row['players'] = $row['players'] ? explode(',', $row['players']) : [];
                    $participants[] = $row;
                }
            } else {
                error_log("Squad query execution error: " . $stmt_participants->error);
                $error_message = "Error fetching squad participants: " . $stmt_participants->error;
            }
            $stmt_participants->close();
        } else {
            error_log("Failed to prepare squad query: " . $conn->error);
            $error_message = "Error preparing squad query: " . $conn->error;
        }
    }

} else {
    $error_message = "No tournament found with that ID.";
}
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($tname); ?> - Tournament Details</title>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .banner-cont{
        background-color: #282828;
        margin-top: 0;
        }
        
        .banner-container {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 70vh;
        position: relative;
        }
    
        .banner-img-container {
        width: 100%;
        display: flex;
        justify-content: center;
        position: relative;
        overflow: hidden;
        }
    
        .banner-img {
        position: relative;
        width: 100%;
        height: 100%;
        overflow: hidden;
        }
    
        .banner-photo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        position: relative;
        }
    
        .tournament-operation {
            display: flex;
            justify-content: flex-end;
            padding: 10px 20px;
        }
    
        .operation-btn {
            display: flex;
            gap: 10px;
            margin-right: 20px;
            position: relative;
        }
    
        .operation-btn button {
            background-color: rgb(30, 196, 141);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease, color 0.3s ease;
            position: relative;
        }
    
        .operation-btn button:hover {
            color: black;
        }
    
        .operation-btn .options {
            background-color: #a75928;
        }
    
        .operation-btn .options:hover {
            color: black;
        }
    
        .dropdown-menu {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #ffffff;
            border: 1px solid #ddd;
            border-radius: 5px;
            list-style: none;
            padding: 0;
            margin: 0;
            width: 200px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }
    
        .dropdown-menu li {
            padding: 10px;
            display: flex;
            align-items: center;
        }
    
        .dropdown-menu li i {
            margin-right: 8px;
        }
    
        .dropdown-menu li:hover {
            background-color: #f1f1f1;
        }
    
        .organizer-actions:hover .dropdown-menu {
            display: block;
        }
    
        .gameuser {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            padding: 10px 0;
            font-size: 21px;
        }
    
        .gameuser .titlename {
            color: aquamarine;
            margin: 0 5px;
            font-size: 24px;
        }
    
        .tournament-details {
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 20px;
            border-top: 1px solid grey;
            border-radius: 8px;
        }
    
        .details, .rules, .prizes, .schedule, .contact {
            width: 100%;
            border-right: 1px solid grey;
            padding: 10px 20px;
            color: #ddd;
            border-radius: 5px;
            text-align: center;
            cursor: pointer;
            transition: color 0.3s ease;
        }
    
        .details:hover, .rules:hover, .prizes:hover, .schedule:hover, .contact:hover {
            color: rgb(0, 255, 170);
        }
    
        .tour-title {
            font-size: 20px;
            font-weight: bold;
        }
    
        .content-container {
            display: none;
        }
    
        .content-container.active {
            display: block;
        }
    
        .container-row {
            display: flex;
            flex-direction: row;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
    
        .content-container {
            flex: 1;
            min-width: 200px;
            margin: 10px;
            padding: 10px;
            border-top: 1px solid grey;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
    
        .tournament-details div {
            cursor: pointer;
            margin: 5px;
        }
        
        .content-title{
        font-size: 18px;
        font-weight: bold;
        color: #ffffff;
        margin: 5px;
        padding: 5px;
        }
    
        .cont-title{
        font-size: 16px;
        color: #ffffff;
        margin: 5px;
        margin-right: 10px;
        padding:10px 5px;
        border-bottom: 0.5px solid grey;
        }
    
        .teams-registered {
            font-size: 20px;
            color: #fff;
            margin-top: 15px;
            font-weight: bold;
            text-align: center;
        }
        
        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            text-align: center;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .br-controls-dropdown {
            position: relative;
            display: inline-block;
            z-index: 1000;
        }
        
        .br-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            cursor: pointer;
            color: #f4f4f9;
            background-color: #2c2c44;
            border-radius: 6px;
            font-size: 0.95rem;
            border: none;
            transition: background 0.3s ease;
        }
        
        .br-btn:hover {
            background-color: #3a3a5e;
        }
        
        .br-dropdown-content {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background-color: #1a1a2e;
            min-width: 220px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            flex-direction: column;
            gap: 5px;
            padding: 10px 0;
            z-index: 1001;
        }
        
        .br-controls-dropdown.show .br-dropdown-content {
            display: flex;
        }
        
        .br-btn-small {
            width: 100%;
            padding: 8px 15px;
            text-align: left;
            background: none;
            border: none;
            color: #f4f4f9;
            font-size: 0.9rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .br-btn-small:hover {
            background-color: #2c2c44;
        }
        
        .participant-item {
            margin-bottom: 20px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            border-radius: 5px;
            background: #282828;
        }
        
        .participant-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        
        .team-logo {
            width: 40px;
            height: 40px;
            overflow: hidden;
            border-radius: 50%;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        
        .team-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .team-name {
            flex: 1;
            margin-left: 10px;
            font-weight: bold;
            color: white;
        }
        
        .remove-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .remove-btn:hover {
            background-color: #c82333;
        }
        
        .players-list {
            display: flex;
            align-items: center;
            margin-left: 50px;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .player-tag {
            background: #e0e0e0;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 14px;
            color: #333;
        }
        
        /* Share Modal Styles */
        .share-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }
        
        .share-modal-content {
            background-color: #282828;
            margin: 10% auto;
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
            color: white;
            animation: modalFadeIn 0.3s;
        }
        
        @keyframes modalFadeIn {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #444;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .close-modal {
            cursor: pointer;
            font-size: 24px;
            color: #aaa;
        }
        
        .close-modal:hover {
            color: white;
        }
        
        .share-options {
            margin: 20px 0;
        }
        
        .share-url-container, .social-share, .qr-code-container, .embed-container {
            margin-bottom: 20px;
        }
        
        .share-url-container input, .embed-container input {
            flex: 1;
            padding: 10px;
            background: #333;
            border: 1px solid #444;
            border-radius: 5px;
            color: white;
        }
        
        .social-share-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .social-btn {
            transition: all 0.3s ease;
            min-width: 120px;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            color: white;
        }
        
        .social-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
        
        .social-btn i {
            font-size: 18px;
        }
        
        .fb-btn {
            background: #3b5998;
        }
        
        .twitter-btn {
            background: #1da1f2;
        }
        
        .whatsapp-btn {
            background: #25d366;
        }
        
        .email-btn {
            background: #ea4335;
        }
        
        .copy-btn {
            background: #1ec48d;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .copy-btn:hover {
            background: #17a67d;
        }
        
        .embed-btn {
            background: #6f42c1;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .embed-btn:hover {
            background: #5a32a3;
        }
        
        .qr-code-container {
            text-align: center;
        }
        
        #qrCodeContainer {
            background: white;
            padding: 10px;
            border-radius: 5px;
            display: inline-block;
        }
        
        .modal-footer {
            border-top: 1px solid #444;
            padding-top: 10px;
            text-align: right;
        }
        
        .close-btn {
            background: #6c757d;
            color: white;
            border: none;
            padding: 8px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .close-btn:hover {
            background: #5a6268;
        }
        
        @media (max-width: 992px) {
            .operation-btn {
                flex-direction: column;
                align-items: center;
            }
        
            .content-container {
                flex: 1 1 100%;
            }
        
            .tournament-details {
                flex-direction: column;
                align-items: center;
            }
        }
        
        @media (max-width: 768px) {
            .banner-img img {
                max-height: 250px;
            }
        
            .operation-btn button {
                width: 100%;
            }
            
            .players-list {
                margin-left: 10px;
            }
            
            .social-share-buttons {
                flex-direction: column;
            }
            
            .social-btn {
                width: 100%;
            }
        }
        
        @media (max-width: 576px) {
            .tournament-details div {
                width: 100%;
            }
        
            .container-row {
                flex-direction: column;
            }
            
            .share-modal-content {
                margin: 5% auto;
                width: 95%;
            }
        }
    </style>
</head>
<body>
    <!-- Display success/error messages -->
    <?php if (isset($_GET['success_message'])): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($_GET['success_message']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_GET['error_message'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error_message']); ?></div>
    <?php endif; ?>
    
    <?php if (!empty($error_message)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>

    <div class="banner-cont">
        <div class="banner-container">
            <div class="banner-img-container">
                <div class="banner-img">
                    <?php if (!empty($bannerimg)): ?>
                        <img id="bannerimg" name="bannerimg" src="image.php?tournament_id=<?php echo urlencode($tournament_id); ?>" alt="Banner Image" class="banner-photo-img" />
                    <?php else: ?>
                        <p style="color: white; text-align: center;">No banner image available.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="tournament">
            <div class="tournament-operation">
                <div class="operation-btn">
                    <button class="organizer-actions">
                        <i class='fa fa-gears'></i> Organizer Actions
                        <ul class="dropdown-menu">
                            <li style="margin-bottom: 10px;">
                                <i class='fa fa-edit'></i>
                                <a href="edit_tour.php?id=<?php echo urlencode($tournament_id); ?>" 
                                style="text-decoration: none; color: #007bff;">
                                    Edit Tournament
                                </a>
                            </li>
                            <li style="margin-bottom: 10px;">
                                <a href="javascript:void(0);" style="text-decoration: none; color: #dc3545;" 
                                onclick="confirmDelete(<?= $tournament_id ?>)">
                                    <i class="fa fa-trash"></i> Delete Tournament
                                </a>
                            </li>
                        </ul>
                    </button>
                    <button onclick="startGame(<?php echo (int)$tournament_id; ?>)">
                        <i class='fa fa-play'></i> Start Game
                    </button>
                    <button class="options" onclick="shareTournament(<?php echo (int)$tournament_id; ?>)">
                        <i class='fa fa-share-alt'></i> Share
                    </button>

                    <div class="br-controls-dropdown">
                        <button class="br-btn" id="br-options-btn">
                            <i class="fas fa-cog"></i> Options
                        </button>
                        <div class="br-dropdown-content" id="br-dropdown">
                            <button class="br-btn-small" onclick="window.location.href='update_br_leaderboard.php?tournament_id=<?php echo $tournament_id; ?>'">
                                <i class="fas fa-chart-line"></i> Update Leaderboard
                            </button>
                            <button class="br-btn-small" onclick="updateBrackets(<?php echo $tournament_id; ?>)">
                                <i class="fas fa-project-diagram"></i> Update Brackets
                            </button>
                            <button class="br-btn-small" onclick="predictWinner(<?php echo $tournament_id; ?>)">
                                <i class="fas fa-crosshairs"></i> Predict Winner
                            </button>
                            <button class="br-btn-small" onclick="location.reload()">
                                <i class="fas fa-sync-alt"></i> Refresh
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="gameuser">
                <p class="titlename"><?php echo htmlspecialchars($selected_game); ?></p>
                Tournament By:
                <p class="titlename"><?php echo htmlspecialchars($creator_name); ?></p>
            </div>
            
            <p class="teams-registered"><?php echo $teams_registered_message; ?></p>
        </div>

        <div class="tournament-details" id="tournament-details">
            <div id="details" class="details active" onclick="showContent('details')">
                <span class="tour-title">Details</span>
            </div>

            <div id="rules" class="rules" onclick="showContent('rules')">
                <span class="tour-title">Rules</span>
            </div>

            <div id="prizes" class="prizes" onclick="showContent('prizes')">
                <span class="tour-title">Prizes</span>
            </div>

            <div id="schedule" class="schedule" onclick="showContent('schedule')">
                <span class="tour-title">Participants</span>
            </div>

            <div id="contact" class="contact" onclick="showContent('contact')">
                <span class="tour-title">Contact</span>
            </div>
        </div>

        <div class="container-row">
            <div class="content-container details-container active" id="details-container">
                <p class="content-title">Game Name</p>
                <p class="cont-title"><?php echo htmlspecialchars($selected_game); ?></p>

                <p class="content-title">Start Date</p>
                <p class="cont-title"><?php echo htmlspecialchars($sdate); ?></p>

                <p class="content-title">Start Time</p>
                <p class="cont-title"><?php echo htmlspecialchars($stime); ?></p>

                <p class="content-title">Bracket Type</p>
                <p class="cont-title"><?php echo htmlspecialchars($bracket_type); ?></p>

                <p class="content-title">Match Type</p>
                <p class="cont-title"><?php echo htmlspecialchars($match_type); ?></p>

                <p class="content-title">About Game</p>
                <p class="cont-title"><?php echo html_entity_decode($about); ?></p>
            </div>

            <div class="content-container rules-container" id="rules-container">
                <p class="content-title">Game Critical Rules</p>
                <p class="cont-title"><?php echo html_entity_decode($rules); ?></p>
            </div>

            <div class="content-container prizes-container" id="prizes-container">
                <p class="content-title">Prize Details</p>
                <p class="cont-title"><?php echo html_entity_decode($prizes); ?></p>
            </div>

            <div class="content-container schedule-container" id="schedule-container">
                <p class="content-title">Registered Teams</p>
                <p class="cont-title"> </p>
                <?php
                if (!empty($participants)) {
                    $counter = 1;
                    foreach ($participants as $participant) {
                        ?>
                        <div class="participant-item">
                            <div class="participant-header">
                                <!-- Team Logo -->
                                <div class="team-logo">
                                    <?php
                                    $logo_path = !empty($participant['logo_path']) && file_exists('uploads/' . htmlspecialchars($participant['logo_path']))
                                                ? 'uploads/' . htmlspecialchars($participant['logo_path'])
                                                : 'uploads/dash-logo.png';
                                    echo '<img src="' . $logo_path . '" alt="Team Logo">';
                                    ?>
                                </div>
                                <!-- Team Name -->
                                <span class="team-name"><?php echo $counter . ". " . htmlspecialchars($participant['team_name']); ?></span>
                                <!-- Remove Team Button -->
                                <button class="remove-btn" onclick="confirmRemove(<?php echo (int)$tournament_id; ?>, '<?php echo $match_type; ?>', '<?php echo addslashes($participant['team_name']); ?>')">
                                    <i class="fa fa-trash"></i> Remove Team
                                </button>
                            </div>

                            <!-- Players Row -->
                            <?php if (!empty($participant['players']) && count($participant['players']) > 0 && $participant['players'][0] !== ''): ?>
                                <div class="players-list">
                                    <?php foreach ($participant['players'] as $player): ?>
                                        <span class="player-tag"><?php echo htmlspecialchars($player); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php
                        $counter++;
                    }
                } else {
                    echo "<p style='color: white; text-align: center;'>No participants registered yet.</p>";
                }
                ?>
            </div>

            <div class="content-container contact-container" id="contact-container">
                <p class="content-title">Contact Info</p>
                <p class="cont-title"><?php echo htmlspecialchars($social_media_input); ?></p>
            </div>
        </div>
        
        <!-- Hidden form for tournament deletion -->
        <form id="deleteTournamentForm" method="POST" style="display: none;">
            <input type="hidden" name="delete_tournament" value="yes">
            <input type="hidden" name="tournament_id" id="deleteTournamentId" value="">
        </form>
        
        <!-- Share Modal -->
        <div id="shareModal" class="share-modal">
            <div class="share-modal-content">
                <div class="modal-header">
                    <h3 style="margin: 0;"><i class="fa fa-share-alt"></i> Share Tournament</h3>
                    <span class="close-modal" onclick="closeShareModal()">&times;</span>
                </div>
                
                <div class="share-options">
                    <!-- Share URL -->
                    <div class="share-url-container">
                        <p style="margin-bottom: 10px; font-weight: bold;">Share Link:</p>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="shareUrl" readonly>
                            <button class="copy-btn" onclick="copyShareUrl()">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                    
                    <!-- Social Media Sharing -->
                    <div class="social-share">
                        <p style="margin-bottom: 10px; font-weight: bold;">Share on Social Media:</p>
                        <div class="social-share-buttons">
                            <button class="social-btn fb-btn" onclick="shareOnFacebook()">
                                <i class="fab fa-facebook-f"></i> Facebook
                            </button>
                            <button class="social-btn twitter-btn" onclick="shareOnTwitter()">
                                <i class="fab fa-twitter"></i> Twitter
                            </button>
                            <button class="social-btn whatsapp-btn" onclick="shareOnWhatsApp()">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </button>
                            <button class="social-btn email-btn" onclick="shareViaEmail()">
                                <i class="fa fa-envelope"></i> Email
                            </button>
                        </div>
                    </div>
                    
                    <!-- QR Code -->
                    <div class="qr-code-container">
                        <p style="margin-bottom: 10px; font-weight: bold;">QR Code:</p>
                        <div id="qrCodeContainer">
                            <!-- QR Code will be generated here -->
                            <div style="width: 128px; height: 128px; display: flex; align-items: center; justify-content: center; background: white;">
                                <span style="color: black; font-size: 12px; text-align: center;">
                                    Scan to share
                                </span>
                            </div>
                        </div>
                        <p style="margin-top: 10px; font-size: 12px; color: #aaa;">Scan to share the tournament</p>
                    </div>
                    
                    <!-- Embed Code -->
                    <div class="embed-container">
                        <p style="margin-bottom: 10px; font-weight: bold;">Embed Code:</p>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="embedCode" readonly style="font-family: monospace; font-size: 12px;">
                            <button class="embed-btn" onclick="copyEmbedCode()">
                                <i class="fa fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button class="close-btn" onclick="closeShareModal()">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Function to show content sections
    function showContent(section) {
        // Hide all content containers
        document.querySelectorAll('.content-container').forEach(container => {
            container.classList.remove('active');
        });
        
        // Remove active class from all navigation items
        document.querySelectorAll('.tournament-details div').forEach(item => {
            item.classList.remove('active');
        });
        
        // Show selected content container
        document.getElementById(section + '-container').classList.add('active');
        
        // Add active class to selected navigation item
        document.getElementById(section).classList.add('active');
    }
    
    // Function to confirm tournament deletion
    function confirmDelete(tournamentId) {
        if (confirm("⚠️ Are you sure you want to delete this tournament?\n\nThis action will permanently delete:\n• All tournament data\n• All registered teams/players\n• All brackets and matches\n• All leaderboard data\n\nThis action cannot be undone!")) {
            document.getElementById('deleteTournamentId').value = tournamentId;
            document.getElementById('deleteTournamentForm').submit();
        }
    }
    
    // Function to confirm team removal
    function confirmRemove(tournamentId, matchType, teamName) {
        if (confirm(`Are you sure you want to remove "${teamName}" from the tournament?\n\nThis will remove the team and all its players.`)) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const tournamentIdInput = document.createElement('input');
            tournamentIdInput.type = 'hidden';
            tournamentIdInput.name = 'tournament_id';
            tournamentIdInput.value = tournamentId;
            
            const matchTypeInput = document.createElement('input');
            matchTypeInput.type = 'hidden';
            matchTypeInput.name = 'match_type';
            matchTypeInput.value = matchType;
            
            const teamNameInput = document.createElement('input');
            teamNameInput.type = 'hidden';
            teamNameInput.name = 'team_name';
            teamNameInput.value = teamName;
            
            const removeInput = document.createElement('input');
            removeInput.type = 'hidden';
            removeInput.name = 'remove';
            removeInput.value = 'yes';
            
            form.appendChild(tournamentIdInput);
            form.appendChild(matchTypeInput);
            form.appendChild(teamNameInput);
            form.appendChild(removeInput);
            
            document.body.appendChild(form);
            form.submit();
        }
    }
    
    // Start Game Function
    function startGame(tournamentId) {
        // Redirect to start_game.php page
        window.location.href = 'start_game.php?tournament_id=' + tournamentId;
    }
    
    // Share Tournament Function
    function shareTournament(tournamentId) {
        const tournamentName = document.querySelector('.titlename').innerText;
        const shareUrl = window.location.origin + '/tournament_details.php?tournament_id=' + tournamentId;
        const shareText = `Check out "${tournamentName}" tournament! Join now: ${shareUrl}`;
        
        // Set share URL
        document.getElementById('shareUrl').value = shareUrl;
        
        // Set embed code
        const embedCode = `<iframe src="${shareUrl}" width="100%" height="500" frameborder="0" style="border-radius: 8px;"></iframe>`;
        document.getElementById('embedCode').value = embedCode;
        
        // Generate simple QR code (text representation)
        generateSimpleQRCode(shareUrl);
        
        // Show modal
        document.getElementById('shareModal').style.display = 'block';
    }
    
    // Generate Simple QR Code (text representation)
    function generateSimpleQRCode(text) {
        const container = document.getElementById('qrCodeContainer');
        container.innerHTML = `
            <div style="width: 128px; height: 128px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: white; padding: 10px; border-radius: 5px;">
                <div style="font-size: 20px; color: black; margin-bottom: 5px;">QR</div>
                <div style="font-size: 10px; color: black; text-align: center;">Scan with phone camera</div>
                <div style="font-size: 8px; color: #666; margin-top: 5px; text-align: center;">Tournament Link</div>
            </div>
        `;
    }
    
    // Close Share Modal
    function closeShareModal() {
        document.getElementById('shareModal').style.display = 'none';
    }
    
    // Copy Share URL to Clipboard
    function copyShareUrl() {
        const shareUrl = document.getElementById('shareUrl');
        shareUrl.select();
        shareUrl.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            // Try using the modern Clipboard API first
            navigator.clipboard.writeText(shareUrl.value)
                .then(() => {
                    showCopyFeedback(event.target, 'Copied!', '#28a745');
                })
                .catch(() => {
                    // Fallback for older browsers
                    document.execCommand('copy');
                    showCopyFeedback(event.target, 'Copied!', '#28a745');
                });
        } catch (err) {
            // Fallback for older browsers
            document.execCommand('copy');
            showCopyFeedback(event.target, 'Copied!', '#28a745');
        }
    }
    
    // Copy Embed Code to Clipboard
    function copyEmbedCode() {
        const embedCode = document.getElementById('embedCode');
        embedCode.select();
        embedCode.setSelectionRange(0, 99999);
        
        try {
            // Try using the modern Clipboard API first
            navigator.clipboard.writeText(embedCode.value)
                .then(() => {
                    showCopyFeedback(event.target, 'Copied!', '#28a745');
                })
                .catch(() => {
                    // Fallback for older browsers
                    document.execCommand('copy');
                    showCopyFeedback(event.target, 'Copied!', '#28a745');
                });
        } catch (err) {
            // Fallback for older browsers
            document.execCommand('copy');
            showCopyFeedback(event.target, 'Copied!', '#28a745');
        }
    }
    
    // Show copy feedback
    function showCopyFeedback(button, text, color) {
        const originalHTML = button.innerHTML;
        const originalColor = button.style.backgroundColor;
        
        button.innerHTML = `<i class="fa fa-check"></i> ${text}`;
        button.style.backgroundColor = color;
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.backgroundColor = originalColor;
        }, 2000);
    }
    
    // Social Media Sharing Functions
    function shareOnFacebook() {
        const shareUrl = document.getElementById('shareUrl').value;
        const tournamentName = document.querySelector('.titlename').innerText;
        const url = `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}&quote=${encodeURIComponent(`Join "${tournamentName}" tournament!`)}`;
        window.open(url, '_blank', 'width=600,height=400');
    }
    
    function shareOnTwitter() {
        const shareUrl = document.getElementById('shareUrl').value;
        const tournamentName = document.querySelector('.titlename').innerText;
        const text = `Join "${tournamentName}" tournament!`;
        const url = `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}&text=${encodeURIComponent(text)}`;
        window.open(url, '_blank', 'width=600,height=400');
    }
    
    function shareOnWhatsApp() {
        const shareUrl = document.getElementById('shareUrl').value;
        const tournamentName = document.querySelector('.titlename').innerText;
        const text = `Join "${tournamentName}" tournament! ${shareUrl}`;
        const url = `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
        window.open(url, '_blank', 'width=600,height=400');
    }
    
    function shareViaEmail() {
        const shareUrl = document.getElementById('shareUrl').value;
        const tournamentName = document.querySelector('.titlename').innerText;
        const subject = `Join "${tournamentName}" Tournament`;
        const body = `You're invited to join "${tournamentName}" tournament!\n\nTournament Details: ${shareUrl}\n\nJoin now to participate!`;
        const url = `mailto:?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
        window.location.href = url;
    }
    
    // Dropdown functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropdownBtn = document.getElementById('br-options-btn');
        const dropdownContainer = dropdownBtn.parentElement;
        
        // Toggle dropdown
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownContainer.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        window.addEventListener('click', function() {
            dropdownContainer.classList.remove('show');
        });
        
        // Close dropdown when clicking on a dropdown item
        document.querySelectorAll('.br-btn-small').forEach(item => {
            item.addEventListener('click', function() {
                dropdownContainer.classList.remove('show');
            });
        });
        
        // Set initial active section
        showContent('details');
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('shareModal');
            if (event.target == modal) {
                closeShareModal();
            }
        }
    });
    
    // Placeholder functions for bracket operations
    function updateBrackets(tournamentId) {
        // Redirect to update brackets page
        window.location.href = 'update_brackets.php?tournament_id=' + tournamentId;
    }
    
    function predictWinner(tournamentId) {
        // Redirect to organizer prediction endpoint
        window.location.href = 'predict_winner.php?tournament_id=' + tournamentId;
    }
    </script>
</body>
</html>

<?php include('footer.php'); ?>
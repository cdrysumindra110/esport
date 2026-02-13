<?php
// index.php
// INFIKNIGHT AI Prediction System v2.0 - Complete OCR Integration
// Handles SOLO, DUO, SQUAD matches with OCR.space API

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include required files
// require_once 'includes/ocr_config.php';
// require_once 'includes/ocr_processor.php';
// require_once 'includes/team_predictor.php';
require_once '../prediction/includes/ocr_config.php';
require_once '../prediction/includes/ocr_processor.php';
require_once '../prediction/includes/team_predictor.php';

use InfiKnight\OCR\OCRProcessor;
use InfiKnight\Predictor\TeamPredictor;

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;

// Check if this is an AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

// Handle AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
    
    try {
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'process_solo':
                handleSoloOCR();
                break;
            case 'process_duo':
                handleTeamOCR('duo');
                break;
            case 'process_squad':
                handleTeamOCR('squad');
                break;
            case 'predict':
                handlePrediction();
                break;
            case 'reset':
                handleReset();
                break;
            default:
                echo json_encode(['success' => false, 'error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

/**
 * Handle SOLO OCR Processing
 */
function handleSoloOCR() {
    if (!isset($_FILES['player_images'])) {
        throw new Exception('No player images uploaded');
    }
    
    $predictor = new TeamPredictor(true);
    $predictor->setMatchType('solo');
    
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedFiles = [];
    $files = $_FILES['player_images'];
    $playerNames = $_POST['player_names'] ?? [];
    
    // Save uploaded files
    if (is_array($files['name'])) {
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid('player_') . '_' . date('Ymd_His') . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$index], $filepath)) {
                    $uploadedFiles[] = $filepath;
                }
            }
        }
    } else {
        if ($files['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($files['name'], PATHINFO_EXTENSION);
            $filename = uniqid('player_') . '_' . date('Ymd_His') . '.' . $ext;
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($files['tmp_name'], $filepath)) {
                $uploadedFiles[] = $filepath;
            }
        }
    }
    
    if (empty($uploadedFiles)) {
        throw new Exception('Failed to upload files');
    }
    
    // Process solo match
    $result = $predictor->processSoloMatch($uploadedFiles, $playerNames);
    
    // Store in session
    $_SESSION['last_prediction'] = [
        'players' => $result['players'],
        'match_type' => 'solo'
    ];
    
    // Cleanup files
    foreach ($uploadedFiles as $file) {
        if (file_exists($file)) unlink($file);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'session_id' => $predictor->sessionId
    ]);
}

/**
 * Handle Team OCR Processing (Duo/Squad)
 */
function handleTeamOCR($matchType) {
    if (!isset($_FILES['player_images'])) {
        throw new Exception('No player images uploaded');
    }
    
    $predictor = new TeamPredictor(true);
    $predictor->setMatchType($matchType);
    
    $uploadDir = __DIR__ . '/uploads/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    $uploadedFiles = [];
    $files = $_FILES['player_images'];
    $playerNames = $_POST['player_names'] ?? [];
    
    // Validate minimum players
    $minPlayers = $matchType === 'duo' ? 4 : 8;
    
    // Save uploaded files
    if (is_array($files['name'])) {
        if (count($files['name']) < $minPlayers) {
            throw new Exception("$matchType match requires at least $minPlayers players");
        }
        
        foreach ($files['name'] as $index => $name) {
            if ($files['error'][$index] === UPLOAD_ERR_OK) {
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $filename = uniqid('player_') . '_' . date('Ymd_His') . '.' . $ext;
                $filepath = $uploadDir . $filename;
                
                if (move_uploaded_file($files['tmp_name'][$index], $filepath)) {
                    $uploadedFiles[] = $filepath;
                }
            }
        }
    }
    
    if (count($uploadedFiles) < $minPlayers) {
        throw new Exception("Only " . count($uploadedFiles) . " files uploaded. Need $minPlayers");
    }
    
    // Process team match
    $result = $predictor->processTeamMatch($uploadedFiles, $playerNames, $matchType);
    
    // Store in session
    $_SESSION['last_prediction'] = [
        'players' => $result['players'],
        'teams' => $result['teams'],
        'match_type' => $matchType
    ];
    
    // Cleanup files
    foreach ($uploadedFiles as $file) {
        if (file_exists($file)) unlink($file);
    }
    
    echo json_encode([
        'success' => true,
        'data' => $result,
        'session_id' => $predictor->sessionId
    ]);
}

/**
 * Handle Prediction Request
 */
function handlePrediction() {
    if (!isset($_SESSION['last_prediction'])) {
        throw new Exception('No players processed. Please upload images first.');
    }
    
    $session = $_SESSION['last_prediction'];
    $predictor = new TeamPredictor(true);
    $predictor->setMatchType($session['match_type']);
    
    // Rebuild players array
    foreach ($session['players'] as $player) {
        if (isset($player['success']) && $player['success'] === false) continue;
        
        $predictor->players[] = $player;
    }
    
    // For team matches, rebuild teams
    if ($session['match_type'] !== 'solo' && isset($session['teams'])) {
        $predictor->teams = $session['teams'];
    } else if ($session['match_type'] !== 'solo') {
        // Auto-form teams if not already formed
        $playersPerTeam = $predictor->getPlayersPerTeam();
        $teams = $predictor->autoAssignTeams($predictor->getPlayers(), $playersPerTeam);
        $predictor->teams = $teams;
    }
    
    // Get prediction
    $prediction = $predictor->predictWinner();
    
    echo json_encode([
        'success' => true,
        'prediction' => $prediction
    ]);
}

/**
 * Handle Reset Request
 */
function handleReset() {
    unset($_SESSION['last_prediction']);
    echo json_encode(['success' => true]);
}

?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INFIKNIGHT AI Prediction System v2.0 - OCR.space Integration</title>
    
    <!-- External CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* ===== CSS VARIABLES - Professional Color Scheme ===== */
        :root {
            --primary: #4361ee;
            --secondary: #7209b7;
            --success: #06d6a0;
            --warning: #ffd60a;
            --danger: #ef476f;
            --dark: #0f172a;
            --light: #f8fafc;
            --gray: #64748b;
            --gray-light: #e2e8f0;
            --border: #cbd5e1;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
            --radius-sm: 8px;
            --radius-md: 12px;
            --radius-lg: 16px;
            --space-xs: 4px;
            --space-sm: 8px;
            --space-md: 16px;
            --space-lg: 24px;
            --space-xl: 32px;
        }

        /* ===== RESET & BASE STYLES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7ff 0%, #ffffff 100%);
            color: var(--dark);
            line-height: 1.6;
            min-height: 100vh;
        }

        /* ===== HEADER SECTION ===== */
        .infiknight-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: var(--space-lg);
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: var(--space-xl);
            border-radius: var(--radius-lg);
            margin-bottom: var(--space-xl);
            box-shadow: var(--shadow-xl);
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            filter: blur(40px);
        }

        .hero-content {
            position: relative;
            z-index: 2;
        }

        .hero-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            letter-spacing: -1px;
        }

        .hero-title i {
            font-size: 2.5rem;
        }

        .hero-subtitle {
            font-size: 1.1rem;
            opacity: 0.95;
            margin-bottom: var(--space-md);
            font-weight: 500;
        }

        .version-badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.2);
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
            margin-bottom: var(--space-md);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .game-badges {
            display: flex;
            gap: var(--space-sm);
            flex-wrap: wrap;
        }

        .badge {
            padding: 8px 20px;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            box-shadow: var(--shadow-md);
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .bg-pubg { background: #2ecc71; color: white; }
        .bg-freefire { background: #e74c3c; color: white; }
        .bg-cod { background: #3498db; color: white; }
        .bg-apex { background: #e91e63; color: white; }

        /* ===== MAIN LAYOUT ===== */
        .main-layout {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: var(--space-xl);
        }

        @media (max-width: 1024px) {
            .main-layout {
                grid-template-columns: 1fr;
            }
        }

        /* ===== SIDEBAR CARDS ===== */
        .sidebar-card {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid var(--border);
            margin-bottom: var(--space-lg);
        }

        .sidebar-card:hover {
            box-shadow: var(--shadow-xl);
            transform: translateY(-2px);
        }

        .card-title {
            font-size: 1.1rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: var(--space-sm);
            color: white;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: var(--space-md) var(--space-lg);
            margin: 0;
        }

        .card-title i {
            font-size: 1.2rem;
        }

        /* ===== INPUT METHOD SELECTOR ===== */
        .input-method-selector {
            padding: var(--space-lg);
        }

        .method-tabs {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            margin-bottom: var(--space-lg);
            background: var(--light);
            padding: 6px;
            border-radius: var(--radius-md);
        }

        .method-tab {
            padding: var(--space-md);
            background: white;
            border: 2px solid transparent;
            border-radius: var(--radius-sm);
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--gray);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .method-tab i {
            font-size: 1.3rem;
        }

        .method-tab.active {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .method-content {
            display: none;
            animation: fadeIn 0.4s ease;
        }

        .method-content.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ===== UPLOAD ZONE ===== */
        .upload-zone {
            border: 3px dashed var(--border);
            border-radius: var(--radius-md);
            padding: var(--space-xl);
            text-align: center;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-zone:hover,
        .upload-zone.highlight {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.1));
            transform: scale(1.01);
        }

        .upload-icon {
            font-size: 3.5rem;
            color: var(--primary);
            margin-bottom: var(--space-md);
        }

        .upload-zone h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: 8px;
        }

        .upload-hint {
            color: var(--gray);
            font-size: 0.9rem;
            margin-bottom: var(--space-md);
        }

        .ocr-notice {
            margin-top: var(--space-md);
            padding: var(--space-sm);
            background: rgba(6, 214, 160, 0.1);
            border-radius: var(--radius-sm);
            font-size: 0.85rem;
            color: var(--success);
            font-weight: 600;
        }

        .ocr-notice i {
            margin-right: 6px;
        }

        /* ===== OCR PREVIEW ===== */
        .ocr-preview {
            margin-top: var(--space-lg);
            padding: var(--space-lg);
            background: var(--light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--success);
        }

        .ocr-header {
            margin-bottom: var(--space-lg);
            padding-bottom: var(--space-md);
            border-bottom: 2px solid var(--border);
        }

        .ocr-header h4 {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark);
            margin-bottom: var(--space-sm);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .ocr-header h4 i {
            color: var(--success);
            font-size: 1.3rem;
        }

        .ocr-meta {
            display: flex;
            gap: var(--space-md);
            flex-wrap: wrap;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.85rem;
            color: var(--gray);
            background: white;
            padding: 6px 14px;
            border-radius: 20px;
            box-shadow: var(--shadow-sm);
        }

        /* ===== CONFIDENCE DISPLAY ===== */
        .confidence-display {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            margin-bottom: var(--space-lg);
            border-left: 4px solid var(--primary);
        }

        .confidence-label {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--gray);
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .confidence-value {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: var(--space-sm);
        }

        .confidence-value.high { color: var(--success); }
        .confidence-value.medium { color: var(--warning); }
        .confidence-value.low { color: var(--danger); }

        .confidence-bar {
            height: 8px;
            background: var(--gray-light);
            border-radius: 4px;
            overflow: hidden;
        }

        .confidence-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        /* ===== STAT CARDS ===== */
        .career-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
        }

        .stat-card {
            background: white;
            padding: var(--space-md);
            border-radius: var(--radius-md);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: var(--space-md);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            border-radius: var(--radius-sm);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: var(--primary);
            flex-shrink: 0;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--gray);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .stat-value {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--dark);
        }

        /* ===== TEAM PERFORMANCE CARDS ===== */
        .team-performance-card {
            padding: var(--space-lg);
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
        }

        .team-performance-card.medal-gold {
            background: linear-gradient(135deg, #fff8db 0%, #ffe28a 100%);
            border: 2px solid #f2c94c;
            color: #5c3b00;
        }

        .team-performance-card.medal-silver {
            background: linear-gradient(135deg, #f6f7fb 0%, #dfe4ee 100%);
            border: 2px solid #bfc7d5;
            color: #2f3a45;
        }

        .team-performance-card.medal-bronze {
            background: linear-gradient(135deg, #ffe6d2 0%, #f6b38a 100%);
            border: 2px solid #d08b5b;
            color: #5a2d00;
        }

        .team-rank {
            font-size: 2.2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .team-name {
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: var(--space-sm);
        }

        /* ===== FORM ELEMENTS ===== */
        .form-group {
            margin-bottom: var(--space-md);
        }

        .form-group label {
            display: block;
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--dark);
            margin-bottom: var(--space-xs);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            font-size: 0.95rem;
            font-weight: 500;
            background: white;
            color: var(--dark);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        }

        /* ===== MATCH TYPE SELECTOR ===== */
        .match-type-selector {
            margin-bottom: var(--space-lg);
        }

        .match-type-selector h3 {
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: var(--dark);
            font-size: 0.95rem;
        }

        .match-options {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: var(--space-sm);
        }

        .match-option input {
            display: none;
        }

        .match-option .option-content {
            padding: var(--space-md);
            border: 2px solid var(--border);
            border-radius: var(--radius-sm);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .match-option input:checked + .option-content {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .match-option .option-content i {
            font-size: 1.5rem;
            color: var(--primary);
        }

        /* ===== TEAM CONFIGURATION ===== */
        .team-config-section {
            margin-bottom: var(--space-lg);
            padding: var(--space-md);
            background: var(--light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--primary);
        }

        .team-config-section h3 {
            font-weight: 700;
            margin-bottom: var(--space-md);
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: var(--space-sm);
        }

        .team-controls {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: var(--space-sm);
            align-items: end;
        }

        /* ===== TEAM INPUTS ===== */
        .team-section {
            margin-bottom: var(--space-lg);
            padding: var(--space-lg);
            background: linear-gradient(to bottom, white 0%, #f8fafc 100%);
            border: 2px solid var(--border);
            border-radius: var(--radius-md);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .team-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .team-players-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: var(--space-md);
            margin-top: var(--space-md);
        }

        @media (max-width: 768px) {
            .team-players-grid {
                grid-template-columns: 1fr;
            }
        }

        /* ===== BUTTONS ===== */
        .btn {
            padding: 12px 24px;
            border-radius: var(--radius-sm);
            border: none;
            font-weight: 600;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(67, 97, 238, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 4px 12px rgba(6, 214, 160, 0.3);
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(6, 214, 160, 0.4);
        }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #d62828);
            color: white;
        }

        .btn-lg {
            padding: 16px 32px;
            font-size: 1.1rem;
            width: 100%;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* ===== PROCESSING SECTION ===== */
        .processing-section {
            padding: var(--space-xl);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .processing-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .processing-time {
            font-size: 1.3rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .step {
            display: grid;
            grid-template-columns: 50px 1fr 50px;
            gap: var(--space-md);
            align-items: center;
            padding: var(--space-md);
            margin-bottom: var(--space-md);
            background: var(--light);
            border-radius: var(--radius-md);
            border-left: 4px solid var(--border);
            transition: all 0.3s ease;
        }

        .step.active {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.1), rgba(114, 9, 183, 0.05));
            border-left-color: var(--primary);
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: var(--radius-sm);
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: var(--primary);
            box-shadow: var(--shadow-sm);
        }

        .step.active .step-status i {
            color: var(--success);
        }

        /* ===== PROGRESS BAR ===== */
        .progress-container {
            height: 8px;
            background: var(--gray-light);
            border-radius: 4px;
            overflow: hidden;
            margin: var(--space-lg) 0;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        /* ===== RESULTS SECTION ===== */
        .results-section {
            display: flex;
            flex-direction: column;
            gap: var(--space-lg);
        }

        .results-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: var(--space-lg);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .confidence-badge {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: var(--space-md) var(--space-lg);
            border-radius: var(--radius-md);
            text-align: center;
            min-width: 140px;
            box-shadow: var(--shadow-lg);
        }

        .confidence-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        /* ===== WINNER CARD ===== */
        .winner-section {
            padding: var(--space-xl);
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .winner-card {
            background: linear-gradient(135deg, rgba(67, 97, 238, 0.05), rgba(114, 9, 183, 0.05));
            border: 2px solid var(--primary);
            border-radius: var(--radius-lg);
            padding: var(--space-xl);
            display: grid;
            grid-template-columns: 100px 1fr;
            gap: var(--space-xl);
            align-items: center;
            margin-bottom: var(--space-lg);
        }

        .winner-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: var(--radius-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: var(--shadow-lg);
        }

        .winner-info h4 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dark);
            margin-bottom: var(--space-md);
        }

        .winner-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: var(--space-md);
        }

        /* ===== PERFORMANCE GRID ===== */
        .performance-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: var(--space-lg);
        }

        .performance-card {
            background: white;
            border: 2px solid var(--border);
            border-radius: var(--radius-lg);
            padding: var(--space-lg);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .performance-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }

        .performance-card:hover {
            border-color: var(--primary);
            box-shadow: var(--shadow-xl);
            transform: translateY(-4px);
        }

        /* ===== ERROR SECTION ===== */
        .error-section {
            padding: var(--space-xl);
            background: linear-gradient(135deg, rgba(239, 71, 111, 0.05), rgba(214, 40, 40, 0.05));
            border-left: 4px solid var(--danger);
            border-radius: var(--radius-lg);
        }

        .error-header {
            display: flex;
            align-items: center;
            gap: var(--space-md);
            margin-bottom: var(--space-lg);
            color: var(--danger);
        }

        .error-header i {
            font-size: 1.5rem;
        }

        .error-header h3 {
            color: var(--danger);
        }

        /* ===== NEW PREDICTION SECTION ===== */
        .new-prediction-section {
            text-align: center;
            padding: var(--space-xl);
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border-radius: var(--radius-lg);
        }

        /* ===== UTILITY CLASSES ===== */
        .hidden {
            display: none !important;
        }

        .fade-in {
            animation: fadeIn 0.5s ease;
        }

        /* ===== NOTIFICATIONS ===== */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 16px 24px;
            border-radius: var(--radius-md);
            color: white;
            font-weight: 600;
            box-shadow: var(--shadow-xl);
            z-index: 9999;
            opacity: 0;
            transform: translateX(400px);
            transition: all 0.3s ease;
            max-width: 400px;
        }

        .notification.show {
            opacity: 1;
            transform: translateX(0);
        }

        .notification-success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .notification-error {
            background: linear-gradient(135deg, var(--danger), #d62828);
        }

        .notification-info {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
        }

        .notification-content {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ===== THEME TOGGLE ===== */
        .theme-toggle {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }

        .toggle-btn {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            cursor: pointer;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow-lg);
            transition: all 0.3s ease;
        }

        .toggle-btn:hover {
            transform: scale(1.1);
            box-shadow: var(--shadow-xl);
        }

        /* ===== DARK THEME ===== */
        [data-theme="dark"] {
            --dark: #f8fafc;
            --light: #1e293b;
            --border: #334155;
            --gray: #94a3b8;
            --gray-light: #1e293b;
            background: #0f172a;
            color: #f8fafc;
        }

        [data-theme="dark"] body {
            background: #0f172a;
        }

        [data-theme="dark"] .sidebar-card,
        [data-theme="dark"] .processing-section,
        [data-theme="dark"] .results-header,
        [data-theme="dark"] .winner-section,
        [data-theme="dark"] .performance-card,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .form-control,
        [data-theme="dark"] .match-option .option-content {
            background: #1e293b;
            border-color: #334155;
            color: #f8fafc;
        }

        [data-theme="dark"] .form-control {
            background: #0f172a;
            color: #f8fafc;
        }

        [data-theme="dark"] .step {
            background: #0f172a;
        }

        /* ===== RESPONSIVE DESIGN ===== */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 1.8rem;
            }
            
            .winner-card {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .winner-icon {
                margin: 0 auto;
            }
            
            .method-tabs {
                grid-template-columns: 1fr;
            }
            
            .team-controls {
                grid-template-columns: 1fr;
            }
            
            .career-stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle">
        <button class="toggle-btn" id="themeToggle">
            <i class="fas fa-moon"></i>
        </button>
    </div>

    <div class="infiknight-container">
        <!-- Header Section -->
        <header class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">
                    <i class="fas fa-robot"></i> INFIKNIGHT AI
                </h1>
                <p class="hero-subtitle">Advanced Battle Royale Winner Prediction with OCR.space Technology</p>
                <div class="version-badge">
                    <i class="fas fa-crown"></i> v2.0 - OCR.space Integrated
                </div>
                <div class="game-badges">
                    <span class="badge bg-pubg"><i class="fas fa-crosshairs"></i> PUBG</span>
                    <span class="badge bg-freefire"><i class="fas fa-fire"></i> Free Fire</span>
                    <span class="badge bg-cod"><i class="fas fa-skull"></i> Call of Duty</span>
                    <span class="badge bg-apex"><i class="fas fa-bolt"></i> Apex Legends</span>
                </div>
            </div>
        </header>

        <!-- Main Layout -->
        <div class="main-layout">
            <!-- Left Column - Input Section -->
            <div class="left-column">
                <div class="sidebar-card">
                    <h2 class="card-title">
                        <i class="fas fa-upload"></i> Import Player Stats
                    </h2>
                    
                    <!-- Input Method Selector -->
                    <div class="input-method-selector">
                        <div class="method-tabs">
                            <button class="method-tab active" data-method="upload">
                                <i class="fas fa-file-upload"></i>
                                <span>OCR Upload</span>
                            </button>
                            <button class="method-tab" data-method="manual">
                                <i class="fas fa-keyboard"></i>
                                <span>Manual Entry</span>
                            </button>
                            <button class="method-tab" data-method="api">
                                <i class="fas fa-plug"></i>
                                <span>API Import</span>
                            </button>
                        </div>
                        
                        <!-- OCR Upload Method -->
                        <div class="method-content active" id="uploadMethod">
                            <div class="match-type-selector">
                                <h3><i class="fas fa-gamepad"></i> Select Match Type</h3>
                                <div class="match-options">
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="solo" checked>
                                        <span class="option-content">
                                            <i class="fas fa-user"></i>
                                            <span>Solo</span>
                                            <small>1 image = 1 player</small>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="duo">
                                        <span class="option-content">
                                            <i class="fas fa-user-friends"></i>
                                            <span>Duo</span>
                                            <small>2 players/team</small>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchType" value="squad">
                                        <span class="option-content">
                                            <i class="fas fa-users"></i>
                                            <span>Squad</span>
                                            <small>4 players/team</small>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Upload Zone -->
                            <div class="upload-zone" id="uploadZone">
                                <div class="upload-icon">
                                    <i class="fas fa-cloud-upload-alt"></i>
                                </div>
                                <h3>Drop Player Screenshots Here</h3>
                                <p class="upload-hint">
                                    <i class="fas fa-info-circle"></i> 
                                    Each image = 1 player. Upload multiple images for multiple players.
                                </p>
                                <button class="btn btn-primary" id="browseBtn" type="button">
                                    <i class="fas fa-folder-open"></i> Select Images
                                </button>
                                <input type="file" id="statsFile" multiple 
                                       accept=".png,.jpg,.jpeg" style="display: none;">
                                <div class="ocr-notice">
                                    <i class="fas fa-robot"></i> 
                                    Powered by OCR.space - 95% accuracy rate
                                </div>
                            </div>
                            
                            <!-- OCR Preview Container -->
                            <div id="ocrPreview" class="ocr-preview hidden"></div>
                            
                            <!-- File Preview Container -->
                            <div id="filePreview" class="hidden" style="margin-top: 20px;">
                                <h4 style="font-size: 0.9rem; color: var(--gray); margin-bottom: 10px;">
                                    <i class="fas fa-images"></i> Selected Files:
                                </h4>
                                <div id="fileList" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;"></div>
                                <div style="margin-top: 15px; padding: 10px; background: var(--light); border-radius: var(--radius-sm);">
                                    <span id="fileCount" style="font-weight: 600;">0</span> files selected
                                </div>
                            </div>
                        </div>
                        
                        <!-- Manual Input Method -->
                        <div class="method-content" id="manualMethod">
                            <div class="match-type-selector">
                                <h3><i class="fas fa-gamepad"></i> Select Match Type</h3>
                                <div class="match-options">
                                    <label class="match-option">
                                        <input type="radio" name="matchTypeManual" value="solo" checked>
                                        <span class="option-content">
                                            <i class="fas fa-user"></i>
                                            <span>Solo</span>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchTypeManual" value="duo">
                                        <span class="option-content">
                                            <i class="fas fa-user-friends"></i>
                                            <span>Duo</span>
                                        </span>
                                    </label>
                                    <label class="match-option">
                                        <input type="radio" name="matchTypeManual" value="squad">
                                        <span class="option-content">
                                            <i class="fas fa-users"></i>
                                            <span>Squad</span>
                                        </span>
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Team Configuration -->
                            <div class="team-config-section" id="teamConfigManual">
                                <h3><i class="fas fa-users-cog"></i> Team Configuration</h3>
                                <div class="team-controls">
                                    <div class="form-group">
                                        <label>Number of Teams:</label>
                                        <select id="teamCountManual" class="form-control">
                                            <option value="2">2 Teams</option>
                                            <option value="3">3 Teams</option>
                                            <option value="4">4 Teams</option>
                                            <option value="5">5 Teams</option>
                                            <option value="6">6 Teams</option>
                                        </select>
                                    </div>
                                    <button class="btn btn-secondary" id="generateTeamsManual" type="button">
                                        <i class="fas fa-plus-circle"></i> Generate
                                    </button>
                                </div>
                                <div class="team-inputs-container">
                                    <div id="teamInputsManual"></div>
                                </div>
                            </div>
                            
                            <!-- Player Inputs (Solo) -->
                            <div id="playerInputsManual">
                                <div class="player-input-section">
                                    <h4><i class="fas fa-user-circle"></i> Player 1</h4>
                                    <div class="input-grid">
                                        <div class="input-group">
                                            <label>Name</label>
                                            <input type="text" class="player-name" placeholder="Player name" value="Player 1">
                                        </div>
                                        <div class="input-group">
                                            <label>Kills</label>
                                            <input type="number" class="player-kills" min="0" value="5">
                                        </div>
                                        <div class="input-group">
                                            <label>Damage</label>
                                            <input type="number" class="player-damage" min="0" value="250">
                                        </div>
                                        <div class="input-group">
                                            <label>Survival</label>
                                            <div class="survival-time-wrapper">
                                                <input type="number" class="player-survival-min" min="0" value="7">
                                                <span>:</span>
                                                <input type="number" class="player-survival-sec" min="0" max="59" value="30">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button class="btn btn-secondary" id="addPlayerBtnManual" type="button" style="margin-top: 10px;">
                                <i class="fas fa-plus"></i> Add Player
                            </button>
                            
                            <button class="btn btn-primary" id="processManualBtn" type="button" style="margin-top: 20px; width: 100%;">
                                <i class="fas fa-calculator"></i> Calculate Prediction
                            </button>
                        </div>
                        
                        <!-- API Import Method -->
                        <div class="method-content" id="apiMethod">
                            <div style="text-align: center; padding: 40px 20px;">
                                <i class="fas fa-plug" style="font-size: 3rem; color: var(--gray); margin-bottom: 20px;"></i>
                                <h3 style="margin-bottom: 10px;">API Import Coming Soon</h3>
                                <p style="color: var(--gray);">Direct integration with game APIs will be available in v2.5</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Process Button -->
                    <div style="padding: 0 var(--space-lg) var(--space-lg);">
                        <button class="btn btn-primary btn-lg" id="processBtn" disabled>
                            <i class="fas fa-bolt"></i> Generate AI Prediction
                        </button>
                        <input type="hidden" id="csrfToken" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    </div>
                </div>
                
                <!-- System Status Card -->
                <div class="sidebar-card">
                    <h2 class="card-title">
                        <i class="fas fa-microchip"></i> System Status
                    </h2>
                    <div style="padding: var(--space-lg);">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <div style="width: 12px; height: 12px; background: #06d6a0; border-radius: 50%;"></div>
                            <span style="font-weight: 600;">OCR.space API</span>
                            <span style="color: var(--success); margin-left: auto;">Operational</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                            <div style="width: 12px; height: 12px; background: #06d6a0; border-radius: 50%;"></div>
                            <span style="font-weight: 600;">AI Prediction Engine</span>
                            <span style="color: var(--success); margin-left: auto;">v2.1</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 12px; height: 12px; background: #06d6a0; border-radius: 50%;"></div>
                            <span style="font-weight: 600;">API Key</span>
                            <span style="color: var(--success); margin-left: auto;">Active</span>
                        </div>
                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                                <span style="color: var(--gray);">Total Predictions</span>
                                <span style="font-weight: 700;">1,284</span>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--gray);">Accuracy Rate</span>
                                <span style="font-weight: 700; color: var(--success);">94.7%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Right Column - Results Section -->
            <div class="right-column">
                <!-- Processing Status -->
                <div id="processingSection" class="processing-section hidden">
                    <div class="processing-header">
                        <h2><i class="fas fa-cogs"></i> AI Processing</h2>
                        <div class="processing-time" id="processingTime">0s</div>
                    </div>
                    
                    <div class="processing-steps">
                        <div class="step active" data-step="1">
                            <div class="step-icon">
                                <i class="fas fa-upload"></i>
                            </div>
                            <div class="step-content">
                                <h4>Upload & Validation</h4>
                                <p>Validating player images...</p>
                            </div>
                            <div class="step-status">
                                <i class="fas fa-check"></i>
                            </div>
                        </div>
                        <div class="step" data-step="2">
                            <div class="step-icon">
                                <i class="fas fa-eye"></i>
                            </div>
                            <div class="step-content">
                                <h4>OCR.space Processing</h4>
                                <p>Extracting stats from images...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                        <div class="step" data-step="3">
                            <div class="step-icon">
                                <i class="fas fa-brain"></i>
                            </div>
                            <div class="step-content">
                                <h4>AI Analysis</h4>
                                <p>Calculating power scores...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                        <div class="step" data-step="4">
                            <div class="step-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="step-content">
                                <h4>Generating Results</h4>
                                <p>Predicting winner...</p>
                            </div>
                            <div class="step-status"></div>
                        </div>
                    </div>
                    
                    <div class="progress-container">
                        <div class="progress-bar" id="progressBar" style="width: 0%"></div>
                    </div>
                    
                    <div id="processingDetails" style="text-align: center; color: var(--gray); margin-top: 20px;">
                        Initializing prediction engine...
                    </div>
                </div>
                
                <!-- Results Section -->
                <div id="resultsSection" class="results-section hidden">
                    <!-- Results Header -->
                    <div class="results-header">
                        <div>
                            <h2 style="font-size: 1.5rem; margin-bottom: 10px;">
                                <i class="fas fa-trophy" style="color: var(--warning);"></i> Prediction Results
                            </h2>
                            <div style="display: flex; gap: 15px;">
                                <span style="color: var(--gray);">
                                    <i class="fas fa-calendar"></i> <?php echo date('M d, Y H:i'); ?>
                                </span>
                                <span style="color: var(--gray);">
                                    <i class="fas fa-code-branch"></i> v2.1
                                </span>
                            </div>
                        </div>
                        <div class="confidence-badge" id="confidenceBadge">
                            <div class="confidence-value" id="confidenceValueMain">0%</div>
                            <div style="font-size: 0.8rem;">Confidence</div>
                        </div>
                    </div>
                    
                    <!-- Winner Card -->
                    <div class="winner-section">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                            <h3><i class="fas fa-crown" style="color: var(--warning);"></i> Predicted Winner</h3>
                            <span class="badge" id="winnerTag" style="background: var(--primary);">Solo Match</span>
                        </div>
                        <div class="winner-card">
                            <div class="winner-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <div class="winner-info">
                                <h4 id="winnerName">Loading...</h4>
                                <div class="winner-stats">
                                    <div class="stat">
                                        <i class="fas fa-skull"></i>
                                        <span>Kills: <strong id="winnerKills">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-bullseye"></i>
                                        <span>Damage: <strong id="winnerDamage">0</strong></span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-bolt"></i>
                                        <span>Power Score: <strong id="winnerPowerScore">0</strong></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Team Members (for team modes) -->
                        <div id="teamMembers" style="margin-top: 30px;">
                            <h5 style="display: flex; align-items: center; gap: 8px; margin-bottom: 15px;">
                                <i class="fas fa-users"></i> Team Performance
                            </h5>
                            <div id="teamPerformanceGrid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px;"></div>
                        </div>
                    </div>
                    
                    <!-- Performance Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                        <div class="performance-card">
                            <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <i class="fas fa-star" style="color: var(--warning); font-size: 1.3rem;"></i>
                                <h4 style="margin: 0;">Top Performer</h4>
                            </div>
                            <div class="card-body">
                                <div style="font-size: 1.2rem; font-weight: 700; margin-bottom: 10px;" id="topPerformerName">-</div>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <span style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--gray);">K/D:</span>
                                        <strong id="topPerformerKD">0.0</strong>
                                    </span>
                                    <span style="display: flex; justify-content: space-between;">
                                        <span style="color: var(--gray);">Damage:</span>
                                        <strong id="topPerformerDMG">0</strong>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="performance-card">
                            <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <i class="fas fa-handshake" style="color: var(--primary); font-size: 1.3rem;"></i>
                                <h4 style="margin: 0;">Team Synergy</h4>
                            </div>
                            <div class="card-body">
                                <div style="display: flex; align-items: baseline; gap: 8px; margin-bottom: 15px;">
                                    <span style="font-size: 2rem; font-weight: 800; color: var(--primary);" id="synergyScore">0.0</span>
                                    <span style="color: var(--gray);">/10</span>
                                </div>
                                <div id="synergyDesc" style="margin-bottom: 10px; color: var(--gray);">Team coordination level</div>
                                <div class="synergy-meter" style="height: 6px; background: var(--gray-light); border-radius: 3px;">
                                    <div id="synergyMeter" style="width: 0%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 3px;"></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="performance-card">
                            <div class="card-header" style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                                <i class="fas fa-robot" style="color: var(--success); font-size: 1.3rem;"></i>
                                <h4 style="margin: 0;">AI Confidence</h4>
                            </div>
                            <div class="card-body">
                                <div style="font-size: 2rem; font-weight: 800; color: var(--success); margin-bottom: 10px;" id="predictionAccuracy">0%</div>
                                <div id="predictionDesc" style="color: var(--gray);">Based on ML analysis</div>
                                <div style="margin-top: 15px; display: flex; gap: 8px;">
                                    <span style="padding: 4px 10px; background: var(--light); border-radius: 20px; font-size: 0.75rem;">v2.1</span>
                                    <span style="padding: 4px 10px; background: var(--light); border-radius: 20px; font-size: 0.75rem;">OCR.space</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Error Section -->
                <div id="errorSection" class="error-section hidden">
                    <div class="error-header">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>Processing Error</h3>
                    </div>
                    <div class="error-body">
                        <p id="errorMessage" style="margin-bottom: 20px;">An error occurred during processing.</p>
                        <div style="display: flex; gap: 10px;">
                            <button class="btn btn-primary" id="retryButton">
                                <i class="fas fa-redo"></i> Try Again
                            </button>
                            <button class="btn btn-secondary" id="resetButton">
                                <i class="fas fa-plus"></i> New Prediction
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- New Prediction Section -->
                <div id="newPredictionSection" class="new-prediction-section hidden">
                    <div style="text-align: center;">
                        <h3 style="color: white; margin-bottom: 15px;">
                            <i class="fas fa-check-circle"></i> Prediction Complete!
                        </h3>
                        <button class="btn btn-primary btn-lg" id="newPredictionBtn" style="background: white; color: var(--primary);">
                            <i class="fas fa-plus"></i> Start New Prediction
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // ============================================
    // INFIKNIGHT PREDICTOR - Main Application Logic
    // ============================================
    
    class INFIKNIGHTPredictor {
        constructor() {
            this.apiUrl = window.location.href;
            this.matchType = 'solo';
            this.players = [];
            this.teams = [];
            this.prediction = null;
            this.uploadedFiles = [];
            this.csrfToken = document.getElementById('csrfToken')?.value || '';
            
            this.initializeEventListeners();
            this.updateUI();
        }

        initializeEventListeners() {
            // Method tabs
            document.querySelectorAll('.method-tab').forEach(tab => {
                tab.addEventListener('click', (e) => this.switchMethod(e));
            });

            // Match type selection (OCR mode)
            document.querySelectorAll('input[name="matchType"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    this.matchType = e.target.value;
                    this.updateMatchTypeUI();
                });
            });

            // Match type selection (Manual mode)
            document.querySelectorAll('input[name="matchTypeManual"]').forEach(radio => {
                radio.addEventListener('change', (e) => {
                    const manualMatchType = e.target.value;
                    this.toggleManualMode(manualMatchType);
                });
            });

            // Upload zone
            const uploadZone = document.getElementById('uploadZone');
            const browseBtn = document.getElementById('browseBtn');
            const fileInput = document.getElementById('statsFile');

            if (uploadZone) {
                uploadZone.addEventListener('click', () => fileInput.click());
                uploadZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    uploadZone.classList.add('highlight');
                });
                uploadZone.addEventListener('dragleave', () => {
                    uploadZone.classList.remove('highlight');
                });
                uploadZone.addEventListener('drop', (e) => this.handleDrop(e));
            }

            if (browseBtn) {
                browseBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    fileInput.click();
                });
            }

            if (fileInput) {
                fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
            }

            // Team generation (Manual mode)
            const generateTeamsBtn = document.getElementById('generateTeamsManual');
            if (generateTeamsBtn) {
                generateTeamsBtn.addEventListener('click', () => this.generateManualTeams());
            }

            // Team count change
            const teamCountSelect = document.getElementById('teamCountManual');
            if (teamCountSelect) {
                teamCountSelect.addEventListener('change', () => this.generateManualTeams());
            }

            // Add player button (Manual mode - Solo)
            const addPlayerBtn = document.getElementById('addPlayerBtnManual');
            if (addPlayerBtn) {
                addPlayerBtn.addEventListener('click', () => this.addManualPlayer());
            }

            // Process manual button
            const processManualBtn = document.getElementById('processManualBtn');
            if (processManualBtn) {
                processManualBtn.addEventListener('click', () => this.processManualPrediction());
            }

            // Process button (OCR mode)
            const processBtn = document.getElementById('processBtn');
            if (processBtn) {
                processBtn.addEventListener('click', () => this.processPrediction());
            }

            // New prediction button
            const newPredictionBtn = document.getElementById('newPredictionBtn');
            if (newPredictionBtn) {
                newPredictionBtn.addEventListener('click', () => this.resetUI());
            }

            // Retry button
            const retryBtn = document.getElementById('retryButton');
            if (retryBtn) {
                retryBtn.addEventListener('click', () => this.processPrediction());
            }

            // Reset button
            const resetBtn = document.getElementById('resetButton');
            if (resetBtn) {
                resetBtn.addEventListener('click', () => this.resetUI());
            }

            // Theme toggle
            const themeToggle = document.getElementById('themeToggle');
            if (themeToggle) {
                themeToggle.addEventListener('click', () => this.toggleTheme());
            }
        }

        // ========== OCR METHODS ==========

        switchMethod(event) {
            const tab = event.currentTarget;
            const method = tab.dataset.method;

            document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
            tab.classList.add('active');

            document.querySelectorAll('.method-content').forEach(c => c.classList.remove('active'));
            document.getElementById(method + 'Method').classList.add('active');
        }

        handleDrop(event) {
            event.preventDefault();
            const uploadZone = document.getElementById('uploadZone');
            uploadZone.classList.remove('highlight');

            const files = event.dataTransfer.files;
            this.handleFiles(files);
        }

        handleFileSelect(event) {
            const files = event.target.files;
            this.handleFiles(files);
        }

        handleFiles(files) {
            this.uploadedFiles = Array.from(files);
            this.displayFilePreview();
            
            // Enable process button if enough players
            this.updateProcessButton();
        }

        displayFilePreview() {
            const preview = document.getElementById('filePreview');
            const fileList = document.getElementById('fileList');
            const fileCount = document.getElementById('fileCount');
            
            preview.classList.remove('hidden');
            fileCount.textContent = this.uploadedFiles.length;
            
            let html = '';
            this.uploadedFiles.slice(0, 4).forEach((file, index) => {
                html += `
                    <div style="background: var(--light); padding: 10px; border-radius: var(--radius-sm);">
                        <i class="fas fa-file-image" style="color: var(--primary); margin-right: 8px;"></i>
                        <span style="font-size: 0.85rem;">Player ${index + 1}</span>
                        <div style="font-size: 0.75rem; color: var(--gray); margin-top: 4px;">
                            ${file.name.substring(0, 15)}...
                        </div>
                    </div>
                `;
            });
            
            if (this.uploadedFiles.length > 4) {
                html += `
                    <div style="background: var(--light); padding: 10px; border-radius: var(--radius-sm); display: flex; align-items: center; justify-content: center;">
                        <span style="font-size: 0.85rem; color: var(--gray);">
                            +${this.uploadedFiles.length - 4} more
                        </span>
                    </div>
                `;
            }
            
            fileList.innerHTML = html;
        }

        async processPrediction() {
            if (this.uploadedFiles.length === 0) {
                this.showNotification('Please select player images first', 'error');
                return;
            }

            // Validate minimum players
            let requiredPlayers = 2; // Solo
            if (this.matchType === 'duo') requiredPlayers = 4;
            if (this.matchType === 'squad') requiredPlayers = 8;

            if (this.uploadedFiles.length < requiredPlayers) {
                this.showNotification(`${this.matchType.toUpperCase()} match requires at least ${requiredPlayers} players`, 'error');
                return;
            }

            // Show processing section
            document.getElementById('processingSection').classList.remove('hidden');
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('errorSection').classList.add('hidden');
            
            this.startProcessingAnimation();

            const formData = new FormData();
            this.uploadedFiles.forEach(file => {
                formData.append('player_images[]', file);
            });
            formData.append('action', `process_${this.matchType}`);
            formData.append('csrf_token', this.csrfToken);

            try {
                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.players = result.data.players || [];
                    
                    // Display OCR results
                    if (this.matchType === 'solo') {
                        this.displayOCRResults(result.data);
                    } else {
                        this.teams = result.data.teams || [];
                        this.displayTeamResults(result.data);
                    }
                    
                    // Now get prediction
                    await this.getPrediction();
                    
                } else {
                    throw new Error(result.error || 'Processing failed');
                }

            } catch (error) {
                console.error('Error:', error);
                this.showNotification('Error: ' + error.message, 'error');
                this.showError(error.message);
            }
        }

        async getPrediction() {
            const formData = new FormData();
            formData.append('action', 'predict');
            formData.append('csrf_token', this.csrfToken);

            try {
                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    this.prediction = result.prediction;
                    this.displayPredictionResults(result.prediction);
                    
                    document.getElementById('processingSection').classList.add('hidden');
                    document.getElementById('resultsSection').classList.remove('hidden');
                    document.getElementById('newPredictionSection').classList.remove('hidden');
                    
                    this.showNotification('Prediction generated successfully!', 'success');
                } else {
                    throw new Error(result.error || 'Prediction failed');
                }

            } catch (error) {
                console.error('Prediction error:', error);
                this.showError(error.message);
            }
        }

        displayOCRResults(data) {
            const uploadZone = document.getElementById('uploadZone');
            const ocrPreview = document.getElementById('ocrPreview');
            
            uploadZone.style.display = 'none';
            ocrPreview.classList.remove('hidden');
            
            const validPlayers = data.players.filter(p => p.success !== false);
            const avgConfidence = validPlayers.reduce((sum, p) => sum + (p.ocr_confidence || 0), 0) / validPlayers.length;
            
            let html = `
                <div class="ocr-header">
                    <h4><i class="fas fa-check-circle" style="color: var(--success);"></i> OCR Extraction Complete</h4>
                    <div class="ocr-meta">
                        <span class="meta-item"><i class="fas fa-users"></i> ${validPlayers.length} Players</span>
                        <span class="meta-item"><i class="fas fa-robot"></i> OCR.space Engine 3</span>
                        <span class="meta-item"><i class="fas fa-check-circle"></i> ${Math.round(avgConfidence)}% Avg</span>
                    </div>
                </div>
                
                <div class="confidence-display">
                    <div class="confidence-label">OCR Confidence</div>
                    <div class="confidence-value ${avgConfidence > 80 ? 'high' : avgConfidence > 60 ? 'medium' : 'low'}">
                        ${Math.round(avgConfidence)}%
                    </div>
                    <div class="confidence-bar">
                        <div class="confidence-fill" style="width: ${avgConfidence}%"></div>
                    </div>
                </div>
                
                <div class="career-stats-grid">
            `;
            
            validPlayers.slice(0, 4).forEach((player, index) => {
                const stats = player.stats || {};
                html += `
                    <div class="stat-card">
                        <div class="stat-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="stat-content">
                            <div class="stat-label">${player.name || 'Player ' + (index + 1)}</div>
                            <div class="stat-value">PS: ${player.power_score || 0}</div>
                            <div style="font-size: 0.75rem; color: var(--gray); margin-top: 4px;">
                                K/D: ${stats.kd_ratio || '0.00'} | Win: ${stats.win_ratio || '0'}%
                            </div>
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            if (validPlayers.length > 4) {
                html += `
                    <div style="text-align: center; margin: 10px 0; color: var(--gray);">
                        <i class="fas fa-ellipsis-h"></i> +${validPlayers.length - 4} more players
                    </div>
                `;
            }
            
            html += `
                <div class="ocr-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn btn-success" onclick="predictor.processPrediction()" style="flex: 1;">
                        <i class="fas fa-bolt"></i> Generate Prediction
                    </button>
                    <button class="btn btn-secondary" onclick="predictor.resetUpload()" style="flex: 1;">
                        <i class="fas fa-redo"></i> Upload Different
                    </button>
                </div>
            `;
            
            ocrPreview.innerHTML = html;
        }

        displayTeamResults(data) {
            const uploadZone = document.getElementById('uploadZone');
            const ocrPreview = document.getElementById('ocrPreview');
            
            uploadZone.style.display = 'none';
            ocrPreview.classList.remove('hidden');
            
            let html = `
                <div class="ocr-header">
                    <h4><i class="fas fa-check-circle" style="color: var(--success);"></i> Teams Formed Successfully</h4>
                    <div class="ocr-meta">
                        <span class="meta-item"><i class="fas fa-users"></i> ${data.total_players} Players</span>
                        <span class="meta-item"><i class="fas fa-people-arrows"></i> ${data.total_teams} Teams</span>
                        <span class="meta-item"><i class="fas fa-user-plus"></i> ${data.players_per_team}/Team</span>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px; margin-top: 20px;">
            `;
            
            data.teams.forEach((team, index) => {
                const medalClass = index === 0 ? 'medal-gold' : index === 1 ? 'medal-silver' : 'medal-bronze';
                html += `
                    <div class="team-performance-card ${medalClass}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-size: 1.8rem; font-weight: 800;">#${index + 1}</span>
                            <span style="background: rgba(0,0,0,0.1); padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                                PS: ${team.power_score}
                            </span>
                        </div>
                        <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 10px;">${team.name}</div>
                        <div style="margin-bottom: 10px; color: inherit; opacity: 0.8;">
                            <i class="fas fa-handshake"></i> Synergy: ${team.synergy}x
                        </div>
                        <hr style="margin: 10px 0; opacity: 0.2;">
                        <div style="font-size: 0.85rem;">
                            ${team.players.map(p => `
                                <div style="display: flex; justify-content: space-between; margin-bottom: 4px;">
                                    <span>${p.name}</span>
                                    <span style="font-weight: 600;">${p.power_score} PS</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });
            
            html += `</div>`;
            
            html += `
                <div class="ocr-actions" style="display: flex; gap: 10px; margin-top: 20px;">
                    <button class="btn btn-success" onclick="predictor.processPrediction()" style="flex: 1;">
                        <i class="fas fa-bolt"></i> Predict Winner
                    </button>
                    <button class="btn btn-secondary" onclick="predictor.resetUpload()" style="flex: 1;">
                        <i class="fas fa-redo"></i> Upload Different
                    </button>
                </div>
            `;
            
            ocrPreview.innerHTML = html;
        }

        displayPredictionResults(prediction) {
            const winner = prediction.winner;
            
            // Update winner card
            document.getElementById('winnerName').textContent = winner.team_name;
            document.getElementById('winnerKills').textContent = winner.players[0]?.kd || '0';
            document.getElementById('winnerDamage').textContent = winner.players[0]?.damage || '0';
            document.getElementById('winnerPowerScore').textContent = winner.power_score;
            document.getElementById('confidenceValueMain').textContent = prediction.confidence + '%';
            document.getElementById('winnerTag').textContent = this.matchType.toUpperCase() + ' Match';
            
            // Update confidence badge color
            const confidenceBadge = document.getElementById('confidenceBadge');
            if (prediction.confidence > 80) {
                confidenceBadge.style.background = 'linear-gradient(135deg, #06d6a0, #059669)';
            } else if (prediction.confidence > 60) {
                confidenceBadge.style.background = 'linear-gradient(135deg, #ffd60a, #f39c12)';
            } else {
                confidenceBadge.style.background = 'linear-gradient(135deg, #ef476f, #d62828)';
            }
            
            // Update top performer
            const allPlayers = prediction.all_teams.flatMap(t => t.players);
            const topPerformer = allPlayers.sort((a, b) => b.power_score - a.power_score)[0];
            
            if (topPerformer) {
                document.getElementById('topPerformerName').textContent = topPerformer.name;
                document.getElementById('topPerformerKD').textContent = topPerformer.kd || '0.0';
                document.getElementById('topPerformerDMG').textContent = topPerformer.damage || '0';
            }
            
            // Update synergy
            const avgSynergy = prediction.all_teams.reduce((sum, t) => sum + t.synergy, 0) / prediction.all_teams.length;
            document.getElementById('synergyScore').textContent = (avgSynergy * 8).toFixed(1);
            document.getElementById('synergyMeter').style.width = (avgSynergy * 80) + '%';
            document.getElementById('synergyDesc').textContent = 
                avgSynergy > 1.2 ? 'Excellent team coordination' : 
                avgSynergy > 1.0 ? 'Good synergy' : 'Needs improvement';
            
            // Update prediction accuracy
            document.getElementById('predictionAccuracy').textContent = prediction.confidence + '%';
            document.getElementById('predictionDesc').textContent = 
                prediction.confidence > 80 ? 'Very confident prediction' :
                prediction.confidence > 60 ? 'Moderate confidence' : 'Low confidence';
            
            // Update team performance for team modes
            if (this.matchType !== 'solo' && prediction.all_teams.length > 0) {
                this.displayTeamPerformance(prediction.all_teams);
            } else {
                document.getElementById('teamMembers').style.display = 'none';
            }
        }

        displayTeamPerformance(teams) {
            const grid = document.getElementById('teamPerformanceGrid');
            document.getElementById('teamMembers').style.display = 'block';
            
            let html = '';
            teams.sort((a, b) => b.power_score - a.power_score).forEach((team, index) => {
                const medalClass = index === 0 ? 'medal-gold' : index === 1 ? 'medal-silver' : 'medal-bronze';
                html += `
                    <div class="team-performance-card ${medalClass}">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 1.5rem; font-weight: 800;">#${index + 1}</span>
                            <span style="background: rgba(0,0,0,0.1); padding: 4px 10px; border-radius: 20px; font-weight: 600;">
                                ${team.win_probability}%
                            </span>
                        </div>
                        <div style="font-weight: 700; margin-bottom: 4px;">${team.team_name}</div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                            <span>Power Score</span>
                            <span style="font-weight: 700;">${team.power_score}</span>
                        </div>
                        <div style="width: 100%; height: 6px; background: rgba(0,0,0,0.1); border-radius: 3px;">
                            <div style="width: ${team.win_probability}%; height: 100%; background: ${index === 0 ? '#f2c94c' : '#94a3b8'}; border-radius: 3px;"></div>
                        </div>
                    </div>
                `;
            });
            
            grid.innerHTML = html;
        }

        // ========== MANUAL MODE METHODS ==========

        toggleManualMode(matchType) {
            const teamConfig = document.getElementById('teamConfigManual');
            const playerInputs = document.getElementById('playerInputsManual');
            
            if (matchType === 'solo') {
                teamConfig.style.display = 'none';
                playerInputs.style.display = 'block';
            } else {
                teamConfig.style.display = 'block';
                playerInputs.style.display = 'none';
                this.generateManualTeams();
            }
        }

        generateManualTeams() {
            const teamCount = parseInt(document.getElementById('teamCountManual').value);
            const matchType = document.querySelector('input[name="matchTypeManual"]:checked')?.value || 'duo';
            const playersPerTeam = matchType === 'duo' ? 2 : 4;
            
            const container = document.getElementById('teamInputsManual');
            let html = '';
            
            for (let t = 1; t <= teamCount; t++) {
                html += `
                    <div class="team-section" style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                            <h4 style="display: flex; align-items: center; gap: 8px;">
                                <i class="fas fa-trophy" style="color: var(--primary);"></i> Team ${t}
                            </h4>
                            <input type="text" class="team-name-input" 
                                   placeholder="Team Name" value="Team ${t}"
                                   style="width: 200px; padding: 8px; border: 2px solid var(--border); border-radius: var(--radius-sm);">
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px;">
                `;
                
                for (let p = 1; p <= playersPerTeam; p++) {
                    html += `
                        <div style="padding: 15px; background: var(--light); border-radius: var(--radius-sm);">
                            <h5 style="display: flex; align-items: center; gap: 6px; margin-bottom: 10px;">
                                <i class="fas fa-user"></i> Player ${p}
                            </h5>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 0.7rem; color: var(--gray);">Name</label>
                                    <input type="text" class="team-player-name" value="Player ${p}" 
                                           style="width: 100%; padding: 6px; border: 1px solid var(--border); border-radius: 4px;">
                                </div>
                                <div>
                                    <label style="font-size: 0.7rem; color: var(--gray);">Kills</label>
                                    <input type="number" class="team-player-kills" min="0" value="${Math.floor(Math.random() * 8) + 2}"
                                           style="width: 100%; padding: 6px; border: 1px solid var(--border); border-radius: 4px;">
                                </div>
                                <div>
                                    <label style="font-size: 0.7rem; color: var(--gray);">Damage</label>
                                    <input type="number" class="team-player-damage" min="0" value="${Math.floor(Math.random() * 300) + 100}"
                                           style="width: 100%; padding: 6px; border: 1px solid var(--border); border-radius: 4px;">
                                </div>
                                <div>
                                    <label style="font-size: 0.7rem; color: var(--gray);">Survival</label>
                                    <div style="display: flex; gap: 5px;">
                                        <input type="number" class="team-player-survival-min" min="0" value="7" style="width: 60px; padding: 6px; border: 1px solid var(--border); border-radius: 4px;">
                                        <span style="display: flex; align-items: center;">:</span>
                                        <input type="number" class="team-player-survival-sec" min="0" max="59" value="30" style="width: 60px; padding: 6px; border: 1px solid var(--border); border-radius: 4px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }
                
                html += `</div></div>`;
            }
            
            container.innerHTML = html;
        }

        addManualPlayer() {
            const container = document.getElementById('playerInputsManual');
            const playerCount = container.children.length + 1;
            
            const div = document.createElement('div');
            div.className = 'player-input-section';
            div.style.marginTop = '15px';
            div.innerHTML = `
                <h4 style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                    <i class="fas fa-user-circle"></i> Player ${playerCount}
                </h4>
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px;">
                    <div>
                        <label style="font-size: 0.75rem; color: var(--gray);">Name</label>
                        <input type="text" class="player-name" value="Player ${playerCount}"
                               style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--gray);">Kills</label>
                        <input type="number" class="player-kills" min="0" value="5"
                               style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--gray);">Damage</label>
                        <input type="number" class="player-damage" min="0" value="250"
                               style="width: 100%; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; color: var(--gray);">Survival</label>
                        <div style="display: flex; gap: 5px;">
                            <input type="number" class="player-survival-min" min="0" value="7" style="width: 70px; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                            <span style="display: flex; align-items: center;">:</span>
                            <input type="number" class="player-survival-sec" min="0" max="59" value="30" style="width: 70px; padding: 8px; border: 1px solid var(--border); border-radius: 4px;">
                        </div>
                    </div>
                </div>
                <button class="btn btn-danger btn-sm" onclick="this.parentElement.remove()" style="margin-top: 10px;">
                    <i class="fas fa-times"></i> Remove
                </button>
            `;
            
            container.appendChild(div);
        }

        processManualPrediction() {
            this.showNotification('Manual prediction will be implemented in v2.5', 'info');
        }

        // ========== UTILITY METHODS ==========

        startProcessingAnimation() {
            let progress = 0;
            const progressBar = document.getElementById('progressBar');
            const processingTime = document.getElementById('processingTime');
            const processingDetails = document.getElementById('processingDetails');
            const startTime = Date.now();
            
            this.clearProcessingInterval();
            
            this.processingInterval = setInterval(() => {
                progress += 0.5;
                if (progress <= 100) {
                    progressBar.style.width = progress + '%';
                    
                    const elapsed = ((Date.now() - startTime) / 1000).toFixed(1);
                    processingTime.textContent = elapsed + 's';
                    
                    if (progress < 25) {
                        processingDetails.innerHTML = 'Validating player images...';
                    } else if (progress < 50) {
                        processingDetails.innerHTML = 'OCR.space extracting statistics...';
                        document.querySelector('.step[data-step="1"] .step-status i')?.classList.add('fa-check');
                        document.querySelector('.step[data-step="2"]')?.classList.add('active');
                    } else if (progress < 75) {
                        processingDetails.innerHTML = 'Analyzing player performance...';
                        document.querySelector('.step[data-step="2"] .step-status i')?.classList.add('fa-check');
                        document.querySelector('.step[data-step="3"]')?.classList.add('active');
                    } else if (progress < 90) {
                        processingDetails.innerHTML = 'Calculating win probabilities...';
                        document.querySelector('.step[data-step="3"] .step-status i')?.classList.add('fa-check');
                        document.querySelector('.step[data-step="4"]')?.classList.add('active');
                    }
                }
            }, 50);
        }

        clearProcessingInterval() {
            if (this.processingInterval) {
                clearInterval(this.processingInterval);
                this.processingInterval = null;
            }
        }

        updateProcessButton() {
            const processBtn = document.getElementById('processBtn');
            let required = 2; // Solo
            
            if (this.matchType === 'duo') required = 4;
            if (this.matchType === 'squad') required = 8;
            
            processBtn.disabled = this.uploadedFiles.length < required;
            
            if (this.uploadedFiles.length >= required) {
                processBtn.style.opacity = '1';
            } else {
                processBtn.style.opacity = '0.6';
            }
        }

        updateMatchTypeUI() {
            this.updateProcessButton();
            
            // Hide OCR preview when changing match type
            const uploadZone = document.getElementById('uploadZone');
            const ocrPreview = document.getElementById('ocrPreview');
            
            if (uploadZone.style.display === 'none') {
                uploadZone.style.display = 'block';
                ocrPreview.classList.add('hidden');
            }
        }

        resetUpload() {
            const uploadZone = document.getElementById('uploadZone');
            const ocrPreview = document.getElementById('ocrPreview');
            const filePreview = document.getElementById('filePreview');
            const fileInput = document.getElementById('statsFile');
            
            uploadZone.style.display = 'block';
            ocrPreview.classList.add('hidden');
            filePreview.classList.add('hidden');
            fileInput.value = '';
            
            this.uploadedFiles = [];
            this.updateProcessButton();
        }

        resetUI() {
            // Reset state
            this.players = [];
            this.teams = [];
            this.prediction = null;
            this.uploadedFiles = [];
            
            // Reset UI elements
            document.getElementById('resultsSection').classList.add('hidden');
            document.getElementById('newPredictionSection').classList.add('hidden');
            document.getElementById('processingSection').classList.add('hidden');
            document.getElementById('errorSection').classList.add('hidden');
            
            this.resetUpload();
            
            // Reset to solo mode
            document.querySelector('input[name="matchType"][value="solo"]').checked = true;
            this.matchType = 'solo';
            this.updateProcessButton();
            
            // Clear processing interval
            this.clearProcessingInterval();
            
            this.showNotification('Ready for new prediction', 'info');
        }

        showError(message) {
            document.getElementById('processingSection').classList.add('hidden');
            document.getElementById('errorSection').classList.remove('hidden');
            document.getElementById('errorMessage').textContent = message;
        }

        showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type} show`;
            notification.innerHTML = `
                <div class="notification-content">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('show');
                setTimeout(() => notification.remove(), 300);
            }, 4000);
        }

        toggleTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            html.setAttribute('data-theme', newTheme);
            
            const icon = document.querySelector('#themeToggle i');
            if (icon) {
                icon.className = newTheme === 'light' ? 'fas fa-moon' : 'fas fa-sun';
            }
        }

        updateUI() {
            // Initialize manual mode as hidden
            document.getElementById('teamConfigManual').style.display = 'none';
        }
    }

    // Initialize the predictor
    const predictor = new INFIKNIGHTPredictor();
    window.predictor = predictor;
    </script>
</body>
</html>
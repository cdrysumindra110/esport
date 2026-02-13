<?php
// api/ocr_prediction_api.php
// Main API Endpoint for OCR + Prediction

session_start();
require_once '../includes/team_predictor.php';

use InfiKnight\Predictor\TeamPredictor;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

class OCRPredictionAPI {
    
    private $predictor;
    private $uploadDir;
    private $maxFiles = 16; // Max 16 players (4 teams of squad)
    
    public function __construct() {
        $this->predictor = new TeamPredictor(true);
        $this->uploadDir = __DIR__ . '/../uploads/';
        
        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    /**
     * Handle API Request
     */
    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $endpoint = $_GET['endpoint'] ?? '';
        
        switch ($method) {
            case 'POST':
                switch ($endpoint) {
                    case 'process-solo':
                        $this->processSoloMatch();
                        break;
                    case 'process-duo':
                        $this->processTeamMatch('duo');
                        break;
                    case 'process-squad':
                        $this->processTeamMatch('squad');
                        break;
                    case 'predict':
                        $this->predictWinner();
                        break;
                    case 'reset':
                        $this->resetSession();
                        break;
                    default:
                        $this->sendResponse(404, ['error' => 'Endpoint not found']);
                }
                break;
                
            case 'GET':
                switch ($endpoint) {
                    case 'status':
                        $this->getStatus();
                        break;
                    case 'players':
                        $this->getPlayers();
                        break;
                    case 'teams':
                        $this->getTeams();
                        break;
                    default:
                        $this->sendResponse(404, ['error' => 'Endpoint not found']);
                }
                break;
                
            default:
                $this->sendResponse(405, ['error' => 'Method not allowed']);
        }
    }
    
    /**
     * PROCESS SOLO MATCH - Each image = 1 player
     */
    private function processSoloMatch() {
        if (!isset($_FILES['player_images'])) {
            $this->sendResponse(400, ['error' => 'No player images uploaded']);
            return;
        }
        
        $files = $_FILES['player_images'];
        $playerNames = $_POST['player_names'] ?? [];
        $uploadedFiles = $this->saveUploadedFiles($files);
        
        try {
            $result = $this->predictor->processSoloMatch($uploadedFiles, $playerNames);
            $this->cleanupFiles($uploadedFiles);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $result,
                'session_id' => $this->predictor->sessionId
            ]);
            
        } catch (\Exception $e) {
            $this->cleanupFiles($uploadedFiles);
            $this->sendResponse(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * PROCESS TEAM MATCH (DUO/SQUAD)
     */
    private function processTeamMatch($matchType) {
        if (!isset($_FILES['player_images'])) {
            $this->sendResponse(400, ['error' => 'No player images uploaded']);
            return;
        }
        
        $files = $_FILES['player_images'];
        $playerNames = $_POST['player_names'] ?? [];
        $uploadedFiles = $this->saveUploadedFiles($files);
        
        // Validate minimum players
        $minPlayers = $matchType === 'duo' ? 4 : 8;
        if (count($uploadedFiles) < $minPlayers) {
            $this->cleanupFiles($uploadedFiles);
            $this->sendResponse(400, [
                'error' => "$matchType match requires at least $minPlayers players",
                'uploaded' => count($uploadedFiles),
                'required' => $minPlayers
            ]);
            return;
        }
        
        try {
            $result = $this->predictor->processTeamMatch($uploadedFiles, $playerNames, $matchType);
            $this->cleanupFiles($uploadedFiles);
            
            $this->sendResponse(200, [
                'success' => true,
                'data' => $result,
                'session_id' => $this->predictor->sessionId
            ]);
            
        } catch (\Exception $e) {
            $this->cleanupFiles($uploadedFiles);
            $this->sendResponse(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * PREDICT WINNER
     */
    private function predictWinner() {
        try {
            $prediction = $this->predictor->predictWinner();
            
            // Store prediction in session
            $_SESSION['last_prediction'] = $prediction;
            
            $this->sendResponse(200, [
                'success' => true,
                'prediction' => $prediction
            ]);
            
        } catch (\Exception $e) {
            $this->sendResponse(500, [
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Save uploaded files to server
     */
    private function saveUploadedFiles($files) {
        $uploadedPaths = [];
        
        if (is_array($files['name'])) {
            // Multiple files
            foreach ($files['name'] as $index => $name) {
                if ($files['error'][$index] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($name, PATHINFO_EXTENSION);
                    $filename = uniqid('player_') . '_' . date('Ymd_His') . '.' . $extension;
                    $uploadPath = $this->uploadDir . $filename;
                    
                    if (move_uploaded_file($files['tmp_name'][$index], $uploadPath)) {
                        $uploadedPaths[] = $uploadPath;
                    }
                }
            }
        } else {
            // Single file
            if ($files['error'] === UPLOAD_ERR_OK) {
                $extension = pathinfo($files['name'], PATHINFO_EXTENSION);
                $filename = uniqid('player_') . '_' . date('Ymd_His') . '.' . $extension;
                $uploadPath = $this->uploadDir . $filename;
                
                if (move_uploaded_file($files['tmp_name'], $uploadPath)) {
                    $uploadedPaths[] = $uploadPath;
                }
            }
        }
        
        return $uploadedPaths;
    }
    
    /**
     * Cleanup temporary files
     */
    private function cleanupFiles($files) {
        foreach ($files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
    
    /**
     * Get system status
     */
    private function getStatus() {
        $this->sendResponse(200, [
            'status' => 'operational',
            'version' => '2.0.0',
            'ocr_api' => 'OCR.space',
            'api_key_valid' => true,
            'upload_limit' => '5MB per file',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Get processed players
     */
    private function getPlayers() {
        $this->sendResponse(200, [
            'players' => $this->predictor->getPlayers()
        ]);
    }
    
    /**
     * Get formed teams
     */
    private function getTeams() {
        $this->sendResponse(200, [
            'teams' => $this->predictor->getTeams()
        ]);
    }
    
    /**
     * Reset session
     */
    private function resetSession() {
        $this->predictor->reset();
        $this->sendResponse(200, [
            'success' => true,
            'message' => 'Session reset successfully'
        ]);
    }
    
    /**
     * Send JSON response
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        exit();
    }
}

// Run API
$api = new OCRPredictionAPI();
$api->handleRequest();
?>
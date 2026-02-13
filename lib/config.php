<?php
// lib/config.php - Shared configuration

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'esport');

// Application settings
define('MAX_FILE_SIZE', 10485760); // 10MB
define('OCR_ENABLED', true);
define('OCR_LANGUAGE', 'eng');
define('ML_ENHANCED', false);
define('ALGORITHM_VERSION', '3.0');
define('APP_ENV', 'development'); // or 'production'

// Initialize services
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Security.php';
require_once __DIR__ . '/Predictor.php';

// Check if cache is enabled
if (!defined('ENABLE_CACHE')) {
    define('ENABLE_CACHE', false);
}

// Global instances
$db = new EnhancedDatabase(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$security = new Security();
$predictor = new InfiKnightPredictor($db);

// Helper function to convert match stats to career stats format
function convertMatchToCareerStats($matchStats) {
    // Extract basic stats
    $kills = floatval($matchStats['kills'] ?? 0);
    $damage = floatval($matchStats['damage'] ?? 0);
    $survival = floatval($matchStats['survival'] ?? 0);
    $headshots = floatval($matchStats['headshots'] ?? 0);
    $assists = floatval($matchStats['assists'] ?? 0);
    
    // Estimate deaths (assuming survival time indicates performance)
    // Higher survival = fewer deaths estimated
    $estimatedDeaths = max(1, 5 - floor($survival / 300));
    
    // Calculate career-like stats from match stats
    $stats = [
        'kd' => $estimatedDeaths > 0 ? round($kills / $estimatedDeaths, 2) : $kills,
        'win_ratio' => min(1.0, max(0.01, $survival / 1800)), // Normalize survival to win rate
        'top10_rate' => min(0.80, max(0.05, ($kills * 0.1) + ($survival / 2000))), // Estimate top10
        'avg_damage' => min(1000, max(100, $damage)),
        'headshot_rate' => $kills > 0 ? min(0.70, $headshots / max(1, $kills)) : 0.10,
        'accuracy' => min(0.50, max(0.10, 0.15 + ($headshots * 0.02))) // Estimate accuracy
    ];
    
    return $stats;
}

// Helper function to convert match stats array to career stats
function convertPlayersToCareerStats($players) {
    $converted = [];
    
    foreach ($players as $player) {
        $converted[] = array_merge(
            ['name' => $player['name'] ?? 'Unknown'],
            convertMatchToCareerStats($player)
        );
    }
    
    return $converted;
}

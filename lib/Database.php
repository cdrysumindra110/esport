<?php
// lib/Database.php - Enhanced Database Operations
class EnhancedDatabase {
    private $conn;
    private $cache;
    private $cacheEnabled;
    
    public function __construct($host, $user, $password, $database) {
        $this->conn = new mysqli($host, $user, $password, $database);
        
        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }
        
        $this->conn->set_charset("utf8mb4");
        $this->cacheEnabled = ENABLE_CACHE && extension_loaded('redis');
        
        if ($this->cacheEnabled) {
            $this->initializeCache();
        }
    }
    
    private function initializeCache() {
        try {
            $this->cache = new Redis();
            $this->cache->connect('127.0.0.1', 6379, 2.5);
            $this->cache->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        } catch (Exception $e) {
            $this->cacheEnabled = false;
            error_log("Redis cache initialization failed: " . $e->getMessage());
        }
    }
    
    public function savePrediction($data) {
        $stmt = $this->conn->prepare("
            INSERT INTO predictions 
            (filename, match_type, extracted_data, prediction_result, 
             assists_score, placement_score, normalized_score, ml_enhanced, 
             algorithm_version, ocr_confidence, processed_data)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $this->conn->error);
        }
        
        // Calculate scores
        $assistsScore = $this->calculateAssistsScore($data);
        $placementScore = $this->calculatePlacementScore($data);
        $normalizedScore = $this->calculateNormalizedScore($data);
        
        $processedData = json_encode($this->processDataForStorage($data));
        
        $stmt->bind_param(
            "ssssdddissd",
            $data['filename'],
            $data['match_type'],
            json_encode($data['extracted_data']),
            json_encode($data['prediction_result']),
            $assistsScore,
            $placementScore,
            $normalizedScore,
            $data['ml_enhanced'] ?? 0,
            ALGORITHM_VERSION,
            $data['ocr_confidence'] ?? 0.0,
            $processedData
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Execute failed: " . $stmt->error);
        }
        
        $id = $stmt->insert_id;
        $stmt->close();
        
        // Cache the result
        $this->cacheResult($id, $data);
        
        return $id;
    }
    
    public function getPrediction($id) {
        $cacheKey = "prediction:$id";
        
        // Try cache first
        if ($this->cacheEnabled && $cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $stmt = $this->conn->prepare("
            SELECT * FROM predictions WHERE id = ?
        ");
        
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $data = $result->fetch_assoc();
        
        if ($data) {
            // Decode JSON fields
            $data['extracted_data'] = json_decode($data['extracted_data'], true);
            $data['prediction_result'] = json_decode($data['prediction_result'], true);
            $data['processed_data'] = json_decode($data['processed_data'], true);
            
            // Cache the result
            $this->cache->set($cacheKey, $data, CACHE_TTL);
        }
        
        return $data;
    }
    
    public function getHistoricalPredictions($limit = 10, $offset = 0) {
        $cacheKey = "historical:$limit:$offset";
        
        if ($this->cacheEnabled && $cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $stmt = $this->conn->prepare("
            SELECT p.*, 
                   COALESCE(AVG(ph.accuracy_score), 0) as avg_accuracy,
                   COUNT(ph.id) as feedback_count
            FROM predictions p
            LEFT JOIN prediction_history ph ON p.id = ph.prediction_id
            GROUP BY p.id
            ORDER BY p.upload_time DESC
            LIMIT ? OFFSET ?
        ");
        
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $predictions = [];
        
        while ($row = $result->fetch_assoc()) {
            $row['extracted_data'] = json_decode($row['extracted_data'], true);
            $row['prediction_result'] = json_decode($row['prediction_result'], true);
            $predictions[] = $row;
        }
        
        if ($this->cacheEnabled) {
            $this->cache->set($cacheKey, $predictions, 300); // 5 minutes
        }
        
        return $predictions;
    }
    
    public function getPlayerStats($playerName) {
        $cacheKey = "player_stats:" . md5($playerName);
        
        if ($this->cacheEnabled && $cached = $this->cache->get($cacheKey)) {
            return $cached;
        }
        
        $stmt = $this->conn->prepare("
            SELECT * FROM player_stats 
            WHERE player_name = ? 
            ORDER BY last_updated DESC 
            LIMIT 1
        ");
        
        $stmt->bind_param("s", $playerName);
        $stmt->execute();
        $result = $stmt->get_result();
        $stats = $result->fetch_assoc() ?? [
            'player_name' => $playerName,
            'avg_rating' => 5.0,
            'win_rate' => 50.0,
            'total_matches' => 0
        ];
        
        if ($this->cacheEnabled) {
            $this->cache->set($cacheKey, $stats, 1800); // 30 minutes
        }
        
        return $stats;
    }
    
    public function updatePlayerStats($playerName, $gameType, $rating, $won) {
        $stmt = $this->conn->prepare("
            INSERT INTO player_stats 
            (player_name, game_type, total_matches, total_kills, avg_rating, win_rate)
            VALUES (?, ?, 1, 0, ?, ?)
            ON DUPLICATE KEY UPDATE
            total_matches = total_matches + 1,
            avg_rating = ((avg_rating * total_matches) + ?) / (total_matches + 1),
            win_rate = ((win_rate * total_matches) + ?) / (total_matches + 1),
            last_updated = CURRENT_TIMESTAMP
        ");
        
        $winRate = $won ? 100 : 0;
        $stmt->bind_param("ssdddd", $playerName, $gameType, $rating, $winRate, $rating, $winRate);
        $stmt->execute();
        
        // Clear cache
        $this->cache->delete("player_stats:" . md5($playerName));
    }
    
    private function calculateAssistsScore($data) {
        if (!isset($data['extracted_data']['players'])) {
            return 0;
        }
        
        $totalAssists = 0;
        foreach ($data['extracted_data']['players'] as $player) {
            $totalAssists += $player['assists'] ?? 0;
        }
        
        return min(5, $totalAssists * 0.5);
    }
    
    private function calculatePlacementScore($data) {
        // Calculate based on prediction confidence
        $confidence = $data['prediction_result']['confidence'] ?? 50;
        return $confidence / 10;
    }
    
    private function calculateNormalizedScore($data) {
        // Normalize to 0-10 scale
        $assistsScore = $this->calculateAssistsScore($data);
        $placementScore = $this->calculatePlacementScore($data);
        
        return ($assistsScore * 0.3) + ($placementScore * 0.7);
    }
    
    private function processDataForStorage($data) {
        return [
            'processed_at' => date('Y-m-d H:i:s'),
            'algorithm_version' => ALGORITHM_VERSION,
            'ml_enhanced' => $data['ml_enhanced'] ?? false,
            'ocr_used' => isset($data['ocr_confidence']),
            'data_points' => count($data['extracted_data']['players'] ?? [])
        ];
    }
    
    private function cacheResult($id, $data) {
        if (!$this->cacheEnabled) return;
        
        $cacheKey = "prediction:$id";
        $this->cache->set($cacheKey, $data, CACHE_TTL);
    }
    
    public function close() {
        $this->conn->close();
        if ($this->cacheEnabled) {
            $this->cache->close();
        }
    }
}
?>
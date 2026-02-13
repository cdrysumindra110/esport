<?php
// includes/ocr_processor.php
// OCR.space API Processor for INFIKNIGHT

namespace InfiKnight\OCR;

// require_once 'prediction/includes/ocr_config.php';
// require_once 'prediction/includes/ocr_config.php';
require_once '../prediction/includes/ocr_config.php';

class OCRProcessor {
    
    private $apiKey;
    private $apiEndpoint;
    private $debugMode;
    private $lastResponse;
    private $processingLog = [];
    
    public function __construct($debug = false) {
        $this->apiKey = OCR_SPACE_API_KEY;
        $this->apiEndpoint = OCR_SPACE_ENDPOINT;
        $this->debugMode = $debug;
        $this->log("OCR Processor initialized");
    }
    
    /**
     * Process single image through OCR.space API
     */
    public function processImage($imagePath, $options = []) {
        $this->log("Processing image: " . basename($imagePath));
        
        // Validate image
        if (!file_exists($imagePath)) {
            throw new \Exception("Image file not found: $imagePath");
        }
        
        if (filesize($imagePath) > OCR_SPACE_MAX_FILE_SIZE) {
            throw new \Exception("File size exceeds 5MB limit");
        }
        
        // Default OCR options
        $ocrOptions = array_merge([
            'language' => 'eng',
            'isOverlayRequired' => false,
            'isCreateSearchablePdf' => false,
            'isSearchablePdfHideTextLayer' => false,
            'scale' => true,
            'detectOrientation' => true,
            'OCREngine' => 3, // Engine 3 best for gaming stats
            'filetype' => $this->getFileType($imagePath)
        ], $options);
        
        // Prepare request
        $postData = [
            'apikey' => $this->apiKey,
            'language' => $ocrOptions['language'],
            'isOverlayRequired' => $ocrOptions['isOverlayRequired'],
            'isCreateSearchablePdf' => $ocrOptions['isCreateSearchablePdf'],
            'isSearchablePdfHideTextLayer' => $ocrOptions['isSearchablePdfHideTextLayer'],
            'scale' => $ocrOptions['scale'],
            'detectOrientation' => $ocrOptions['detectOrientation'],
            'OCREngine' => $ocrOptions['OCREngine']
        ];
        
        // Add file to request
        if (class_exists('CURLFile')) {
            $postData['file'] = new \CURLFile($imagePath);
        } else {
            $postData['file'] = '@' . $imagePath;
        }
        
        // Send request to OCR.space
        $startTime = microtime(true);
        $response = $this->sendRequest($postData);
        $processingTime = microtime(true) - $startTime;
        
        $this->log("OCR completed in " . round($processingTime, 2) . "s");
        
        // Parse response
        $result = $this->parseResponse($response);
        $result['processing_time'] = $processingTime;
        $result['image'] = basename($imagePath);
        
        return $result;
    }
    
    /**
     * Send request to OCR.space API
     */
    private function sendRequest($postData) {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $this->apiEndpoint);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, OCR_SPACE_TIMEOUT);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_USERAGENT, 'INFIKNIGHT-Predictor/2.0');
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("CURL Error: $error");
        }
        
        if ($httpCode !== 200) {
            throw new \Exception("OCR.space API returned HTTP $httpCode");
        }
        
        $this->lastResponse = $response;
        return json_decode($response, true);
    }
    
    /**
     * Parse OCR.space response and extract text
     */
    private function parseResponse($response) {
        $result = [
            'success' => false,
            'text' => '',
            'confidence' => 0,
            'pages' => 0,
            'error' => null,
            'raw_response' => $this->debugMode ? $response : null
        ];
        
        if (!isset($response['ParsedResults'])) {
            $errorMessage = $response['ErrorMessage'][0] ?? 'Unknown OCR error';
            $result['error'] = $errorMessage;
            $this->log("OCR Error: $errorMessage", 'error');
            return $result;
        }
        
        $parsedResults = $response['ParsedResults'];
        $result['pages'] = count($parsedResults);
        
        $allText = [];
        $totalConfidence = 0;
        $validResults = 0;
        
        foreach ($parsedResults as $page) {
            if (isset($page['ParsedText'])) {
                $text = $page['ParsedText'];
                $allText[] = $text;
                
                if (isset($page['TextOverlay']['Lines'])) {
                    $confidence = $this->calculateConfidence($page);
                    $totalConfidence += $confidence;
                    $validResults++;
                }
            }
        }
        
        $result['success'] = true;
        $result['text'] = implode("\n", $allText);
        $result['confidence'] = $validResults > 0 ? 
            round($totalConfidence / $validResults, 2) : 70; // Default confidence
        
        $this->log("Extracted " . strlen($result['text']) . " characters with " . 
                   $result['confidence'] . "% confidence");
        
        return $result;
    }
    
    /**
     * Calculate confidence from TextOverlay
     */
    private function calculateConfidence($page) {
        if (!isset($page['TextOverlay']['Lines'])) {
            return 70;
        }
        
        $totalWordConfidence = 0;
        $wordCount = 0;
        
        foreach ($page['TextOverlay']['Lines'] as $line) {
            if (isset($line['Words'])) {
                foreach ($line['Words'] as $word) {
                    if (isset($word['WordText'])) {
                        $wordCount++;
                    }
                }
            }
        }
        
        return $wordCount > 0 ? min(95, 70 + ($wordCount / 10)) : 70;
    }
    
    /**
     * Extract PUBG stats from OCR text
     */
    public function extractStats($ocrText) {
        $this->log("Extracting statistics from OCR text...");
        
        $stats = [];
        $extractedValues = [];
        
        // Clean text
        $text = preg_replace('/\s+/', ' ', $ocrText);
        $text = str_replace(["\n", "\r", "\t"], ' ', $text);
        
        // Extract each stat using patterns
        foreach (OCRConfig::$statPatterns as $statName => $config) {
            foreach ($config['patterns'] as $pattern) {
                if (preg_match($pattern, $text, $matches)) {
                    $value = $matches[1];
                    
                    // Convert to proper type
                    if ($config['type'] === 'float') {
                        $value = floatval($value);
                    } elseif ($config['type'] === 'integer') {
                        $value = intval($value);
                    } elseif ($config['type'] === 'percentage') {
                        $value = floatval($value);
                    }
                    
                    // Validate range
                    if ($value >= $config['min'] && $value <= $config['max']) {
                        $stats[$statName] = $value;
                        $extractedValues[] = $statName;
                        $this->log("Extracted $statName: $value");
                        break;
                    }
                }
            }
        }
        
        // Calculate derived stats
        if (!isset($stats['kd_ratio']) && isset($stats['eliminations']) && isset($stats['matches_played'])) {
            $stats['kd_ratio'] = round($stats['eliminations'] / $stats['matches_played'], 2);
            $this->log("Derived K/D Ratio: {$stats['kd_ratio']}");
        }
        
        if (!isset($stats['win_ratio']) && isset($stats['wins']) && isset($stats['matches_played'])) {
            $stats['win_ratio'] = round(($stats['wins'] / $stats['matches_played']) * 100, 1);
            $this->log("Derived Win Ratio: {$stats['win_ratio']}%");
        }
        
        if (!isset($stats['headshot_rate']) && isset($stats['headshots']) && isset($stats['eliminations'])) {
            $stats['headshot_rate'] = round(($stats['headshots'] / $stats['eliminations']) * 100, 1);
            $this->log("Derived Headshot Rate: {$stats['headshot_rate']}%");
        }
        
        $stats['extracted_count'] = count($extractedValues);
        $stats['extracted_fields'] = $extractedValues;
        $stats['raw_text_preview'] = substr($text, 0, 500);
        
        return $stats;
    }
    
    /**
     * Calculate Power Score for player
     */
    public function calculatePowerScore($stats) {
        $weights = [
            'kd_ratio' => 0.35,
            'win_ratio' => 0.25,
            'top10_rate' => 0.15,
            'avg_damage' => 0.12,
            'headshot_rate' => 0.08,
            'accuracy' => 0.05
        ];
        
        $benchmarks = [
            'kd_ratio' => ['min' => 0.5, 'max' => 3.5],
            'win_ratio' => ['min' => 2, 'max' => 25],
            'top10_rate' => ['min' => 30, 'max' => 80],
            'avg_damage' => ['min' => 150, 'max' => 450],
            'headshot_rate' => ['min' => 5, 'max' => 30],
            'accuracy' => ['min' => 5, 'max' => 25]
        ];
        
        $score = 0;
        $totalWeight = 0;
        
        foreach ($weights as $metric => $weight) {
            if (isset($stats[$metric])) {
                $value = $stats[$metric];
                $benchmark = $benchmarks[$metric];
                
                // Clamp to benchmark range
                $value = max($benchmark['min'], min($benchmark['max'], $value));
                
                // Normalize to 0-1
                $normalized = ($value - $benchmark['min']) / 
                             ($benchmark['max'] - $benchmark['min']);
                
                $score += $normalized * $weight;
                $totalWeight += $weight;
            }
        }
        
        return $totalWeight > 0 ? round(($score / $totalWeight) * 100, 1) : 0;
    }
    
    /**
     * Get file type for OCR.space
     */
    private function getFileType($filePath) {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                return 'JPG';
            case 'png':
                return 'PNG';
            case 'gif':
                return 'GIF';
            case 'bmp':
                return 'BMP';
            case 'tif':
            case 'tiff':
                return 'TIF';
            case 'pdf':
                return 'PDF';
            default:
                return 'JPG';
        }
    }
    
    /**
     * Validate OCR result
     */
    public function validateStats($stats) {
        $required = ['kd_ratio', 'win_ratio', 'top10_rate', 'avg_damage'];
        $missing = [];
        
        foreach ($required as $field) {
            if (!isset($stats[$field])) {
                $missing[] = $field;
            }
        }
        
        return [
            'valid' => count($missing) === 0,
            'missing' => $missing,
            'complete_percentage' => round((1 - (count($missing) / count($required))) * 100, 1)
        ];
    }
    
    /**
     * Log message
     */
    private function log($message, $level = 'info') {
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message";
        $this->processingLog[] = $logEntry;
        
        if ($this->debugMode) {
            error_log("INFIKNIGHT_OCR: $logEntry");
        }
    }
    
    /**
     * Get processing log
     */
    public function getLog() {
        return $this->processingLog;
    }
    
    /**
     * Get last API response
     */
    public function getLastResponse() {
        return $this->lastResponse;
    }
}
?>
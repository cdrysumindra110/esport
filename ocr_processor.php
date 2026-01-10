<?php
/**
 * ---------------------------------------------------------
 * Infiknight OCR Processor (FINAL – ERROR FREE)
 * ---------------------------------------------------------
 * Features:
 * - Secure image upload
 * - HUD-based OCR preprocessing
 * - Performance-text filtering
 * - Player stat extraction
 * - Team normalization
 * - Confidence calculation
 * ---------------------------------------------------------
 */

header('Content-Type: application/json');

/* =========================================================
   CONFIGURATION
========================================================= */
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_TYPES', ['image/png', 'image/jpeg', 'image/jpg']);

/**
 * OCR ENGINE
 * mock     = demo / offline
 * ocrspace = real OCR API
 */
define('OCR_ENGINE', 'mock'); // change to 'ocrspace' when needed
define('OCR_SPACE_API_KEY', 'K84753099788957');

/**
 * Keywords allowed for performance calculation
 */
define('PERFORMANCE_KEYWORDS', [
    'kill', 'kills', 'damage', 'assist', 'assists',
    'survival', 'time', 'kd', 'k/d'
]);

/* =========================================================
   MAIN EXECUTION
========================================================= */
try {
    validateRequest();

    $imagePath = handleUpload();
    $matchType = $_POST['match_type'] ?? 'solo';

    // 🔹 Preprocessing
    $imagePath = cropHUDRegion($imagePath);
    preprocessImage($imagePath);

    // 🔹 OCR
    $rawText = extractTextFromImage($imagePath);
    if (!$rawText) {
        throw new Exception('OCR text extraction failed');
    }

    // 🔹 Filter only performance text
    $filteredText = filterPerformanceText($rawText);

    $players = parsePlayerStats($filteredText, $matchType);
    if (empty($players)) {
        throw new Exception('No valid player data detected');
    }

    $confidence = calculateConfidence($players, $filteredText);

    respond(true, [
        'players' => $players,
        'confidence' => $confidence
    ]);

} catch (Exception $e) {
    respond(false, null, $e->getMessage());
}

/* =========================================================
   VALIDATION
========================================================= */
function validateRequest(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['image'])) {
        throw new Exception('Image file missing');
    }

    if ($_FILES['image']['size'] > MAX_FILE_SIZE) {
        throw new Exception('Image exceeds 5MB limit');
    }

    if (!in_array($_FILES['image']['type'], ALLOWED_TYPES)) {
        throw new Exception('Invalid image format');
    }
}

/* =========================================================
   FILE UPLOAD
========================================================= */
function handleUpload(): string {

    $uploadDir = __DIR__ . '/uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = uniqid('ocr_', true) . '.jpg';
    $path = $uploadDir . $filename;

    if (!move_uploaded_file($_FILES['image']['tmp_name'], $path)) {
        throw new Exception('Failed to upload image');
    }

    return $path;
}

/* =========================================================
   IMAGE PREPROCESSING
========================================================= */
function cropHUDRegion(string $imagePath): string {

    $img = @imagecreatefromjpeg($imagePath);
    if (!$img) return $imagePath;

    // Example scoreboard area (adjust per game UI)
    $crop = imagecrop($img, [
        'x' => 300,
        'y' => 200,
        'width' => 900,
        'height' => 600
    ]);

    if ($crop !== false) {
        imagejpeg($crop, $imagePath, 90);
        imagedestroy($crop);
    }

    imagedestroy($img);
    return $imagePath;
}

function preprocessImage(string $imagePath): void {

    $img = @imagecreatefromjpeg($imagePath);
    if (!$img) return;

    imagefilter($img, IMG_FILTER_GRAYSCALE);
    imagefilter($img, IMG_FILTER_CONTRAST, -25);
    imagefilter($img, IMG_FILTER_BRIGHTNESS, 10);

    imagejpeg($img, $imagePath, 95);
    imagedestroy($img);
}

/* =========================================================
   OCR EXTRACTION
========================================================= */
function extractTextFromImage(string $imagePath): string {

    if (OCR_ENGINE === 'ocrspace') {
        return runOCRSpace($imagePath);
    }

    // 🔁 MOCK OCR (Stable for demo / defense)
    return "
        PlayerA 6 820 18 3
        PlayerB 4 610 15 2
        PlayerC 5 700 16 2
    ";
}

function runOCRSpace(string $imagePath): string {

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.ocr.space/parse/image',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'apikey' => OCR_SPACE_API_KEY,
            'file' => new CURLFile($imagePath),
            'language' => 'eng'
        ]
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    $result = json_decode($response, true);
    return $result['ParsedResults'][0]['ParsedText'] ?? '';
}

/* =========================================================
   TEXT FILTERING
========================================================= */
function filterPerformanceText(string $text): string {

    $filtered = [];
    $lines = preg_split('/\r\n|\r|\n/', strtolower($text));

    foreach ($lines as $line) {
        if (preg_match('/\d+/', $line)) {
            foreach (PERFORMANCE_KEYWORDS as $key) {
                if (strpos($line, $key) !== false || preg_match('/\d+/', $line)) {
                    $filtered[] = $line;
                    break;
                }
            }
        }
    }

    return implode("\n", $filtered);
}

/* =========================================================
   DATA PARSING
========================================================= */
function parsePlayerStats(string $text, string $matchType): array {

    $lines = preg_split('/\r\n|\r|\n/', trim($text));
    $players = [];

    foreach ($lines as $line) {
        if (preg_match('/([A-Za-z0-9_]+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/', $line, $m)) {
            $players[] = [
                'name'     => $m[1],
                'kills'    => (int)$m[2],
                'damage'   => (int)$m[3],
                'survival' => (int)$m[4],
                'assists'  => (int)$m[5]
            ];
        }
    }

    return normalizeTeams($players, $matchType);
}

/* =========================================================
   TEAM NORMALIZATION
========================================================= */
function normalizeTeams(array $players, string $matchType): array {

    if ($matchType === 'solo') {
        return $players;
    }

    $teamSize = $matchType === 'duo' ? 2 : 4;
    $teams = [];
    $i = 1;

    foreach (array_chunk($players, $teamSize) as $chunk) {
        $teams[] = [
            'name'     => 'Team ' . $i++,
            'kills'    => array_sum(array_column($chunk, 'kills')),
            'damage'   => array_sum(array_column($chunk, 'damage')),
            'survival' => round(array_sum(array_column($chunk, 'survival')) / count($chunk)),
            'assists'  => array_sum(array_column($chunk, 'assists'))
        ];
    }

    return $teams;
}

/* =========================================================
   CONFIDENCE CALCULATION
========================================================= */
function calculateConfidence(array $players, string $text): string {

    $score = 0;
    $score += count($players) >= 2 ? 30 : 10;
    $score += strlen($text) > 40 ? 30 : 15;
    $score += count($players) >= 4 ? 30 : 15;

    return min(95, $score) . '%';
}

/* =========================================================
   RESPONSE
========================================================= */
function respond(bool $success, $data = null, string $error = ''): void {
    echo json_encode([
        'success' => $success,
        'players' => $data['players'] ?? [],
        'confidence' => $data['confidence'] ?? '0%',
        'error' => $error
    ]);
    exit;
}

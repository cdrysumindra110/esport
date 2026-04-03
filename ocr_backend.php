<?php
// ocr_backend.php - FIXED AND IMPROVED OCR PROCESSING
header('Content-Type: application/json');

// Turn on error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set maximum execution time
set_time_limit(60);

// Simple function to return JSON
function jsonResponse($success, $message, $data = null, $error = null) {
    $response = [
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'error' => $error,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    if (!$success && $error) {
        http_response_code(400);
    }
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// ============================================
// SECURITY CHECKS
// ============================================

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Invalid request method', null, 'Use POST method');
}

include_once('config.php');
// Validate CSRF token if session exists
if (!empty($_SESSION['csrf_token'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        jsonResponse(false, 'CSRF token validation failed', null, 'Security validation failed');
    }
}

// Check if file was uploaded
if (!isset($_FILES['statsFile'])) {
    jsonResponse(false, 'No file uploaded', null, 'Please select an image file');
}

$file = $_FILES['statsFile'];

// ============================================
// FILE VALIDATION
// ============================================

// Check for upload errors
if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
    ];
    
    $errorMsg = $uploadErrors[$file['error']] ?? 'Unknown upload error';
    jsonResponse(false, 'Upload failed', null, $errorMsg);
}

// Check file type using multiple methods
$allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'webp'];
$fileName = strtolower($file['name']);
$fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

if (!in_array($fileExtension, $allowedExtensions)) {
    jsonResponse(false, 'Invalid file type', null, "File extension '$fileExtension' not allowed. Use PNG, JPEG, JPG, GIF, BMP, or WEBP.");
}

// Verify MIME type
$allowedMimeTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif', 'image/bmp', 'image/webp'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedMimeTypes)) {
    jsonResponse(false, 'Invalid file type', null, "File MIME type '$mimeType' not allowed.");
}

// Check file size (10MB max)
$maxFileSize = 10 * 1024 * 1024; // 10MB
if ($file['size'] > $maxFileSize) {
    jsonResponse(false, 'File too large', null, 'Maximum file size is 10MB');
}

// Check minimum file size (at least 1KB)
if ($file['size'] < 1024) {
    jsonResponse(false, 'File too small', null, 'File appears to be empty or corrupted');
}

// ============================================
// FILE UPLOAD PROCESSING
// ============================================

// Ensure uploads directory exists
$uploadDir = __DIR__ . '/uploads/';
if (!file_exists($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    jsonResponse(false, 'Server error', null, 'Cannot create upload directory');
}

// Add .htaccess to prevent direct access
$htaccessFile = $uploadDir . '.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "Order Deny,Allow\nDeny from all\n");
}

// Ensure directory is writable
if (!is_writable($uploadDir)) {
    jsonResponse(false, 'Server error', null, 'Upload directory not writable');
}

// Generate secure filename
$safeFilename = 'ocr_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $fileExtension;
$filepath = $uploadDir . $safeFilename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    jsonResponse(false, 'Failed to save file', null, 'File upload failed. Check directory permissions.');
}

// Verify the uploaded file is a valid image
if (!@getimagesize($filepath)) {
    unlink($filepath);
    jsonResponse(false, 'Invalid image file', null, 'The uploaded file is not a valid image');
}

// ============================================
// IMAGE PRE-PROCESSING FOR BETTER OCR
// ============================================

$preprocessedPath = preprocessImage($filepath, $fileExtension);
if ($preprocessedPath && $preprocessedPath !== $filepath) {
    unlink($filepath);
    $filepath = $preprocessedPath;
}

// ============================================
// OCR PROCESSING
// ============================================

$ocrResult = null;
$ocrMethods = ['tesseract_optimized', 'tesseract_basic', 'ocrspace', 'manual'];

foreach ($ocrMethods as $method) {
    if ($method === 'tesseract_optimized') {
        $ocrResult = processWithTesseractOptimized($filepath);
    } elseif ($method === 'tesseract_basic' && (!$ocrResult || !$ocrResult['success'])) {
        $ocrResult = processWithTesseractBasic($filepath);
    } elseif ($method === 'ocrspace' && (!$ocrResult || !$ocrResult['success'])) {
        $ocrResult = processWithOCRSpace($filepath);
    }
    
    if ($ocrResult && $ocrResult['success'] && $ocrResult['confidence'] > 50) {
        break;
    }
}

if (!$ocrResult || !$ocrResult['success']) {
    $ocrResult = analyzeImageManually($filepath);
}

if (isset($ocrResult['processed_path']) && $ocrResult['processed_path'] !== $filepath && file_exists($ocrResult['processed_path'])) {
    unlink($ocrResult['processed_path']);
}

// ============================================
// PREPARE RESPONSE
// ============================================

$finalResponse = [
    'success' => true,
    'message' => $ocrResult['message'],
    'data' => [
        'filename' => $safeFilename,
        'ocr_text' => $ocrResult['text'] ?? '',
        'extracted_data' => $ocrResult['extracted_data'] ?? [],
        'confidence' => $ocrResult['confidence'] ?? 0,
        'processing_time' => $ocrResult['processing_time'] ?? 0,
        'ocr_method' => $ocrResult['method'] ?? 'unknown',
        'characters_extracted' => strlen($ocrResult['text'] ?? ''),
        'numbers_found' => count($ocrResult['extracted_data']['career_stats']['extracted_numbers'] ?? [])
    ],
    'error' => null
];

echo json_encode($finalResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;

// ============================================
// FUNCTIONS
// ============================================

function preprocessImage($imagePath, $originalExtension) {
    if (!extension_loaded('gd')) return $imagePath;

    switch (strtolower($originalExtension)) {
        case 'jpg': case 'jpeg': $image = imagecreatefromjpeg($imagePath); break;
        case 'png': $image = imagecreatefrompng($imagePath); break;
        case 'gif': $image = imagecreatefromgif($imagePath); break;
        case 'bmp': $image = imagecreatefrombmp($imagePath); break;
        case 'webp': $image = imagecreatefromwebp($imagePath); break;
        default: return $imagePath;
    }

    if (!$image) return $imagePath;

    $width = imagesx($image);
    $height = imagesy($image);
    $processed = imagecreatetruecolor($width, $height);

    for ($x = 0; $x < $width; $x++) {
        for ($y = 0; $y < $height; $y++) {
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            $gray = (int)(0.299*$r + 0.587*$g + 0.114*$b);
            $gray = min(255, max(0, ($gray - 128) * 1.5 + 128));
            $color = imagecolorallocate($processed, $gray, $gray, $gray);
            imagesetpixel($processed, $x, $y, $color);
        }
    }

    $processedPath = preg_replace('/\.[^.]+$/', '_processed.jpg', $imagePath);
    imagejpeg($processed, $processedPath, 90);
    imagedestroy($image);
    imagedestroy($processed);

    return $processedPath;
}

// --- Tesseract Optimized ---
function processWithTesseractOptimized($imagePath) {
    $tesseractCheck = shell_exec('which tesseract 2>/dev/null');
    if (empty($tesseractCheck)) return ['success'=>false,'message'=>'Tesseract not installed'];

    $bestResult = null;
    $bestConfidence = 0;
    $psmModes = [6=>'Uniform block of text',3=>'Fully automatic',11=>'Sparse text',12=>'Sparse text with OSD'];

    foreach ($psmModes as $psm=>$desc) {
        $outputFile = tempnam(sys_get_temp_dir(),'ocr_');
        $command = "tesseract ".escapeshellarg($imagePath)." ".escapeshellarg($outputFile)." -l eng --psm $psm --oem 3 -c tessedit_char_whitelist=0123456789%.,KMkmKD/TopElimWinsDamageAvgAssistsAccuracyRatioHeadshot 2>&1";
        $startTime = microtime(true);
        @shell_exec($command);
        $processingTime = round(microtime(true)-$startTime,2);
        $text = file_exists($outputFile.'.txt') ? trim(file_get_contents($outputFile.'.txt')) : '';
        @unlink($outputFile.'.txt'); @unlink($outputFile);

        if (!empty($text)) {
            $parsedData = parseExtractedText($text);
            $confidence = calculateConfidence($text,$parsedData);
            if ($confidence > $bestConfidence) {
                $bestConfidence = $confidence;
                $bestResult = ['success'=>true,'message'=>"Text extracted using Tesseract (PSM $psm: $desc)",'text'=>$text,'extracted_data'=>$parsedData,'confidence'=>$confidence,'processing_time'=>$processingTime,'method'=>"tesseract_psm_$psm"];
            }
        }
    }

    return $bestResult ?? ['success'=>false,'message'=>'Tesseract returned no usable text','method'=>'tesseract_optimized'];
}

// --- Basic Tesseract ---
function processWithTesseractBasic($imagePath) {
    $outputFile = tempnam(sys_get_temp_dir(),'ocr_');
    $command = "tesseract ".escapeshellarg($imagePath)." ".escapeshellarg($outputFile)." -l eng 2>&1";
    $startTime = microtime(true);
    @shell_exec($command);
    $processingTime = round(microtime(true)-$startTime,2);
    $text = file_exists($outputFile.'.txt') ? trim(file_get_contents($outputFile.'.txt')) : '';
    @unlink($outputFile.'.txt'); @unlink($outputFile);

    if (empty($text)) return ['success'=>false,'message'=>'Tesseract returned empty text'];
    $parsedData = parseExtractedText($text);
    $confidence = calculateConfidence($text,$parsedData);
    return ['success'=>true,'message'=>'Text extracted using basic Tesseract','text'=>$text,'extracted_data'=>$parsedData,'confidence'=>$confidence,'processing_time'=>$processingTime,'method'=>'tesseract_basic'];
}

// --- OCR.space ---
function processWithOCRSpace($imagePath) {
    $apiKey = 'K87805488957';
    $imageData = file_get_contents($imagePath);
    if (!$imageData) return ['success'=>false,'message'=>'Cannot read image file'];

    $base64Image = base64_encode($imageData);
    $payload = json_encode(['base64Image'=>"data:image/jpeg;base64,".$base64Image,'language'=>'eng','isOverlayRequired'=>false,'OCREngine'=>2,'scale'=>true,'detectOrientation'=>true,'isTable'=>true,'isCreateSearchablePdf'=>false,'isSearchablePdfHideTextLayer'=>false]);

    $ch = curl_init();
    curl_setopt_array($ch,[
        CURLOPT_URL=>"https://api.ocr.space/parse/image",
        CURLOPT_RETURNTRANSFER=>true,
        CURLOPT_POST=>true,
        CURLOPT_TIMEOUT=>30,
        CURLOPT_HTTPHEADER=>['Content-Type: application/json','apikey: '.$apiKey],
        CURLOPT_POSTFIELDS=>$payload
    ]);

    $startTime = microtime(true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch,CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $processingTime = round(microtime(true)-$startTime,2);

    if ($error) return ['success'=>false,'message'=>'OCR API connection failed','error'=>$error];

    $result = json_decode($response,true);
    if (!isset($result['ParsedResults'][0]['ParsedText'])) {
        $errorMsg = $result['ErrorMessage'] ?? 'Unknown OCR error';
        return ['success'=>false,'message'=>'OCR API returned no text','error'=>$errorMsg];
    }

    $rawText = trim($result['ParsedResults'][0]['ParsedText']);
    $parsedData = parseExtractedText($rawText);
    $confidence = calculateConfidence($rawText,$parsedData);
    return ['success'=>true,'message'=>'Text extracted using OCR.space API','text'=>$rawText,'extracted_data'=>$parsedData,'confidence'=>$confidence,'processing_time'=>$processingTime,'method'=>'ocrspace'];
}

// --- Manual Fallback ---
function analyzeImageManually($imagePath) {
    $imageInfo = @getimagesize($imagePath);
    if (!$imageInfo) return ['success'=>false,'message'=>'Cannot read image file'];

    $text = "Image Analysis (OCR Failed)\nDimensions: {$imageInfo[0]}x{$imageInfo[1]}\nFile size: ".round(filesize($imagePath)/1024,2)." KB\n";
    $parsedData = ['career_stats'=>['matches_played'=>0,'wins'=>0,'kd_ratio'=>0,'total_damage'=>0,'accuracy'=>'0%','extraction_method'=>'manual_fallback','error'=>'OCR processing failed'],'image_info'=>['width'=>$imageInfo[0],'height'=>$imageInfo[1]]];

    return ['success'=>true,'message'=>'Basic image analysis (OCR unavailable)','text'=>$text,'extracted_data'=>$parsedData,'confidence'=>10,'processing_time'=>0.1,'method'=>'manual_analysis'];
}

// --- Parsing and utilities ---
function parseExtractedText($text) {
    $stats=[];
    $text = str_replace(["\r","\t"],' ',$text); // keep \n for splitting
    $lines = explode("\n",$text);
    $textCleaned = preg_replace('/\s+/',' ',$text);

    $patterns=[
        'matches_played'=>'/(matches|played|total\s+games?|battles?)\D*?(\d[\d,]*)/i',
        'wins'=>'/(wins|victories?|booyah|chicken\s+dinner)\D*?(\d[\d,]*)/i',
        'top10'=>'/(top\s*10|top.*?ten|top\s*\d+)\D*?(\d[\d,]*)/i',
        'eliminations'=>'/(eliminations?|kills?|frags?)\D*?(\d[\d,]*)/i',
        'kd_ratio'=>'/(k\/d|kd|kill.*?death|ratio)\D*?(\d[\d,]*\.?\d*)/i',
        'total_damage'=>'/(total.*?damage|damage.*?total|damage\s+dealt)\D*?(\d[\d,]*)/i',
        'headshots'=>'/(headshots?|hs)\D*?(\d[\d,]*)/i',
        'headshot_rate'=>'/(headshot.*?rate|hs.*?rate|headshot\s*%)\D*?(\d[\d,]*\.?\d*%?)/i',
        'avg_damage'=>'/(avg.*?damage|average.*?damage|damage.*?avg)\D*?(\d[\d,]*)/i',
        'accuracy'=>'/(accuracy|acc|hit\s*rate)\D*?(\d[\d,]*\.?\d*%?)/i',
        'assists'=>'/(assists?|helps?)\D*?(\d[\d,]*)/i',
        'most_eliminations'=>'/(most.*?eliminations?|most.*?kills?|highest.*?kills?)\D*?(\d[\d,]*)/i',
        'highest_damage'=>'/(highest.*?damage|most.*?damage|max.*?damage)\D*?(\d[\d,]*)/i',
        'win_rate'=>'/(win.*?rate|wr|winning\s*%)\D*?(\d[\d,]*\.?\d*%?)/i',
        'large_numbers'=>'/\b(\d{3,6})\b/'
    ];

    foreach ($patterns as $key=>$pattern) {
        if (preg_match_all($pattern,$text,$matches,PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (isset($match[2])) {
                    $value = cleanNumber($match[2]);
                    if ($value>0) {
                        if (!isset($stats[$key])) $stats[$key]=$value;
                        elseif (is_array($stats[$key])) $stats[$key][]=$value;
                        else $stats[$key]=[$stats[$key],$value];
                    }
                }
            }
        }
    }

    preg_match_all('/\b(\d{1,7}(?:,\d{3})*(?:\.\d+)?%?)\b/',$text,$allNumbers);
    if (!empty($allNumbers[1])) {
        $stats['extracted_numbers']=array_filter(array_map('cleanNumber',$allNumbers[1]),fn($n)=>$n>0 && $n<10000000);
    }

    if (!isset($stats['matches_played']) && isset($stats['extracted_numbers'])) {
        $filtered=array_filter($stats['extracted_numbers'],fn($n)=>$n>=100 && $n<=20000);
        if (!empty($filtered)) $stats['matches_played']=max($filtered);
    }

    return [
        'career_stats'=>$stats,
        'game_type'=>identifyGameType($text),
        'extraction_method'=>'ocr_parsing',
        'raw_text_preview'=>substr($textCleaned,0,300).(strlen($textCleaned)>300?'...':''),
        'total_characters'=>strlen($textCleaned),
        'numbers_found'=>count($stats['extracted_numbers'] ?? [])
    ];
}

function calculateConfidence($text,$parsedData) {
    $confidence=30;
    $textLength=strlen($text);
    if($textLength>100)$confidence+=10;
    if($textLength>300)$confidence+=10;
    if($textLength>500)$confidence+=10;
    $numbers=$parsedData['career_stats']['extracted_numbers']??[];
    $numCount=count($numbers);
    if($numCount>5)$confidence+=10;
    if($numCount>10)$confidence+=10;
    if($numCount>15)$confidence+=10;
    $keyStats=['matches_played','wins','eliminations','kd_ratio'];
    $foundCount=0;
    foreach($keyStats as $stat) if(isset($parsedData['career_stats'][$stat])&&$parsedData['career_stats'][$stat]>0)$foundCount++;
    $confidence+=$foundCount*5;
    if(($parsedData['game_type']??'unknown')!=='unknown')$confidence+=5;
    return min(95,$confidence);
}

function identifyGameType($text) {
    $text=strtolower($text);
    if(preg_match('/(pubg|player.*?unknown|battleground)/i',$text)) return 'PUBG';
    elseif(preg_match('/(free.?fire|ff)/i',$text)) return 'Free Fire';
    elseif(preg_match('/(call of duty|cod|warzone)/i',$text)) return 'Call of Duty';
    elseif(preg_match('/(apex|legends)/i',$text)) return 'Apex Legends';
    elseif(preg_match('/(fortnite|fn)/i',$text)) return 'Fortnite';
    elseif(preg_match('/(valorant|val)/i',$text)) return 'Valorant';
    return 'unknown';
}

function cleanNumber($number){
    if(empty($number))return 0;
    $clean=str_replace(',','',preg_replace('/[^\d\.%]/','',$number));
    if(strpos($number,'%')!==false)return floatval($clean);
    if(strpos($clean,'.')!==false)return floatval($clean);
    $intVal=intval($clean);
    if($intVal>100000000)return 0;
    return $intVal;
}
?>

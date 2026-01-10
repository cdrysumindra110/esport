<?php
// lib/Security.php - Security Implementation
class SecurityHandler {
    
    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        // Remove HTML tags
        $input = strip_tags($input);
        
        // Convert special characters
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        // Remove non-printable characters
        $input = preg_replace('/[\x00-\x1F\x7F]/u', '', $input);
        
        // Trim whitespace
        $input = trim($input);
        
        return $input;
    }
    
    public function validateFileUpload($file) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'File upload failed. Error code: ' . ($file['error'] ?? 'unknown');
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit';
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mime, ALLOWED_FILE_TYPES)) {
            $errors[] = 'Invalid file type. Only PNG/JPEG images are allowed';
        }
        
        // Check file extension
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['png', 'jpg', 'jpeg'])) {
            $errors[] = 'Invalid file extension';
        }
        
        // Check for malicious content
        if ($this->containsMaliciousContent($file['tmp_name'])) {
            $errors[] = 'File contains potentially malicious content';
        }
        
        return empty($errors) ? true : $errors;
    }
    
    private function containsMaliciousContent($filePath) {
        // Check for PHP tags in image
        $content = file_get_contents($filePath);
        
        // Look for PHP tags or scripts
        if (preg_match('/<\?php|<\?=|script\s*=/i', $content)) {
            return true;
        }
        
        // Check for null bytes
        if (strpos($content, "\x00") !== false) {
            return true;
        }
        
        return false;
    }
    
    public function generateCSRFToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $_SESSION['csrf_token_time'] = time();
        }
        return $_SESSION['csrf_token'];
    }
    
    public function validateCSRFToken($token) {
        if (!isset($_SESSION['csrf_token']) || !isset($_SESSION['csrf_token_time'])) {
            return false;
        }
        
        // Token expires after 1 hour
        if (time() - $_SESSION['csrf_token_time'] > 3600) {
            unset($_SESSION['csrf_token'], $_SESSION['csrf_token_time']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    public function preventSQLInjection($string) {
        global $conn;
        return $conn->real_escape_string($string);
    }
    
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public function validateNumber($number, $min = null, $max = null) {
        if (!is_numeric($number)) return false;
        
        if ($min !== null && $number < $min) return false;
        if ($max !== null && $number > $max) return false;
        
        return true;
    }
    
    public function logSecurityEvent($event, $details = []) {
        $logEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'details' => $details
        ];
        
        $logFile = __DIR__ . '/../logs/security.log';
        file_put_contents($logFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
    }
}
?>
<?php
// lib/Security.php

class Security {
    private static $instance = null;
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Generate CSRF token
     */
    public function generateCSRFToken() {
        if (!session_id()) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        $_SESSION['csrf_token'] = $token;
        $_SESSION['csrf_token_time'] = time();
        
        return $token;
    }
    
    /**
     * Validate CSRF token
     */
    public function validateCSRFToken($token) {
        if (!session_id()) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Token expires after 2 hours
        if (time() - $_SESSION['csrf_token_time'] > 7200) {
            unset($_SESSION['csrf_token']);
            return false;
        }
        
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize user input
     */
    public function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([$this, 'sanitizeInput'], $input);
        }
        
        $input = trim($input);
        $input = strip_tags($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        
        return $input;
    }
    
    /**
     * Validate file upload
     */
    public function validateFileUpload($file) {
        $errors = [];
        
        // Check if file was uploaded
        if (!isset($file) || !is_array($file)) {
            $errors[] = 'No file uploaded';
            return $errors;
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            switch ($file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errors[] = 'File size exceeds limit';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errors[] = 'File was only partially uploaded';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errors[] = 'No file was uploaded';
                    break;
                default:
                    $errors[] = 'Upload failed with error code: ' . $file['error'];
            }
            return $errors;
        }
        
        // Check file size
        if ($file['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size exceeds ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB limit';
        }
        
        // Check file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, ALLOWED_EXTENSIONS)) {
            $errors[] = 'Invalid file type. Allowed: ' . implode(', ', ALLOWED_EXTENSIONS);
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($mimeType, $allowedMimes)) {
            $errors[] = 'Invalid file MIME type';
        }
        
        // Scan for malware (basic check)
        $content = file_get_contents($file['tmp_name']);
        $suspicious = ['<?php', '<?=', '<script', 'eval(', 'base64_decode'];
        foreach ($suspicious as $pattern) {
            if (stripos($content, $pattern) !== false) {
                $errors[] = 'File contains suspicious content';
                break;
            }
        }
        
        return empty($errors) ? true : $errors;
    }
    
    /**
     * Generate image hash for caching
     */
    public function generateImageHash($filepath) {
        if (file_exists($filepath)) {
            return hash_file('sha256', $filepath);
        }
        return null;
    }
    
    /**
     * Rate limiting
     */
    public function checkRateLimit($ip, $limit = 60, $window = 3600) {
        $key = 'rate_limit_' . $ip;
        $current = $_SESSION[$key] ?? ['count' => 0, 'reset' => time() + $window];
        
        if ($current['reset'] < time()) {
            $current = ['count' => 0, 'reset' => time() + $window];
        }
        
        $current['count']++;
        $_SESSION[$key] = $current;
        
        if ($current['count'] > $limit) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Secure headers
     */
    public function setSecureHeaders() {
        header('X-Content-Type-Options: nosniff');
        header('X-Frame-Options: DENY');
        header('X-XSS-Protection: 1; mode=block');
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        if (APP_ENV === 'production') {
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('Content-Security-Policy: default-src \'self\'');
        }
    }
}

// Global security instance
$security = Security::getInstance();
$security->setSecureHeaders();
?>
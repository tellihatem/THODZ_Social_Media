<?php
/**
 * Security Helper Class
 * Provides secure password hashing, CSRF protection, and input sanitization
 */

class Security {
    
    /**
     * Hash password using bcrypt (secure)
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password against hash
     */
    public static function verifyPassword($password, $hash) {
        // Support legacy MD5 passwords during migration
        if (strpos($hash, 'THODZ') === 0) {
            // Legacy format: THODZ + md5(password)
            return $hash === 'THODZ' . md5($password);
        }
        return password_verify($password, $hash);
    }
    
    /**
     * Check if password needs rehashing (migration from MD5)
     */
    public static function needsRehash($hash) {
        // Legacy MD5 hashes start with 'THODZ'
        if (strpos($hash, 'THODZ') === 0) {
            return true;
        }
        return password_needs_rehash($hash, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyCSRFToken($token) {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input) {
        if (is_null($input)) return '';
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Sanitize email
     */
    public static function sanitizeEmail($email) {
        return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
    }
    
    /**
     * Validate email format
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Sanitize integer
     */
    public static function sanitizeInt($input) {
        return filter_var($input, FILTER_VALIDATE_INT) !== false ? (int)$input : 0;
    }
    
    /**
     * Validate string length
     */
    public static function isValidLength($string, $min, $max) {
        $len = mb_strlen($string, 'UTF-8');
        return $len >= $min && $len <= $max;
    }
    
    /**
     * Validate alphanumeric string
     */
    public static function isAlphanumeric($string) {
        return ctype_alnum($string);
    }
    
    /**
     * Set secure session parameters
     */
    public static function secureSession() {
        if (session_status() == PHP_SESSION_NONE) {
            // Set secure session parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            ini_set('session.cookie_samesite', 'Strict');
            
            // Use secure cookies if HTTPS
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
        }
        
        // Regenerate session ID periodically to prevent fixation
        if (!isset($_SESSION['created'])) {
            $_SESSION['created'] = time();
        } else if (time() - $_SESSION['created'] > 1800) {
            // Session started more than 30 minutes ago
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }
    
    /**
     * Validate image upload
     */
    public static function validateImageUpload($file, $maxSize = 7340032) { // 7MB default
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large'];
        }
        
        // Verify it's actually an image using getimagesize
        $imageInfo = @getimagesize($file['tmp_name']);
        if ($imageInfo === false) {
            return ['valid' => false, 'error' => 'Invalid image file'];
        }
        
        // Only allow JPEG, PNG, GIF
        $allowedTypes = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF];
        if (!in_array($imageInfo[2], $allowedTypes)) {
            return ['valid' => false, 'error' => 'Only JPEG, PNG, and GIF images are allowed'];
        }
        
        // Check MIME type matches
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif'];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if (!in_array($mimeType, $allowedMimes)) {
            return ['valid' => false, 'error' => 'Invalid MIME type'];
        }
        
        return ['valid' => true, 'type' => $imageInfo[2], 'mime' => $mimeType];
    }
    
    /**
     * Generate secure random filename
     */
    public static function generateSecureFilename($extension = 'jpg') {
        return 'THODZ_' . bin2hex(random_bytes(16)) . '.' . $extension;
    }
    
    /**
     * Validate PDF upload
     */
    public static function validatePdfUpload($file, $maxSize = 10485760) { // 10MB default
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No file uploaded'];
        }
        
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'error' => 'Upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'error' => 'File too large (max 10MB)'];
        }
        
        // Check MIME type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        if ($mimeType !== 'application/pdf') {
            return ['valid' => false, 'error' => 'Only PDF files are allowed'];
        }
        
        // Verify PDF header (magic bytes)
        $handle = fopen($file['tmp_name'], 'rb');
        $header = fread($handle, 5);
        fclose($handle);
        if ($header !== '%PDF-') {
            return ['valid' => false, 'error' => 'Invalid PDF file'];
        }
        
        return ['valid' => true, 'mime' => $mimeType];
    }
    
    /**
     * Validate file upload (image or PDF)
     */
    public static function validateFileUpload($file, $maxSize = 10485760) {
        if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
            return ['valid' => false, 'error' => 'No file uploaded', 'type' => null];
        }
        
        // Check MIME type to determine file type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);
        
        if ($mimeType === 'application/pdf') {
            $result = self::validatePdfUpload($file, $maxSize);
            $result['filetype'] = 'pdf';
            return $result;
        } elseif (in_array($mimeType, ['image/jpeg', 'image/png', 'image/gif'])) {
            $result = self::validateImageUpload($file, $maxSize);
            $result['filetype'] = 'image';
            return $result;
        }
        
        return ['valid' => false, 'error' => 'Only images (JPEG, PNG, GIF) and PDF files are allowed', 'filetype' => null];
    }
    
    /**
     * Convert URLs in text to clickable links
     */
    public static function makeLinksClickable($text) {
        // Pattern to match URLs
        $pattern = '/(https?:\/\/[^\s<>"\']+)/i';
        
        // Replace URLs with anchor tags
        $replacement = '<a href="$1" target="_blank" rel="noopener noreferrer" class="post-link">$1</a>';
        
        return preg_replace($pattern, $replacement, $text);
    }
}

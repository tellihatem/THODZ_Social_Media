<?php
/**
 * THODZ Configuration File
 * Update these settings for your environment
 */

// Base URL - auto-detect protocol and host, or use environment variable
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
    ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
define('BASE_URL', getenv('BASE_URL') ?: "{$protocol}://{$host}");

// Database settings
// For Koyeb (PostgreSQL): Set DATABASE_URL or individual DATABASE_* env vars
// For local Docker (MySQL): Uses defaults below
define('DB_DRIVER', getenv('DATABASE_DRIVER') ?: (getenv('DATABASE_URL') ? 'pgsql' : 'mysql'));
define('DB_HOST', getenv('DATABASE_HOST') ?: (getenv('DB_HOST') ?: 'db'));
define('DB_NAME', getenv('DATABASE_NAME') ?: (getenv('DB_NAME') ?: 'THODZ'));
define('DB_USER', getenv('DATABASE_USER') ?: (getenv('DB_USER') ?: 'root'));
define('DB_PASS', getenv('DATABASE_PASSWORD') ?: (getenv('DB_PASS') ?: ''));
define('DB_PORT', getenv('DATABASE_PORT') ?: (DB_DRIVER === 'pgsql' ? '5432' : '3306'));

// Email settings (Gmail SMTP)
// Credentials are loaded from environment variables for security
// Set these in your .env file or Docker environment
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM', getenv('SMTP_FROM') ?: getenv('SMTP_USER') ?: '');

// Upload settings
define('MAX_UPLOAD_SIZE', 7 * 1024 * 1024); // 7MB
define('UPLOAD_DIR', 'uploads');

// Session settings
define('SESSION_LIFETIME', 3600); // 1 hour

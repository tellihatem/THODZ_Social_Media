<?php
/**
 * THODZ Configuration File
 * Update these settings for your environment
 */

// Base URL - change this for production
define('BASE_URL', 'http://localhost:8080');

// Database settings (used by Docker, can be overridden)
define('DB_HOST', getenv('DB_HOST') ?: 'db');
define('DB_NAME', getenv('DB_NAME') ?: 'thodz');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

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

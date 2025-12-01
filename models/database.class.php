<?php 

require_once __DIR__ . '/../config.php';

class Database {

	private static $instance = null;

	function connect() {
		if (self::$instance !== null) {
			return self::$instance;
		}

		$conn = null;
		try {
			$driver = defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
			$host = defined('DB_HOST') ? DB_HOST : 'db';
			$dbname = defined('DB_NAME') ? DB_NAME : 'THODZ';
			$user = defined('DB_USER') ? DB_USER : 'root';
			$pass = defined('DB_PASS') ? DB_PASS : '';
			$port = defined('DB_PORT') ? DB_PORT : ($driver === 'pgsql' ? '5432' : '3306');

			if ($driver === 'pgsql') {
				// PostgreSQL (Koyeb)
				$dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";
			} else {
				// MySQL/MariaDB (local Docker)
				$dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
			}

			$conn = new PDO($dsn, $user, $pass);
			$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			
			self::$instance = $conn;
		} catch (PDOException $e) {
			error_log("Database connection error: " . $e->getMessage());
			die("Database connection failed. Please check your configuration.");
		}
		return $conn;
	}

	// Get the database driver type
	static function getDriver() {
		return defined('DB_DRIVER') ? DB_DRIVER : 'mysql';
	}

	// Check if using PostgreSQL
	static function isPostgres() {
		return self::getDriver() === 'pgsql';
	}
}
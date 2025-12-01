<?php 

class Database{

	const DB_SERVER = 'db'; // Use 'db' for Docker, 'localhost' for local install
	const DB_USER = "root";
	const DB_PASS = "";
	const DB_NAME = "THODZ";

	function connect(){
		$conn = null;
		try {
		    $conn = new PDO('mysql:host='.self::DB_SERVER.';dbname='.self::DB_NAME, self::DB_USER, self::DB_PASS);
		    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch (PDOException $e) {
		    print "Error!: " . $e->getMessage() . "<br/>";
		    die();
		}
		return $conn;
	}
}
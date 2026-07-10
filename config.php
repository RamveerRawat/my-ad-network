<?php
// Errors ko screen par dikhane ke liye (Taki 500 error ke peeche ka asli galti dikhe)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session ko active rakhne ke liye
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Aapki Real InfinityFree Database Credentials
define('DB_HOST', 'sql100.infinityfree.com'); 
define('DB_USER', 'if0_40982028');            
define('DB_PASS', 'Itpathar1234'); // <-- Yahan apna asli cPanel password likhein (jo hide hai)
define('DB_NAME', 'if0_40982028_adsnetwork');  

// Database Connection Establish karna
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Check Connection
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Global Website URL Setup
$site_url = "https://" . $_SERVER['HTTP_HOST'] . "/"; 
?>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Render Postgres Database ki EXTERNAL DATABASE URL yahan paste kijiye
$db_url = "APNI_POSTGRES_EXTERNAL_URL_YAHAN_DAALEIN"; 

$dbopts = parse_url($db_url);

$host = $dbopts["host"];
$port = $dbopts["port"];
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

try {
    // MySQL ke bajay Postgres (pgsql) connection handler
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Aapki live Render website ka link
$site_url = "https://my-ad-network.onrender.com/"; 
?>

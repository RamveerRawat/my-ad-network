<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Aapki asli Render External Database URL
$db_url = "postgresql://adnetwork_db_user:NZ49mi46Y3U1bl692TP46xSOA1t8DDwR@dpg-d98fpfvavr4c739fgdhg-a.oregon-postgres.render.com/adnetwork_db"; 

$dbopts = parse_url($db_url);

$host = $dbopts["host"];
// Agar URL me port nahi hai, toh default 5432 use karega
$port = isset($dbopts["port"]) ? $dbopts["port"] : "5432"; 
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

try {
    // Connection string ko clean up kiya
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Aapki live Render website ka link
$site_url = "https://my-ad-network.onrender.com/"; 
?>

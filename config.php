<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Apne Render Database page se 'External Database URL' ke samne bani COPY button daba kar link yahan paste karein
$db_url = "postgresql://adnetwork_db_user:NZ49mi46Y3U1bl692TP46xSOA1t8DDwR@dpg-d98fpfvavr4c739fgdhg-a.oregon-postgres.render.com/adnetwork_db"; 

$dbopts = parse_url($db_url);

$host = $dbopts["host"];
$port = $dbopts["port"];
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/');

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Aapki live Render website ka link
$site_url = "https://my-ad-network.onrender.com/"; 
?>

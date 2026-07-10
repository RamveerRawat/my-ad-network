<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Apne Render Postgres page se dekh kar ye alag-alag details bhariye:
$host     = "DNP_HOST_NAME_JO_RENDER_PAR_HAI"; // Render page par 'Hostname' ke samne hoga
$port     = "5432";                            // Postgres ka port hamesha 5432 hota hai
$dbname   = "adnetwork_db";                    // Aapka database naam
$user     = "adnetwork_db_user";               // Aapka username
$pass     = "APKA_PASSWORD_JO_WIPER_DOTS_MEIN_THA"; // Render se password copy karke yahan daalein

try {
    // Direct configuration string - no parse_url needed
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

// Aapki live Render website ka link
$site_url = "https://my-ad-network.onrender.com/"; 
?>

<?php
require_once 'config.php';

try {
    // 1. Users Table
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id SERIAL PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role VARCHAR(20) NOT NULL
    )");

    // 2. Campaigns Table
    $conn->exec("CREATE TABLE IF NOT EXISTS campaigns (
        id SERIAL PRIMARY KEY,
        title VARCHAR(100),
        description TEXT,
        media_url TEXT NOT NULL,
        ad_type VARCHAR(20) NOT NULL,
        ad_size VARCHAR(20),
        status VARCHAR(20) DEFAULT 'active'
    )");

    // 3. Stats Table
    $conn->exec("CREATE TABLE IF NOT EXISTS ad_stats (
        id SERIAL PRIMARY KEY,
        campaign_id INT NOT NULL,
        impressions INT DEFAULT 0,
        clicks INT DEFAULT 0,
        date DATE NOT NULL,
        UNIQUE(campaign_id, date)
    )");

    echo "<h1>Database Tables Created Successfully on Render!</h1>";
} catch (PDOException $e) {
    echo "Error creating tables: " . $e->getMessage();
}
?>

<?php
// Kisi bhi external website (like Blogger) se ad request allow karne ke liye headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET");

// Errors test karne ke liye toggle (InfinityFree testing ke liye)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'config.php';

// Parameters get karna
$type = isset($_GET['type']) ? trim($_GET['type']) : '';
$size = isset($_GET['size']) ? trim($_GET['size']) : '';

if (empty($type)) {
    echo json_encode(["success" => false, "message" => "Ad type is required."]);
    exit;
}

// Simple query execution bina strict variable typing issue ke
if ($type === 'banner' && !empty($size)) {
    $query = "SELECT id, ad_type, ad_size, title, description, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'banner' AND ad_size = ? ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $size);
} else {
    // Video, Popup, Native bina size dynamic query ke fetch honge
    $query = "SELECT id, ad_type, ad_size, title, description, media_url FROM campaigns WHERE status = 'active' AND ad_type = ? ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $type);
}

$stmt->execute();
$result = $stmt->get_result();
$ad = $result->fetch_assoc();

if ($ad) {
    $campaign_id = $ad['id'];
    $today = date('Y-m-d');

    // Impression log logic update karna
    $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) 
                   ON DUPLICATE KEY UPDATE impressions = impressions + 1";
    $stat_stmt = $conn->prepare($stat_query);
    $stat_stmt->bind_param("is", $campaign_id, $today);
    $stat_stmt->execute();

    // Tracking click redirect setup
    $click_url = $site_url . "click.php?ad_id=" . $campaign_id;

    // Output valid JSON format response
    echo json_encode([
        "success" => true,
        "ad_id" => $ad['id'],
        "type" => $ad['ad_type'],
        "title" => htmlspecialchars_decode($ad['title']),
        "description" => htmlspecialchars_decode($ad['description']),
        "media" => $ad['media_url'],
        "target" => $click_url
    ]);
} else {
    echo json_encode(["success" => false, "message" => "No active ads found in database for type: " . $type . " (" . $size . ")"]);
}
?>
<?php
require_once 'config.php';

$size = isset($_GET['size']) ? trim($_GET['size']) : '728x90';

// Database se random ACTIVE banner ad uthao jo required size ka ho
$query = "SELECT id, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'banner' AND ad_size = ? ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $size);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if ($ad) {
    $campaign_id = $ad['id'];
    $today = date('Y-m-d');

    // Real-time Impression Count (+1)
    $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) 
                   ON DUPLICATE KEY UPDATE impressions = impressions + 1";
    $stat_stmt = $conn->prepare($stat_query);
    $stat_stmt->bind_param("is", $campaign_id, $today);
    $stat_stmt->execute();

    $click_url = $site_url . "click.php?ad_id=" . $campaign_id;
    $media_url = $ad['media_url'];
} else {
    // Agar koi active ad na mile toh fallback/default placeholder dikhao
    $click_url = "#";
    $media_url = "https://placehold.co/" . $size . "/e2e8f0/475569.png?text=Advertise+With+Us";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body, html { margin: 0; padding: 0; overflow: hidden; background: transparent; }
        img { width: 100%; height: auto; display: block; border: none; max-width: 100%; }
    </style>
</head>
<body>
    <a href="<?= $click_url; ?>" target="_blank">
        <img src="<?= $media_url; ?>" />
    </a>
</body>
</html>
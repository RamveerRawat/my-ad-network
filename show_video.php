<?php
require_once 'config.php';

// Database se random ACTIVE video ad uthao
$query = "SELECT id, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'video' ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($query);
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
    $show_ad = true;
} else {
    $show_ad = false;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body, html { margin: 0; padding: 0; overflow: hidden; background: #000; font-family: sans-serif; }
        .video-container { position: relative; width: 100%; height: 100vh; display: flex; justify-content: center; align-items: center; }
        video { width: 100%; height: 100%; object-fit: cover; }
        .click-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 85%; z-index: 10; cursor: pointer; }
        .ad-indicator { position: absolute; bottom: 10px; left: 10px; background: rgba(0,0,0,0.6); color: #fff; padding: 2px 8px; font-size: 12px; border-radius: 3px; z-index: 20; }
        .no-ad { color: #fff; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <?php if($show_ad): ?>
        <div class="video-container">
            <video src="<?= $media_url; ?>" autoplay muted playsinline controls></video>
            <a href="<?= $click_url; ?>" target="_blank" class="click-overlay"></a>
            <div class="ad-indicator">Sponsored Ad</div>
        </div>
    <?php else: ?>
        <div class="no-ad">
            <p style="margin: 0; font-size: 14px; color: #aaa;">No active video ads available.</p>
        </div>
    <?php endif; ?>
</body>
</html>
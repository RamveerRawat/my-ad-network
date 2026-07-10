<?php
require_once 'config.php';

// Database se random ACTIVE native ad uthao
$query = "SELECT id, title, description, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'native' ORDER BY RAND() LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->execute();
$ad = $stmt->get_result()->fetch_assoc();

if ($ad) {
    $campaign_id = $ad['id'];
    $today = date('Y-m-d');

    // Native Impression Count (+1)
    $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) 
                   ON DUPLICATE KEY UPDATE impressions = impressions + 1";
    $stat_stmt = $conn->prepare($stat_query);
    $stat_stmt->bind_param("is", $campaign_id, $today);
    $stat_stmt->execute();

    $click_url = $site_url . "click.php?ad_id=" . $campaign_id;
    $media_url = $ad['media_url'];
    $title = htmlspecialchars($ad['title']);
    $desc = htmlspecialchars($ad['description']);
} else {
    // Fallback template agar koi ad na ho
    $click_url = "#";
    $media_url = "https://placehold.co/150x150/e2e8f0/475569.png?text=Ad";
    $title = "Sponsored Content Available";
    $desc = "Advertise your product or website here with our dynamic native ad network solutions.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body, html { margin: 0; padding: 0; overflow: hidden; background: #ffffff; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        .native-ad-box { display: flex; align-items: center; padding: 10px; border: 1px solid #e5e7eb; border-radius: 8px; box-sizing: border-box; text-decoration: none; color: #1f2937; height: 100vh; transition: background 0.2s; }
        .native-ad-box:hover { background: #f9fafb; }
        .ad-img-box { width: 80px; height: 80px; flex-shrink: 0; border-radius: 6px; overflow: hidden; margin-right: 12px; border: 1px solid #f3f4f6; }
        .ad-img-box img { width: 100%; height: 100%; object-fit: cover; }
        .ad-content { flex-grow: 1; min-width: 0; }
        .ad-title { font-size: 14px; font-weight: 600; margin: 0 0 4px 0; color: #111827; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ad-desc { font-size: 12px; margin: 0; color: #4b5563; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4; }
        .ad-badge { display: inline-block; font-size: 9px; background: #f3f4f6; color: #6b7280; padding: 1px 4px; border-radius: 3px; font-weight: bold; margin-top: 4px; text-transform: uppercase; }
    </style>
</head>
<body>
    <a href="<?= $click_url; ?>" target="_blank" class="native-ad-box">
        <div class="ad-img-box">
            <img src="<?= $media_url; ?>" alt="ad thumbnail" />
        </div>
        <div class="ad-content">
            <h4 class="ad-title"><?= $title; ?></h4>
            <p class="ad-desc"><?= $desc; ?></p>
            <span class="ad-badge">Sponsored</span>
        </div>
    </a>
</body>
</html>
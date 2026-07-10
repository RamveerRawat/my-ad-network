<?php
require_once 'config.php';

$ad_id = isset($_GET['ad_id']) ? intval($_GET['ad_id']) : 0;

if ($ad_id <= 0) {
    die("Invalid Ad Request.");
}

// Database se rates aur ad type fetch karna
$query = "SELECT target_url, cpc_cpm_rate, video_click_rate, advertiser_id, ad_type FROM campaigns WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ad_id);
$stmt->execute();
$campaign = $stmt->get_result()->fetch_assoc();

if ($campaign) {
    $target_url = $campaign['target_url'];
    $ad_type = $campaign['ad_type'];
    $advertiser_id = $campaign['advertiser_id'];
    $today = date('Y-m-d');

    // Niyam: Agar video ad hai toh premium click charge uthao, baaki formats ke liye standard view/click rate
    if ($ad_type === 'video') {
        $charge_rate = floatval($campaign['video_click_rate']);
    } else {
        $charge_rate = floatval($campaign['cpc_cpm_rate']);
    }

    // Processing billing only if charge rate is greater than 0
    if ($charge_rate > 0) {
        // 1. Advertiser wallet deduction
        $deduct_query = "UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?";
        $deduct_stmt = $conn->prepare($deduct_query);
        $deduct_stmt->bind_param("did", $charge_rate, $advertiser_id, $charge_rate);
        $deduct_stmt->execute();

        if ($deduct_stmt->affected_rows > 0) {
            // 2. Publisher 70% dynamic profit split matrix
            $publisher_id = isset($_SESSION['user_id']) && $_SESSION['role'] === 'publisher' ? $_SESSION['user_id'] : 2;
            $pub_share = $charge_rate * 0.70;
            
            $credit_query = "UPDATE users SET balance = balance + ? WHERE id = ? AND role = 'publisher'";
            $credit_stmt = $conn->prepare($credit_query);
            $credit_stmt->bind_param("di", $pub_share, $publisher_id);
            $credit_stmt->execute();
        }
    }

    // 3. Stats update (Click count +1)
    $click_query = "INSERT INTO ad_stats (campaign_id, clicks, date) VALUES (?, 1, ?) 
                    ON DUPLICATE KEY UPDATE clicks = clicks + 1";
    $click_stmt = $conn->prepare($click_query);
    $click_stmt->bind_param("is", $ad_id, $today);
    $click_stmt->execute();

    // User ko final landing page par redirect karna
    header("Location: " . $target_url);
    exit;
} else {
    die("Campaign not found or expired.");
}
?>
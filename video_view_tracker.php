<?php
require_once 'config.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$ad_id = isset($_POST['ad_id']) ? intval($_POST['ad_id']) : 0;

if ($ad_id <= 0) {
    echo json_encode(["success" => false, "message" => "Invalid Ad ID"]);
    exit;
}

// Database se view rate (cpc_cpm_rate) aur advertiser ki details nikalna
$query = "SELECT cpc_cpm_rate, advertiser_id FROM campaigns WHERE id = ? AND status = 'active' LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $ad_id);
$stmt->execute();
$campaign = $stmt->get_result()->fetch_assoc();

if ($campaign) {
    $view_rate = floatval($campaign['cpc_cpm_rate']); // 5-Sec View Rate
    $advertiser_id = $campaign['advertiser_id'];
    $today = date('Y-m-d');

    if ($view_rate <= 0) {
        echo json_encode(["success" => true, "message" => "View rate is 0, skipping charge."]);
        exit;
    }

    // 1. Advertiser wallet se view rate deduct karna
    $deduct_query = "UPDATE users SET balance = balance - ? WHERE id = ? AND balance >= ?";
    $deduct_stmt = $conn->prepare($deduct_query);
    $deduct_stmt->bind_param("did", $view_rate, $advertiser_id, $view_rate);
    $deduct_stmt->execute();

    if ($deduct_stmt->affected_rows > 0) {
        // 2. Publisher ko 70% share dena
        $publisher_id = isset($_SESSION['user_id']) && $_SESSION['role'] === 'publisher' ? $_SESSION['user_id'] : 2; // Default ID for testing
        $pub_share = $view_rate * 0.70;

        $credit_query = "UPDATE users SET balance = balance + ? WHERE id = ? AND role = 'publisher'";
        $credit_stmt = $conn->prepare($credit_query);
        $credit_stmt->bind_param("di", $pub_share, $publisher_id);
        $credit_stmt->execute();

        // 3. Stats Update karna (Views/Impressions count +1)
        $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) 
                       ON DUPLICATE KEY UPDATE impressions = impressions + 1";
        $stat_stmt = $conn->prepare($stat_query);
        $stat_stmt->bind_param("is", $ad_id, $today);
        $stat_stmt->execute();

        echo json_encode(["success" => true, "message" => "View counted, balance shared!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Advertiser has insufficient balance."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Active Campaign not found."]);
}
?>
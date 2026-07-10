<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// 1. Publisher ka current wallet balance nikalna
$user_query = "SELECT balance FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$balance = $user_data['balance'] ?? 0.0000;

// 2. Network Stats Matrix (Total Impressions aur Clicks jo is platform par track hue hain)
// Note: Kyunki free setup mein ad_stats campaigns ke sath linked hai, hum global summary dikha rahe hain.
$stats_query = "
    SELECT IFNULL(SUM(s.impressions), 0) as total_impressions, 
           IFNULL(SUM(s.clicks), 0) as total_clicks 
    FROM ad_stats s";
$stats_result = $conn->query($stats_query)->fetch_assoc();
$total_impressions = $stats_result['total_impressions'] ?? 0;
$total_clicks = $stats_result['total_clicks'] ?? 0;

// CTR (Click-Through Rate) calculate karna safety check ke sath
$ctr = ($total_impressions > 0) ? ($total_clicks / $total_impressions) * 100 : 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Publisher Dashboard - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        .stat-card { border: none; border-radius: 12px; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="#">🟢 AdPlatform Publisher Zone</a>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['user_name'] ?? 'Publisher'); ?></span>
            <a class="btn btn-dark btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-dark text-white p-4 stat-card shadow-sm position-relative overflow-hidden">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="text-uppercase text-muted fw-bold mb-1" style="letter-spacing: 1px;">Total Earning Account Balance</h6>
                        <h1 class="display-5 fw-bold text-success">$<?= number_format($balance, 4); ?></h1>
                        <p class="mb-0 text-light-50 small">Aapki video views aur ad clicks dono ki revenue isme dynamic judti hai.</p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <a href="generate-code.php" class="btn btn-success btn-lg fw-bold px-4 py-3 shadow">➔ Get Integration Ad Codes</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <h4 class="fw-bold text-dark mb-3">Traffic Analytics Summary</h4>
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-white p-4 border-0 shadow-sm stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total Impressions / Video Views</h6>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_impressions); ?></h3>
                    </div>
                    <div class="bg-light-success p-3 rounded-circle text-success" style="background: #e8f5e9;">
                        🎬
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-white p-4 border-0 shadow-sm stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Total Ad Clicks Tracked</h6>
                        <h3 class="fw-bold mb-0 text-dark"><?= number_format($total_clicks); ?></h3>
                    </div>
                    <div class="bg-light-primary p-3 rounded-circle text-primary" style="background: #e3f2fd;">
                        ⚡
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-white p-4 border-0 shadow-sm stat-card">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1 small fw-bold text-uppercase">Average Click Rate (CTR)</h6>
                        <h3 class="fw-bold mb-0 text-warning"><?= number_format($ctr, 2); ?>%</h3>
                    </div>
                    <div class="bg-light-warning p-3 rounded-circle text-warning" style="background: #fff8e1;">
                        📈
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-dark">💡 Publisher Optimization Guidelines</h5>
        </div>
        <div class="card-body">
            <div class="row text-muted small">
                <div class="col-md-6 mb-3 mb-md-0">
                    <h6 class="text-dark fw-bold">1. Video Ads Se Kaise Kamayein?</h6>
                    <p class="mb-0">Hamare network par video ads **Per-View** par chalti hain. Jab koi visitor aapki site par ad ko 5 second tak dekhega, aapke account mein 70% share automatically credit ho jayega. Agar user click karta hai, toh click ka alag premium bonus milega!</p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-dark fw-bold">2. Earning Kaise Badhayein?</h6>
                    <p class="mb-0">Apne high-traffic pages aur post layout ke beech mein ad codes widget lagayein. Sidebar ke bajay banner layout templates ka use karein jisse maximum view visibility mile aur aapka revenue automatic multiply ho sake.</p>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
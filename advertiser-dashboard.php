<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'advertiser') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User ka current balance fetch karna
$user_query = "SELECT balance FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$balance = $user_data['balance'] ?? 0.00;

// Campaigns list aur total clicks/impressions calculate karna
$campaigns_query = "
    SELECT c.*, 
           IFNULL(SUM(s.impressions), 0) as total_impressions, 
           IFNULL(SUM(s.clicks), 0) as total_clicks 
    FROM campaigns c 
    LEFT JOIN ad_stats s ON c.id = s.campaign_id 
    WHERE c.advertiser_id = ? 
    GROUP BY c.id 
    ORDER BY c.id DESC";

$stmt_camp = $conn->prepare($campaigns_query);
$stmt_camp->bind_param("i", $user_id);
$stmt_camp->execute();
$campaigns_result = $stmt_camp->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advertiser Dashboard - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="index.php">AdPlatform (Advertiser)</a>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white me-3">Welcome, <?= htmlspecialchars($_SESSION['user_name']); ?></span>
            <a class="btn btn-danger btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-primary text-white shadow-sm">
                <div class="card-body">
                    <h5>Current Balance</h5>
                    <h2>$<?= number_format($balance, 4); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-8 text-end d-flex align-items-center justify-content-end">
            <a href="create-campaign.php" class="btn btn-success btn-lg">+ Create New Campaign</a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Your Ad Campaigns</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Campaign Name</th>
                            <th>Type</th>
                            <th>Size / Details</th>
                            <th>Rate (CPC/CPM)</th>
                            <th>Impressions</th>
                            <th>Clicks</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($campaigns_result->num_rows > 0): ?>
                            <?php while($row = $campaigns_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id']; ?></td>
                                    <td><strong><?= htmlspecialchars($row['ad_name']); ?></strong></td>
                                    <td><span class="badge bg-secondary"><?= strtoupper($row['ad_type']); ?></span></td>
                                    <td><?= $row['ad_size'] ? $row['ad_size'] : 'Responsive'; ?></td>
                                    <td>$<?= number_format($row['cpc_cpm_rate'], 4); ?></td>
                                    <td><?= number_format($row['total_impressions']); ?></td>
                                    <td><?= number_format($row['total_clicks']); ?></td>
                                    <td>
                                        <?php if($row['status'] == 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php elseif($row['status'] == 'paused'): ?>
                                            <span class="badge bg-warning text-dark">Paused</span>
                                        <?php else: ?>
                                            <span class="badge bg-info text-dark">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-4">No campaigns found. Create one to start!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
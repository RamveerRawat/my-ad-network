<?php
require_once 'config.php';

// Security Check: Sirf 'admin' role wale users hi isse open kar sakte hain
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("<div style='text-align:center; margin-top:50px; font-family:sans-serif;'><h2>Access Denied!</h2><p>Aapke paas is page ko dekhne ki permission nahi hai.</p><a href='login.php'>Login karein</a></div>");
}

// --- Action 1: Campaign Status Update (Approve / Reject / Pause) ---
if (isset($_GET['action']) && isset($_GET['campaign_id'])) {
    $action = $_GET['action'];
    $campaign_id = intval($_GET['campaign_id']);
    
    if ($action === 'approve') {
        $update_status = "UPDATE campaigns SET status = 'active' WHERE id = ?";
    } elseif ($action === 'pause') {
        $update_status = "UPDATE campaigns SET status = 'paused' WHERE id = ?";
    }
    
    if (isset($update_status)) {
        $stmt = $conn->prepare($update_status);
        $stmt->bind_param("i", $campaign_id);
        $stmt->execute();
    }
    header("Location: admin-dashboard.php");
    exit;
}

// --- Action 2: User Balance Manual Update ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_balance'])) {
    $target_user_id = intval($_POST['user_id']);
    $new_balance = floatval($_POST['balance']);
    
    $update_bal_query = "UPDATE users SET balance = ? WHERE id = ?";
    $stmt = $conn->prepare($update_bal_query);
    $stmt->bind_param("di", $new_balance, $target_user_id);
    $stmt->execute();
    header("Location: admin-dashboard.php");
    exit;
}

// --- Data Fetching: Platform Overview Stats ---
$total_users = $conn->query("SELECT COUNT(id) as total FROM users")->fetch_assoc()['total'];
$total_ads = $conn->query("SELECT COUNT(id) as total FROM campaigns")->fetch_assoc()['total'];

// Stats table se sums uthana aur null safety check lagana
$impressions_res = $conn->query("SELECT SUM(impressions) as total FROM ad_stats")->fetch_assoc();
$total_impressions = $impressions_res['total'] ?? 0;

$clicks_res = $conn->query("SELECT SUM(clicks) as total FROM ad_stats")->fetch_assoc();
$total_clicks = $clicks_res['total'] ?? 0;

// --- Data Fetching: Pending/Active Campaigns List (Naya video_click_rate select kiya) ---
$campaigns_result = $conn->query("SELECT c.*, u.name as advertiser_name FROM campaigns c JOIN users u ON c.advertiser_id = u.id ORDER BY c.status DESC, c.id DESC");

// --- Data Fetching: Users List ---
$users_result = $conn->query("SELECT id, name, email, role, balance FROM users WHERE role != 'admin' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Control Panel - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
    <div class="container">
        <a class="navbar-brand" href="#">👑 AdPlatform Core Admin</a>
        <div class="navbar-nav ms-auto">
            <span class="nav-link text-white me-3">Welcome, Master <?= htmlspecialchars($_SESSION['user_name'] ?? 'Admin'); ?></span>
            <a class="btn btn-dark btn-sm" href="logout.php">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card bg-white p-3 border-0 shadow-sm text-center">
                <h6 class="text-muted">Total Network Users</h6>
                <h3><?= $total_users; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white p-3 border-0 shadow-sm text-center">
                <h6 class="text-muted">Total Campaigns</h6>
                <h3><?= $total_ads; ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white p-3 border-0 shadow-sm text-center">
                <h6 class="text-muted">Total Impressions Served</h6>
                <h3><?= number_format($total_impressions); ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-white p-3 border-0 shadow-sm text-center">
                <h6 class="text-muted">Total Clicks Tracked</h6>
                <h3><?= number_format($total_clicks); ?></h3>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Campaign Approvals & Control</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Advertiser</th>
                            <th>Ad Name / Format</th>
                            <th>Media / Target Link</th>
                            <th>Bid Pricing Model</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($ad = $campaigns_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($ad['advertiser_name']); ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ad['ad_name']); ?></strong><br>
                                    <span class="badge bg-secondary"><?= strtoupper($ad['ad_type']); ?> <?= $ad['ad_size'] ? $ad['ad_size'] : ''; ?></span>
                                </td>
                                <td>
                                    <small><strong>Media:</strong> <a href="<?= $ad['media_url']; ?>" target="_blank">View Media</a></small><br>
                                    <small><strong>Landing:</strong> <a href="<?= $ad['target_url']; ?>" target="_blank">Visit Site</a></small>
                                </td>
                                <td>
                                    <?php if($ad['ad_type'] === 'video'): ?>
                                        <small class="text-success"><strong>View:</strong> $<?= number_format($ad['cpc_cpm_rate'], 4); ?></small><br>
                                        <small class="text-primary"><strong>Click:</strong> $<?= number_format($ad['video_click_rate'], 4); ?></small>
                                    <?php else: ?>
                                        <small class="text-dark"><strong>Rate:</strong> $<?= number_format($ad['cpc_cpm_rate'], 4); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($ad['status'] == 'pending'): ?>
                                        <span class="badge bg-info text-dark">Pending</span>
                                    <?php elseif($ad['status'] == 'active'): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning text-dark">Paused</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($ad['status'] == 'pending' || $ad['status'] == 'paused'): ?>
                                        <a href="admin-dashboard.php?action=approve&campaign_id=<?= $ad['id']; ?>" class="btn btn-success btn-sm">Approve</a>
                                    <?php else: ?>
                                        <a href="admin-dashboard.php?action=pause&campaign_id=<?= $ad['id']; ?>" class="btn btn-warning btn-sm">Pause</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">User Accounts & Wallet Manager</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Account Type</th>
                            <th>Wallet Balance</th>
                            <th>Modify Wallet</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($user = $users_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $user['id']; ?></td>
                                <td><strong><?= htmlspecialchars($user['name']); ?></strong></td>
                                <td><?= htmlspecialchars($user['email']); ?></td>
                                <td><span class="badge <?= $user['role'] == 'advertiser' ? 'bg-primary' : 'bg-success'; ?>"><?= strtoupper($user['role']); ?></span></td>
                                <td><strong>$<?= number_format($user['balance'], 4); ?></strong></td>
                                <td>
                                    <form action="admin-dashboard.php" method="POST" class="d-flex g-2" style="max-width: 220px;">
                                        <input type="hidden" name="user_id" value="<?= $user['id']; ?>">
                                        <input type="number" name="balance" class="form-control form-control-sm me-1" step="0.0001" value="<?= $user['balance']; ?>" required>
                                        <button type="submit" name="update_balance" class="btn btn-sm btn-primary">Update</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>
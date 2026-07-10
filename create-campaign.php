<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'advertiser') {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $advertiser_id = $_SESSION['user_id'];
    $ad_name = trim($_POST['ad_name']);
    $ad_type = $_POST['ad_type'];
    $ad_size = isset($_POST['ad_size']) ? $_POST['ad_size'] : null;
    $title = isset($_POST['title']) ? trim($_POST['title']) : null;
    $description = isset($_POST['description']) ? trim($_POST['description']) : null;
    $media_url = trim($_POST['media_url']);
    $target_url = trim($_POST['target_url']);
    $rate = floatval($_POST['rate']); // Main Click Rate (cpc_cpm_rate or video_click_rate)

    // Naya feature: Video ke liye View Rate fetch karna
    $video_view_rate = isset($_POST['video_view_rate']) ? floatval($_POST['video_view_rate']) : 0.0000;

    if (empty($ad_name) || empty($ad_type) || empty($media_url) || empty($target_url) || $rate <= 0) {
        $error = "Sabhi zaroori fields sahi se bhariye.";
    } else {
        // Core Architecture Dynamic Mapping:
        // Agar Video ad hai, toh dynamic query ke mutabik view_rate jaayega 'cpc_cpm_rate' mein, aur click_rate jaayega 'video_click_rate' mein.
        if ($ad_type === 'video') {
            $final_cpc_cpm = $video_view_rate;
            $final_video_click = $rate;
        } else {
            $final_cpc_cpm = $rate;
            $final_video_click = 0.0000;
        }

        $insert_query = "INSERT INTO campaigns (advertiser_id, ad_name, ad_type, ad_size, title, description, media_url, target_url, cpc_cpm_rate, video_click_rate, status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("isssssssdd", $advertiser_id, $ad_name, $ad_type, $ad_size, $title, $description, $media_url, $target_url, $final_cpc_cpm, $final_video_click);

        if ($stmt->execute()) {
            $success = "Campaign safaltapoorvak ban gaya hai! Admin approval ke baad active ho jayega.";
        } else {
            $error = "Database error: Campaign nahi ban paya.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Campaign - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="mb-0">Create New Ad Campaign</h3>
                        <a href="advertiser-dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
                    </div>

                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success; ?></div>
                    <?php endif; ?>

                    <form action="create-campaign.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Campaign / Ad Name</label>
                            <input type="text" name="ad_name" class="form-control" placeholder="e.g., My First Banner Ad" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Ad Format Type</label>
                            <select name="ad_type" id="ad_type" class="form-select" onchange="toggleFormFields()" required>
                                <option value="banner">Banner Ad</option>
                                <option value="native">Native Ad (Image + Text)</option>
                                <option value="popup">Popunder / Popup Ad</option>
                                <option value="video">Video Ad (Direct MP4 URL)</option>
                            </select>
                        </div>

                        <div class="mb-3" id="banner_size_div">
                            <label class="form-label">Banner Size</label>
                            <select name="ad_size" class="form-select">
                                <option value="300x250">300x250 (Square)</option>
                                <option value="728x90">728x90 (Leaderboard)</option>
                                <option value="160x600">160x600 (Skyscraper)</option>
                            </select>
                        </div>

                        <div id="native_fields_div" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Native Ad Title</label>
                                <input type="text" name="title" class="form-control" placeholder="Catchy heading...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Native Ad Description</label>
                                <textarea name="description" class="form-control" rows="2" placeholder="Short description..."></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" id="media_label">Media URL (Image Link)</label>
                            <input type="url" name="media_url" class="form-control" placeholder="https://example.com/image.jpg" required>
                            <small class="text-muted" id="media_hint">Ad ke banner image ka direct link dalein.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Target / Landing Page URL</label>
                            <input type="url" name="target_url" class="form-control" placeholder="https://advertiser-website.com" required>
                            <small class="text-muted">User jab ad par click karega toh is website par jayega.</small>
                        </div>

                        <!-- Dynamic Rates Framework Block -->
                        <div class="row g-3 mb-3">
                            <!-- Naya Video View Rate Feature (Sirf Video select karne par JS se open hoga) -->
                            <div class="col-md-6" id="video_view_rate_div" style="display: none;">
                                <label class="form-label">Video 5-Sec View Rate (CPM Price in USD)</label>
                                <input type="number" name="video_view_rate" id="video_view_rate" class="form-control" step="0.0001" placeholder="0.0050">
                                <small class="text-muted">User ke 5 second video dekhne par ye rate katega.</small>
                            </div>

                            <div class="col-12" id="rate_input_container">
                                <label class="form-label" id="rate_label">Bid Rate (CPC/CPM Price in USD)</label>
                                <input type="number" name="rate" class="form-control" step="0.0001" placeholder="0.0010" required>
                                <small class="text-muted" id="rate_hint">Per click ya per view aap kitna pay karna chahte hain.</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Submit Campaign</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFormFields() {
    var type = document.getElementById('ad_type').value;
    var bannerSizeDiv = document.getElementById('banner_size_div');
    var nativeFieldsDiv = document.getElementById('native_fields_div');
    var mediaLabel = document.getElementById('media_label');
    var mediaHint = document.getElementById('media_hint');
    
    // Naye fields elements pointers
    var videoViewRateDiv = document.getElementById('video_view_rate_div');
    var rateInputContainer = document.getElementById('rate_input_container');
    var rateLabel = document.getElementById('rate_label');
    var rateHint = document.getElementById('rate_hint');
    var videoViewInput = document.getElementById('video_view_rate');

    // Reset fields defaults
    bannerSizeDiv.style.display = 'none';
    nativeFieldsDiv.style.display = 'none';
    videoViewRateDiv.style.display = 'none';
    videoViewInput.removeAttribute('required');
    rateInputContainer.className = "col-12";
    mediaLabel.innerText = "Media URL (Image Link)";
    mediaHint.innerText = "Ad ke banner image ka direct link dalein.";
    rateLabel.innerText = "Bid Rate (CPC/CPM Price in USD)";
    rateHint.innerText = "Per click ya per view aap kitna pay karna chahte hain.";

    if (type === 'banner') {
        bannerSizeDiv.style.display = 'block';
    } else if (type === 'native') {
        value = bannerSizeDiv.style.display = 'none';
        nativeFieldsDiv.style.display = 'block';
    } else if (type === 'popup') {
        mediaLabel.innerText = "Popup/Destination URL";
        mediaHint.innerText = "Yahan media link aur destination target link dono me aap apna same target URL daal sakte hain.";
    } else if (type === 'video') {
        mediaLabel.innerText = "Direct Video URL (.mp4)";
        mediaHint.innerText = "Google Drive ya cloud par hosted direct mp4 video ka link dalein.";
        
        // Dynamic View/Click Input Matrix layout update
        videoViewRateDiv.style.display = 'block';
        videoViewInput.setAttribute('required', 'required');
        rateInputContainer.className = "col-md-6"; // Split screen equally
        
        rateLabel.innerText = "Additional Video Click Rate (CPC in USD)";
        rateHint.innerText = "User agar video par click karega toh alag se ye charge katega.";
    }
}

// Run basic check on window init
document.addEventListener("DOMContentLoaded", function() {
    toggleFormFields();
});
</script>
</body>
</html>
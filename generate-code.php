<?php
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'publisher') {
    header("Location: login.php");
    exit;
}

// Global server base urls
$js_engine_url = $site_url . "get_ad.php";
$click_base_url = $site_url . "click.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Get Ad Codes - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background: #f4f6f9; }
        pre { background: #1e293b; color: #f8fafc; padding: 15px; border-left: 4px solid #10b981; border-radius: 6px; overflow-x: auto; font-size: 14px; position: relative; }
        .card { border: none; border-radius: 10px; }
        .copy-btn { position: absolute; top: 10px; right: 10px; background: #475569; color: #fff; border: none; padding: 4px 10px; font-size: 11px; border-radius: 4px; cursor: pointer; }
        .copy-btn:hover { background: #334155; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5 mb-5">
    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0 fw-bold text-dark">Publisher Integration Codes</h3>
                <a href="publisher-dashboard.php" class="btn btn-secondary btn-sm">Back to Dashboard</a>
            </div>
            
            <p class="text-muted mb-4">Niche diye gaye high-performance JavaScript codes ko copy karke apni website ke HTML view ya Layout widgets mein paste karein. Yeh codes modern ad block bypass technology ke sath dynamically work karte hain.</p>

            <div class="row">
                
                <!-- 1. BANNER DISPLAY ADS -->
                <div class="col-12 mb-4">
                    <div class="border p-4 rounded bg-white shadow-sm">
                        <h5 class="fw-bold text-primary mb-2">1. Banner Ads (Bypass Delivery Script)</h5>
                        <p class="text-muted small">Apne widget area ke layout ke hisab se dimensions chunein aur code copy karein:</p>
                        
                        <select id="banner_size" class="form-select mb-3" style="max-width: 300px;" onchange="updatePublisherBannerCode()">
                            <option value="300x250">300x250 - Medium Rectangle (Sidebar/Content)</option>
                            <option value="728x90">728x90 - Leaderboard Banner (Header/Footer)</option>
                            <option value="160x600">160x600 - Skyscraper (Left/Right Sidebar)</option>
                        </select>
                        
                        <pre><button class="copy-btn" onclick="copyToClipboard('banner_code_box')">Copy</button><code id="banner_code_box"></code></pre>
                    </div>
                </div>

                <!-- 2. NATIVE ARTICLE ADS -->
                <div class="col-12 mb-4">
                    <div class="border p-4 rounded bg-white shadow-sm">
                        <h5 class="fw-bold mb-2" style="color: #4f46e5 !important;">2. Native Content Ads (Recommendation Block)</h5>
                        <p class="text-muted small">Ise posts/articles ke beech mein ya bilkul aakhiri mein lagayein. Yeh content ke sath automatic blend hokar sabsay zyada clicks generate karta hai.</p>
                        
                        <pre><button class="copy-btn" onclick="copyToClipboard('native_code_box')">Copy</button><code id="native_code_box">&lt;script src="<?= $js_engine_url; ?>?type=native"&gt;&lt;/script&gt;</code></pre>
                    </div>
                </div>

                <!-- 3. POPUNDER ADS -->
                <div class="col-12 mb-4">
                    <div class="border p-4 rounded bg-white shadow-sm">
                        <h5 class="fw-bold text-success mb-2">3. Popunder Smart Ads</h5>
                        <p class="text-muted small">Ise apni website ke <code>&lt;body&gt;</code> tag ke sabse niche lagayein. User ke pehle tap/click par background mein advertiser landing page smoothly load ho jayega.</p>
                        
                        <pre><button class="copy-btn" onclick="copyToClipboard('pop_code_box')">Copy</button><code id="pop_code_box">&lt;script src="<?= $js_engine_url; ?>?type=popup"&gt;&lt;/script&gt;</code></pre>
                    </div>
                </div>

                <!-- 4. OUTSTREAM VIDEO ADS -->
                <div class="col-12 mb-4">
                    <div class="border p-4 rounded bg-white shadow-sm">
                        <h5 class="fw-bold text-danger mb-2">4. Video Stream Ads (YouTube Style Skip)</h5>
                        <p class="text-muted small">Auto-playing responsive interactive player injection code. 5 second completed views aur clicks dono par separate double metrics evaluate hote hain.</p>
                        
                        <pre><button class="copy-btn" onclick="copyToClipboard('video_code_box')">Copy</button><code id="video_code_box">&lt;script src="<?= $js_engine_url; ?>?type=video"&gt;&lt;/script&gt;</code></pre>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
// Dynamic code script switcher logic for publishers
function updatePublisherBannerCode() {
    var size = document.getElementById('banner_size').value;
    var code = `<!-- AdNet Dynamic Banner Tag -->\n` +
               `<script src="<?= $js_engine_url; ?>?type=banner&size=${size}"><\/script>`;
               
    document.getElementById('banner_code_box').innerText = code;
}

// Global Clipboard Copy Mechanism
function copyToClipboard(elementId) {
    var text = document.getElementById(elementId).innerText;
    var elem = document.createElement("textarea");
    document.body.appendChild(elem);
    elem.value = text;
    elem.select();
    document.execCommand("copy");
    document.body.removeChild(elem);
    alert("Ad code copied to clipboard successfully!");
}

// Initial fire on layout load
updatePublisherBannerCode();
</script>
</body>
</html>
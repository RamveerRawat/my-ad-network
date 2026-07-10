<?php
require_once 'config.php';

// Browser ko batana ki ye ek JavaScript file hai
header("Content-Type: application/javascript; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

$type = isset($_GET['type']) ? trim($_GET['type']) : 'banner';
$size = isset($_GET['size']) ? trim($_GET['size']) : '728x90';
$today = date('Y-m-d');

$dimensions = explode('x', $size);
$width = isset($dimensions[0]) ? intval($dimensions[0]) : 728;
$height = isset($dimensions[1]) ? intval($dimensions[1]) : 90;
?>

(function() {
    // Current script element ka reference lena taaki ad sahi jagah par inject ho sake
    var currentScript = document.currentScript || (function() {
        var scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

<?php
// ==========================================
// 1. BANNER DISPLAY ADS LOGIC
// ==========================================
if ($type === 'banner') {
    $query = "SELECT id, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'banner' AND ad_size = ? ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $size);
    $stmt->execute();
    $ad = $stmt->get_result()->fetch_assoc();

    if ($ad) {
        $ad_id = $ad['id'];
        $media_url = $ad['media_url'];
        
        $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1";
        $stat_stmt = $conn->prepare($stat_query);
        $stat_stmt->bind_param("is", $ad_id, $today);
        $stat_stmt->execute();

        $click_url = $site_url . "click.php?ad_id=" . $ad_id;
    } else {
        $click_url = "#";
        $media_url = "https://placehold.co/" . $size . "/007bff/ffffff.png?text=Advertise+With+Us";
    }
?>
    var adContainer = document.createElement('div');
    adContainer.style.textAlign = 'center';
    adContainer.style.margin = '10px auto';
    adContainer.style.maxWidth = '100%';

    var adLink = document.createElement('a');
    adLink.href = "<?= $click_url; ?>";
    adLink.target = "_blank";

    var adImg = document.createElement('img');
    adImg.src = "<?= $media_url; ?>";
    adImg.width = "<?= $width; ?>";
    adImg.height = "<?= $height; ?>";
    adImg.style.border = "none";
    adImg.style.maxWidth = "100%";
    adImg.style.height = "auto";
    adImg.style.borderRadius = "4px";
    adImg.style.boxShadow = "0 1px 3px rgba(0,0,0,0.1)";

    adLink.appendChild(adImg);
    adContainer.appendChild(adLink);
    currentScript.parentNode.insertBefore(adContainer, currentScript);
<?php
    exit;
}

// ==========================================
// 2. NATIVE ARTICLE ADS LOGIC
// ==========================================
if ($type === 'native') {
    $query = "SELECT id, title, description, media_url FROM campaigns WHERE status = 'active' AND ad_type = 'native' ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $ad = $stmt->get_result()->fetch_assoc();

    if ($ad) {
        $ad_id = $ad['id'];
        $title = htmlspecialchars($ad['title'], ENT_QUOTES, 'UTF-8');
        $desc = htmlspecialchars($ad['description'], ENT_QUOTES, 'UTF-8');
        $media_url = $ad['media_url'];

        $stat_query = "INSERT INTO ad_stats (campaign_id, impressions, date) VALUES (?, 1, ?) ON DUPLICATE KEY UPDATE impressions = impressions + 1";
        $stat_stmt = $conn->prepare($stat_query);
        $stat_stmt->bind_param("is", $ad_id, $today);
        $stat_stmt->execute();

        $click_url = $site_url . "click.php?ad_id=" . $ad_id;
    } else {
        $click_url = "#";
        $media_url = "https://placehold.co/100x100/e2e8f0/475569.png?text=Ad";
        $title = "Sponsored Content Available";
        $desc = "Advertise your brand or website here instantly with premium native widgets.";
    }
?>
    var nativeLink = document.createElement('a');
    nativeLink.href = "<?= $click_url; ?>";
    nativeLink.target = "_blank";
    nativeLink.style.display = 'flex';
    nativeLink.style.alignItems = 'center';
    nativeLink.style.padding = '12px';
    nativeLink.style.border = '1px solid #e5e7eb';
    nativeLink.style.borderRadius = '8px';
    nativeLink.style.textDecoration = 'none';
    nativeLink.style.color = '#1f2937';
    nativeLink.style.background = '#ffffff';
    nativeLink.style.maxWidth = '600px';
    nativeLink.style.margin = '10px auto';
    nativeLink.style.fontFamily = 'sans-serif';

    var imgBox = document.createElement('div');
    imgBox.style.width = '80px';
    imgBox.style.height = '80px';
    imgBox.style.flexShrink = '0';
    imgBox.style.borderRadius = '6px';
    imgBox.style.overflow = 'hidden';
    imgBox.style.marginRight = '12px';

    var nativeImg = document.createElement('img');
    nativeImg.src = "<?= $media_url; ?>";
    nativeImg.style.width = '100%';
    nativeImg.style.height = '100%';
    nativeImg.style.objectFit = 'cover';
    imgBox.appendChild(nativeImg);

    var contentBox = document.createElement('div');
    contentBox.style.flexGrow = '1';
    contentBox.style.minWidth = '0';

    var nativeTitle = document.createElement('h4');
    nativeTitle.style.fontSize = '14px';
    nativeTitle.style.fontWeight = '600';
    nativeTitle.style.margin = '0 0 4px 0';
    nativeTitle.style.color = '#111827';
    nativeTitle.style.whiteSpace = 'nowrap';
    nativeTitle.style.overflow = 'hidden';
    nativeTitle.style.textOverflow = 'ellipsis';
    nativeTitle.innerText = "<?= $title; ?>";

    var nativeDesc = document.createElement('p');
    nativeDesc.style.fontSize = '12px';
    nativeDesc.style.margin = '0';
    nativeDesc.style.color = '#4b5563';
    nativeDesc.style.lineHeight = '1.4';
    nativeDesc.style.display = '-webkit-box';
    nativeDesc.style.webkitLineClamp = '2';
    nativeDesc.style.webkitBoxOrient = 'vertical';
    nativeDesc.style.overflow = 'hidden';
    nativeDesc.innerText = "<?= $desc; ?>";

    var badge = document.createElement('span');
    badge.style.display = 'inline-block';
    badge.style.fontSize = '9px';
    badge.style.background = '#f3f4f6';
    badge.style.color = '#6b7280';
    badge.style.padding = '1px 5px';
    badge.style.borderRadius = '3px';
    badge.style.fontWeight = 'bold';
    badge.style.marginTop = '5px';
    badge.style.textTransform = 'uppercase';
    badge.innerText = "Sponsored";

    contentBox.appendChild(nativeTitle);
    contentBox.appendChild(nativeDesc);
    contentBox.appendChild(badge);

    nativeLink.appendChild(imgBox);
    nativeLink.appendChild(contentBox);

    currentScript.parentNode.insertBefore(nativeLink, currentScript);
<?php
    exit;
}

// ==========================================
// 3. POPUNDER / POPUP ADS LOGIC
// ==========================================
if ($type === 'popup') {
    $query = "SELECT id FROM campaigns WHERE status = 'active' AND ad_type = 'popup' ORDER BY RAND() LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $ad = $stmt->get_result()->fetch_assoc();

    if ($ad) {
        $click_url = $site_url . "click.php?ad_id=" . $ad['id'];
?>
    document.addEventListener('click', function() {
        if (!window.adNetPopTriggered) {
            window.open("<?= $click_url; ?>", '_blank');
            window.adNetPopTriggered = true;
        }
    });
<?php
    }
    exit;
}

// ==========================================
// 4. OUTSTREAM VIDEO ADS LOGIC (YouTube Style)
// ==========================================
if ($type === 'video') {
    $video_iframe_url = $site_url . "show_video.php";
?>
    var videoContainer = document.createElement('div');
    videoContainer.style.width = '100%';
    videoContainer.style.maxWidth = '480px';
    videoContainer.style.margin = '10px auto';

    var videoIframe = document.createElement('iframe');
    videoIframe.src = "<?= $video_iframe_url; ?>";
    videoIframe.width = '100%';
    videoIframe.height = '280';
    videoIframe.frameBorder = '0';
    videoIframe.scrolling = 'no';
    videoIframe.style.border = 'none';
    videoIframe.style.overflow = 'hidden';
    videoIframe.style.background = '#000000';
    videoIframe.style.borderRadius = '6px';
    videoIframe.setAttribute('allowtransparency', 'true');

    videoContainer.appendChild(videoIframe);
    currentScript.parentNode.insertBefore(videoContainer, currentScript);
<?php
    exit;
}
?>
})();
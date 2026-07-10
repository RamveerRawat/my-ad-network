<?php
require_once 'config.php';

// Agar user pehle se login hai, toh use dashboard par bhej do
if (isset($_SESSION['user_id'])) {
    header("Location: " . $_SESSION['role'] . "-dashboard.php");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    if (empty($name) || empty($email) || empty($password) || empty($role)) {
        $error = "Sabhi fields bharna zaroori hai.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Galat email format.";
    } elseif (strlen($password) < 6) {
        $error = "Password kam se kam 6 characters ka hona chahiye.";
    } else {
        // Check karna ki email pehle se register toh nahi hai
        $check_query = "SELECT id FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Yeh email pehle se registered hai.";
        } else {
            // Password ko secure hash karna
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Database me user insert karna
            $insert_query = "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($insert_stmt->execute()) {
                $success = "Registration safal raha! Ab aap login kar sakte hain.";
            } else {
                $error = "Kuch galat hua. Kripya baad me koshish karein.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Create Account</h3>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success"><?= $success; ?></div>
                    <?php endif; ?>

                    <form action="register.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Type</label>
                            <select name="role" class="form-select" required>
                                <option value="publisher">Publisher (Earn Money)</option>
                                <option value="advertiser">Advertiser (Run Ads)</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </form>
                    <p class="text-center mt-3">Pehle se account hai? <a href="login.php">Login karein</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
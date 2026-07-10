<?php
require_once 'config.php';

if (isset($_SESSION['user_id'])) {
    header("Location: " . $_SESSION['role'] . "-dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (empty($email) || empty($password)) {
        $error = "Email aur Password dono bhariye.";
    } else {
        $query = "SELECT id, name, password, role FROM users WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        // Password verify karna
        if ($user && password_verify($password, $user['password'])) {
            // Session variables set karna
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            // Role ke basis par redirect karna
            if ($user['role'] == 'admin') {
                header("Location: admin-dashboard.php");
            } elseif ($user['role'] == 'advertiser') {
                header("Location: advertiser-dashboard.php");
            } else {
                header("Location: publisher-dashboard.php");
            }
            exit;
        } else {
            $error = "Galat Email ya Password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - Ad Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3 class="card-title text-center mb-4">Login to Platform</h3>
                    
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?= $error; ?></div>
                    <?php endif; ?>

                    <form action="login.php" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Login</button>
                    </form>
                    <p class="text-center mt-3">Account nahi hai? <a href="register.php">Naya banayein</a></p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
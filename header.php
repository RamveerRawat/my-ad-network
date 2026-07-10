<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AdNet — Smart Advertising Network</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .bg-gradient-dark {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        }
        .text-gradient {
            background: linear-gradient(45deg, #3b82f6, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .hero-section {
            padding: 100px 0;
            color: white;
        }
        .feature-card {
            border: none;
            border-radius: 12px;
            transition: all 0.3s ease;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-gradient-dark shadow-sm py-3">
    <div class="container">
        <a class="navbar-brand fw-bold fs-4" href="index.php">
            <i class="fa-solid fa-rectangle-ad text-info me-2"></i>AdNet
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-3">
                <li class="nav-item"><a class="nav-link text-white-50" href="index.php#publishers">Publishers</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="index.php#advertisers">Advertisers</a></li>
                <li class="nav-item"><a class="nav-link text-white-50" href="index.php#formats">Ad Formats</a></li>
            </ul>
            <div class="d-flex align-items-center gap-2">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="<?= $_SESSION['role']; ?>-dashboard.php" class="btn btn-info btn-sm text-dark px-3 fw-semibold">Dashboard</a>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm px-3">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light btn-sm px-4 me-2">Login</a>
                    <a href="register.php" class="btn btn-info btn-sm text-dark px-4 fw-semibold shadow-sm">Sign Up</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
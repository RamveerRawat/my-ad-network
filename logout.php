<?php
require_once 'config.php';

// Session ko empty karna aur destroy karna
$_SESSION = array();
session_destroy();

// Login page par redirect
header("Location: login.php");
exit;
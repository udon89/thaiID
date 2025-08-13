<?php
session_start();
$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = $user;
    if ($user['role'] === 'admin') {
        header("Location: home.php");
    } else {
        header("Location: read_card.php");
    }
    exit;
} else {
    $_SESSION['error'] = "ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง";
    header("Location: login.php");
    exit;
}

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];

    // ตรวจสอบว่าชื่อผู้ใช้ซ้ำหรือไม่
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $message = "<div class='alert alert-warning'>⚠️ มีชื่อผู้ใช้นี้แล้วในระบบ</div>";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, fullname, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$username, $password, $fullname, $role]);
        $message = "<div class='alert alert-success'>✅ เพิ่มผู้ใช้เรียบร้อยแล้ว</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>เพิ่มผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #eef3ff; padding: 30px; }
        .form-box { max-width: 500px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="form-box">
    <h4 class="text-center mb-3">➕ เพิ่มผู้ใช้งานใหม่</h4>

    <?= $message ?>

    <form method="post">
        <div class="mb-3">
            <label>ชื่อผู้ใช้:</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>รหัสผ่าน:</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>ชื่อ-นามสกุล:</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>บทบาท:</label>
            <select name="role" class="form-select" required>
                <option value="admin">เจ้าพนักงานตำรวจศาล</option>
                <option value="officer">เจ้าหน้าที่รักษาความปลอดภัย</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">💾 บันทึกผู้ใช้</button>
        <a href="manage_users.php" class="btn btn-secondary mt-2 w-100">ย้อนกลับ</a>
    </form>
</div>
</body>
</html>

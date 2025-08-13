<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

// ตรวจสอบว่ามี id ผู้ใช้
if (!isset($_GET['id'])) {
    echo "❌ ไม่พบรหัสผู้ใช้ที่ต้องการลบ";
    exit;
}

$id = intval($_GET['id']);

// ดึงข้อมูลผู้ใช้เพื่อแสดงชื่อก่อนลบ
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ ไม่พบผู้ใช้นี้ในระบบ";
    exit;
}

// หากผู้ใช้ยืนยันการลบแล้ว
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header("Location: manage_users.php?msg=deleted");
        exit;
    } else {
        header("Location: manage_users.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>ลบผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #fdf5f5; padding: 50px; }
        .box { max-width: 500px; margin: auto; padding: 30px; background: #fff; border: 1px solid #ccc; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="box text-center">
    <h4 class="mb-4 text-danger">⚠️ ยืนยันการลบผู้ใช้งาน</h4>
    <p>คุณต้องการลบผู้ใช้งาน <strong><?= htmlspecialchars($user['fullname']) ?></strong> (ชื่อผู้ใช้: <strong><?= htmlspecialchars($user['username']) ?></strong>) ใช่หรือไม่?</p>

    <form method="post">
        <input type="hidden" name="confirm" value="yes">
        <button type="submit" class="btn btn-danger mt-3">✅ ใช่, ลบผู้ใช้</button>
        <a href="manage_users.php" class="btn btn-secondary mt-3">❌ ยกเลิก</a>
    </form>
</div>
</body>
</html>

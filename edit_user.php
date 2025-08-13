<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

if (!isset($_GET['id'])) {
    echo "❌ ไม่พบ ID ผู้ใช้";
    exit;
}

$id = intval($_GET['id']);
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) {
    echo "❌ ไม่พบผู้ใช้นี้";
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $role = $_POST['role'];

    $stmt = $pdo->prepare("UPDATE users SET username = ?, fullname = ?, role = ? WHERE id = ?");
    $stmt->execute([$username, $fullname, $role, $id]);

    $message = "<div class='alert alert-success'>✅ แก้ไขข้อมูลเรียบร้อยแล้ว</div>";

    // Refresh user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f9fbff; padding: 30px; }
        .form-box { max-width: 500px; margin: auto; background: #fff; padding: 25px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
<div class="form-box">
    <h4 class="text-center mb-3">✏️ แก้ไขข้อมูลผู้ใช้งาน</h4>
    <?= $message ?>

    <form method="post">
        <div class="mb-3">
            <label>ชื่อผู้ใช้:</label>
            <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label>ชื่อ-นามสกุล:</label>
            <input type="text" name="fullname" class="form-control" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>
        <div class="mb-3">
            <label>บทบาท:</label>
            <select name="role" class="form-select" required>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>เจ้าพนักงานตำรวจศาล</option>
                <option value="officer" <?= $user['role'] === 'officer' ? 'selected' : '' ?>>เจ้าหน้าที่รักษาความปลอดภัย</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary w-100">💾 บันทึกการเปลี่ยนแปลง</button>
        <a href="manage_users.php" class="btn btn-secondary mt-2 w-100">ย้อนกลับ</a>
    </form>
</div>
</body>
</html>

<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE username LIKE ? OR fullname LIKE ? ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute(["%$search%", "%$search%"]);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>จัดการผู้ใช้งาน</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { font-family: 'Kanit', sans-serif; background-color: #f0f4ff; padding: 30px; }
    .container { max-width: 900px; margin: auto; }
    .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 10px; }
    .table td, .table th { vertical-align: middle; }
  </style>
</head>
<body>
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>👥 จัดการผู้ใช้งาน</h3>
        <a href="add_user.php" class="btn btn-success">➕ เพิ่มผู้ใช้งาน</a>
    </div>

    <form method="get" class="input-group mb-3">
        <input type="text" name="search" class="form-control" placeholder="ค้นหาชื่อ หรือ username" value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary">ค้นหา</button>
    </form>

    <div class="card p-3">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light text-center">
                <tr>
                    <th>ชื่อเต็ม</th>
                    <th>Username</th>
                    <th>บทบาท</th>
                    <th width="250">การจัดการ</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users): ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?= htmlspecialchars($user['fullname']) ?></td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= $user['role'] === 'admin' ? 'เจ้าพนักงานตำรวจศาล' : 'เจ้าหน้าที่รักษาความปลอดภัย' ?></td>
                            <td class="text-center">
                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-warning">✏️ แก้ไข</a>
                                <a href="reset_password.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-secondary">🔑 รีเซ็ตรหัสผ่าน</a>
                                <a href="delete_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนยันการลบผู้ใช้งานนี้?')">🗑️ ลบ</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">ไม่พบผู้ใช้งาน</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <a href="home.php" class="btn btn-outline-secondary">← กลับหน้าแรก</a>
    </div>
</div>
</body>
</html>

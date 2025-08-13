<?php
session_start();
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>หน้าแรกเจ้าพนักงานตำรวจศาล</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background-color: #e9f1ff;
    }
    .container {
      margin-top: 60px;
      max-width: 700px;
    }
    .card {
      padding: 30px;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
      border-radius: 15px;
      background: #fff;
    }
    .btn-group-custom .btn {
      margin-bottom: 15px;
      width: 100%;
      font-size: 1.1rem;
    }
    .logout {
      margin-top: 30px;
    }
  </style>
</head>
<body>

<div class="container">
  <div class="card text-center">
    <h3 class="mb-3">👮‍♂️ ยินดีต้อนรับ <?= htmlspecialchars($_SESSION['user']['fullname']) ?></h3>
    <p class="text-muted">คุณเข้าสู่ระบบในสิทธิ์ <strong><?= $_SESSION['user']['role'] === 'admin' ? 'เจ้าพนักงานตำรวจศาล' : 'เจ้าหน้าที่รักษาความปลอดภัย' ?></strong></p>

    <div class="btn-group-custom mt-4">
      <a href="manage_users.php" class="btn btn-primary">👤 จัดการผู้ใช้งาน</a>
      <a href="import_warrants.php" class="btn btn-warning">📁 อัปโหลดข้อมูลหมายจับ</a>
      <a href="read_card.php" class="btn btn-success">💳 อ่านข้อมูลจากบัตรประชาชน</a>
      <a href="monthly_report.php" class="btn btn-info">📊 ออกรายงานประจำเดือน</a> <!-- ✅ ปุ่มรายงาน -->
    </div>

    <a href="logout.php" class="btn btn-danger logout">🚪 ออกจากระบบ</a>
  </div>
</div>

</body>
</html>

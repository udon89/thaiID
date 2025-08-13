<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}
$userName = $_SESSION['user']['full_name'] ?? 'ไม่ทราบชื่อ';

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// รับช่วงวันที่
$date_start = $_GET['date_start'] ?? date('Y-m-01');
$date_end   = $_GET['date_end'] ?? date('Y-m-t');

// ดึงข้อมูลผู้มาติดต่อ
$stmt = $pdo->prepare("SELECT * FROM visitors WHERE DATE(visit_time) BETWEEN ? AND ?");
$stmt->execute([$date_start, $date_end]);
$data = $stmt->fetchAll();

// นับชาย / หญิง
$male = $female = 0;
$hasWarrant = [];

foreach ($data as $row) {
    $prefix = $row['prefix'];
    if ($prefix === 'นาย') $male++;
    elseif (in_array($prefix, ['นาง', 'นางสาว'])) $female++;

    // ตรวจสอบหมายจับ
    $stmtCheck = $pdo->prepare("SELECT * FROM warrants WHERE citizen_id = ?");
    $stmtCheck->execute([$row['citizen_id']]);
    if ($stmtCheck->fetch()) {
        $hasWarrant[] = $row;
    }
}

$total = count($data);
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>รายงานผู้มาติดต่อ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { font-family: 'Kanit', sans-serif; }
    .btn-bar { margin-bottom: 20px; }
  </style>
</head>
<body class="p-4">

  <div class="btn-bar d-flex justify-content-between">
    <div>
      <a href="home.php" class="btn btn-secondary">🏠 กลับหน้าแรก</a>
    </div>
    <div>
      <a href="logout.php" class="btn btn-danger">🚪 ออกจากระบบ</a>
    </div>
  </div>

  <h3>📊 รายงานผู้มาติดต่อระหว่างวันที่ <?= htmlspecialchars($date_start) ?> ถึง <?= htmlspecialchars($date_end) ?></h3>

  <form method="get" class="row g-3 mb-4">
    <div class="col-auto">
      <label>จากวันที่: <input type="date" name="date_start" class="form-control" value="<?= $date_start ?>"></label>
    </div>
    <div class="col-auto">
      <label>ถึงวันที่: <input type="date" name="date_end" class="form-control" value="<?= $date_end ?>"></label>
    </div>
    <div class="col-auto align-self-end">
      <button type="submit" class="btn btn-primary">📅 แสดงรายงาน</button>
    </div>
  </form>
  <a href="export_report_pdf.php?date_start=<?= $date_start ?>&date_end=<?= $date_end ?>" class="btn btn-outline-danger">🖨️ Export PDF</a>
  <div class="mb-3">
    👨 ชาย: <strong><?= $male ?></strong> |
    👩 หญิง: <strong><?= $female ?></strong> |
    🧑‍🤝‍🧑 รวมทั้งหมด: <strong><?= $total ?></strong>
  </div>

  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>ชื่อ - นามสกุล</th>
        <th>เพศ (จากคำนำหน้า)</th>
        <th>เลขบัตรประชาชน</th>
        <th>เวลาเข้า</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($data as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= $row['prefix'] === 'นาย' ? 'ชาย' : (in_array($row['prefix'], ['นาง', 'นางสาว']) ? 'หญิง' : '-') ?></td>
        <td><?= htmlspecialchars($row['citizen_id']) ?></td>
        <td><?= htmlspecialchars($row['visit_time']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <div class="mt-4 text-muted">
    📝 รายงานโดย: <strong><?= htmlspecialchars($userName) ?></strong>
  </div>

  <?php if (count($hasWarrant) > 0): ?>
    <hr>
    <h4 class="text-danger">⚠️ รายชื่อผู้มีหมายจับในช่วงเวลานี้</h4>
    <table class="table table-danger table-bordered">
      <thead>
        <tr>
          <th>ชื่อ - นามสกุล</th>
          <th>เลขบัตรประชาชน</th>
          <th>เวลาเข้า</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hasWarrant as $w): ?>
          <tr>
            <td><?= htmlspecialchars($w['full_name']) ?></td>
            <td><?= htmlspecialchars($w['citizen_id']) ?></td>
            <td><?= htmlspecialchars($w['visit_time']) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>

</body>
</html>

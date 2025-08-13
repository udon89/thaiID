<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("❌ ไม่สามารถเชื่อมต่อภานข้อมูลได้: " . $e->getMessage());
}

// ✅ ตรวจหมายจับแบบ JSON API (ต้องมาก่อนแสดงหน้า)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_warrant']) && isset($_POST['citizen_id'])) {
    header('Content-Type: application/json; charset=utf-8');
    $cid = $_POST['citizen_id'];
    $stmt = $pdo->prepare("SELECT id FROM warrants WHERE citizen_id = ?");
    $stmt->execute([$cid]);
    echo json_encode(['found' => $stmt->fetch() ? true : false]);
    exit;
}

// ✅ บันทึกผู้มาติดต่อ
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_visitor'])) {
    $citizenId = $_POST['citizen_id'] ?? '';
    $prefix = $_POST['prefix'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';
    $fullName = trim("$prefix $firstName $lastName");

    if ($citizenId && $fullName) {
        try {
            $stmt = $pdo->prepare("INSERT INTO visitors (citizen_id, prefix, full_name, first_name, last_name) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$citizenId, $prefix, $fullName, $firstName, $lastName]);
            $_SESSION['saveMessage'] = ['type' => 'success', 'text' => '✅ บันทึกข้อมูลผู้มาติดต่อเรียบร้อยแล้ว'];
        } catch (Exception $e) {
            $_SESSION['saveMessage'] = ['type' => 'danger', 'text' => '❌ เกิดข้อผิด: ' . htmlspecialchars($e->getMessage())];
        }
    } else {
        $_SESSION['saveMessage'] = ['type' => 'warning', 'text' => '⚠️ กรุณากรอกข้อมูลให้ครบถ้วน'];
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

$saveMessage = $_SESSION['saveMessage'] ?? null;
unset($_SESSION['saveMessage']);
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ระบบผู้มาติดต่อราชการศาล</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body { font-family: 'Kanit', sans-serif; background-color: #f8f9fa; padding: 20px; }
    .blink { animation: blinkAnim 1s infinite; }
    @keyframes blinkAnim { 0%, 100% { background-color: #fff3f3; } 50% { background-color: #ffcccc; } }
  </style>
</head>
<body>
<div class="container mt-5 text-center">
  <h3>📇 ระบบผู้มาติดต่อราชการศาลจังหวัดเชียงราย</h3>
  <div style="position: absolute; right: 20px; top: 20px;">
      <a href="login.php" class="btn btn-outline-dark">🔐 เมนูเจ้าหน้าที่ตำรวจศาล</a>
    </div>
  <div class="mb-3">
    <button class="btn btn-primary me-2" onclick="readCard()">📤 อ่านบัตร</button>
    <button class="btn btn-secondary" onclick="clearData()">🪑 ล้างข้อมูล</button>
  </div>

  <div id="status" class="text-muted mb-3">👉 หลักจากเสียบบัตรประชาชนแล้วกดปุ่ม "อ่านบัตร"
  </div>
  <div id="resultArea"><div class="text-muted">ยังไม่มีข้อมูล</div></div>

  <div id="saveFormArea" style="display:none; max-width:400px; margin:auto; margin-top:20px;">
    <form method="post">
      <input type="hidden" name="citizen_id" id="form_citizen_id">
      <input type="hidden" name="first_name" id="form_first_name">
      <input type="hidden" name="last_name" id="form_last_name">
      <input type="hidden" name="prefix" id="form_prefix">
      <input type="hidden" name="full_name" id="form_full_name">
      <button type="submit" name="save_visitor" class="btn btn-success w-100">💾 บันทึกข้อมูล</button>
    </form>
  </div>

  <?php if ($saveMessage): ?>
    <div class="alert alert-<?= htmlspecialchars($saveMessage['type']) ?> mt-3">
      <?= htmlspecialchars($saveMessage['text']) ?>
    </div>
  <?php endif; ?>
</div>
<audio id="alertSound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto"></audio>
<script>
async function readCard() {
  const statusEl = document.getElementById('status');
  const resultEl = document.getElementById('resultArea');
  const saveFormArea = document.getElementById('saveFormArea');
  const alertSound = document.getElementById('alertSound');

  statusEl.textContent = "🔄 กำลังอ่านข้อมูลจากบัตร...";
  statusEl.className = "text-secondary";
  resultEl.innerHTML = `<div class="text-secondary">📅 กำลังโหลด...</div>`;
  saveFormArea.style.display = "none";

  try {
    const response = await fetch('http://localhost:8080/read.json');
    const data = await response.json();
    const citizenId = data.citizen_id;
    const fullName = `${data.title || ''} ${data.first_name || ''} ${data.last_name || ''}`.trim();

    const warrantCheck = await fetch('', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({ check_warrant: '1', citizen_id: citizenId })
    });
    const warrantJson = await warrantCheck.json();

    let html = `
      <ul class="list-group mb-3" style="max-width:400px;margin:auto;">
        <li class="list-group-item"><strong>เลขบัตร:</strong> ${citizenId}</li>
        <li class="list-group-item"><strong>ชื่อ-นามสกุล:</strong> ${fullName}</li>
      </ul>
    `;
    if (warrantJson.found) {
      html += `<div class="alert alert-danger blink">⚠️ ให้นำตัวบุคคลดังกล่าว พบเจ้าพนักงานตำรวจ</div>`;
      alertSound.play().catch(() => {});
    }

    resultEl.innerHTML = html;
    statusEl.textContent = "✅ อ่านข้อมูลสำเร็จ";
    statusEl.className = "text-success";

    document.getElementById('form_citizen_id').value = citizenId;
    document.getElementById('form_first_name').value = data.first_name;
    document.getElementById('form_last_name').value = data.last_name;
    document.getElementById('form_prefix').value = data.title;
    document.getElementById('form_full_name').value = fullName;

    saveFormArea.style.display = "block";

  } catch (e) {
    statusEl.textContent = "❌ เกิดข้อผิด: " + e.message;
    statusEl.className = "text-danger";
    resultEl.innerHTML = `<div class="alert alert-danger">${e.message}</div>`;
    saveFormArea.style.display = "none";
  }
}

// ✅ ปุ่มล้างข้อมูล = reload หน้าทั้งหมด
function clearData() {
  location.reload();
}
</script>
<?php
// ตรวจหมายจับแบบ POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['check_warrant']) && isset($_POST['citizen_id'])) {
    $cid = $_POST['citizen_id'];
    $stmt = $pdo->prepare("SELECT id FROM warrants WHERE citizen_id = ?");
    $stmt->execute([$cid]);
    echo json_encode(['found' => $stmt->fetch() ? true : false]);
    exit;
}
?>
</body>
</html>

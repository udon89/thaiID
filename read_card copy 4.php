<?php
session_start();

try {
    $pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("❌ ไม่สามารถเชื่อมต่อฐานข้อมูลได้: " . $e->getMessage());
}

// ฟังก์ชันตรวจสอบหมายจับและ COJ API
function checkPerson($pdo, $citizenId, $firstName, $lastName) {
    $result = [
        'warrant' => null,
        'cojResult' => null,
        'citizenId' => $citizenId,
        'fullName' => trim($firstName . ' ' . $lastName),
        'gender' => ''
    ];

    if (!$citizenId) return $result;

    // ตรวจสอบหมายจับในฐานข้อมูล
    $stmt = $pdo->prepare("SELECT * FROM warrants WHERE citizen_id = ?");
    $stmt->execute([$citizenId]);
    $warrant = $stmt->fetch(PDO::FETCH_ASSOC);
    $result['warrant'] = $warrant;

    // ตรวจสอบกับ COJ ถ้าไม่พบหมายจับในฐานข้อมูล
    if (!$warrant && $firstName && $lastName) {
        $token = "ใส่_Bearer_Token_ของคุณที่นี่";
        $payload = [
            "courtCode" => "001",
            "caseNumber" => "",
            "caseType" => "",
            "prefixBlackCase" => "",
            "blackCase" => "",
            "yearBlackCase" => "",
            "prefixRedCase" => "",
            "redCase" => "",
            "yearRedCase" => "",
            "deptCode" => "",
            "name" => $firstName,
            "surname" => $lastName
        ];

        $ch = curl_init("http://10.35.44.6:8089/cojProceed/api/v1/proceed/searchElectronicAppointDateByCase/search?version=1");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer $token",
            "Content-Type: application/json"
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($status === 200) {
            $cojResult = json_decode($response, true);
            $result['cojResult'] = $cojResult;
        }
    }
    return $result;
}

// หากเป็นการส่งแบบ AJAX เพื่ออ่านบัตร
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['citizen_id'])) {
    $citizenId = $_POST['citizen_id'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $lastName = $_POST['last_name'] ?? '';

    $check = checkPerson($pdo, $citizenId, $firstName, $lastName);

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($check);
    exit;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8" />
  <title>ตรวจสอบหมายจับและนัดศาล</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background-color: #f8f9fa;
      padding: 20px;
    }
    .blink {
      animation: blinkAnim 1s infinite;
    }
    @keyframes blinkAnim {
      0%, 100% { background-color: #fff3f3; }
      50% { background-color: #ffcccc; }
    }
    #resultArea ul {
      max-width: 600px;
      margin: auto;
    }
  </style>
</head>
<body>

<div class="container mt-5 text-center">
  <h3 >📇 ระบบผู้มาติดต่อราชการศาลจังหวัดเชียงราย</h3>

  <div class="mb-3 text-center">
    <button class="btn btn-primary me-2" onclick="readCard()">📤 อ่านบัตรประชาชน</button>
    <button class="btn btn-secondary" onclick="clearData()">🧹 ล้างข้อมูล</button>
  </div>

  <div id="status" class="text-center text-muted mb-3">👈 กรุณาเสียบบัตรแล้วกด "อ่านบัตรประชาชน"</div>

  <div id="resultArea" class="text-center">
    <div class="text-muted">ยังไม่มีข้อมูลแสดง</div>
  </div>
</div>

<!-- เสียงแจ้งเตือนหมายจับ -->
<audio id="alertSound" src="https://actions.google.com/sounds/v1/alarms/alarm_clock.ogg" preload="auto"></audio>

<script>
async function readCard() {
  const statusEl = document.getElementById('status');
  const resultEl = document.getElementById('resultArea');
  const alertSound = document.getElementById('alertSound');

  statusEl.textContent = "🔄 กำลังอ่านข้อมูลจากบัตร...";
  statusEl.className = "text-secondary";

  resultEl.innerHTML = `<div class="text-secondary">📥 กำลังโหลดข้อมูล...</div>`;

  try {
    // อ่านข้อมูลบัตรจาก API เครื่องอ่านบัตร
    const response = await fetch('http://localhost:8080/read.json');
    if (!response.ok) throw new Error('ไม่สามารถอ่านข้อมูลจาก API หรือยังไม่ได้เสียบบัตร');

    const data = await response.json();

    // ส่งข้อมูลไปตรวจสอบ (POST AJAX)
    const checkResponse = await fetch(window.location.href, {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        citizen_id: data.citizen_id,
        first_name: data.first_name,
        last_name: data.last_name
      })
    });

    if (!checkResponse.ok) throw new Error('ไม่สามารถตรวจสอบข้อมูลได้');

    const checkData = await checkResponse.json();

    // แสดงข้อมูลพื้นฐาน
    let html = `
      <ul class="list-group mb-3">
        <li class="list-group-item"><strong>เลขบัตรประชาชน:</strong> ${checkData.citizenId}</li>
        <li class="list-group-item"><strong>ชื่อ-นามสกุล:</strong> ${checkData.fullName}</li>
      </ul>
    `;

    // เช็คหมายจับ
    if (checkData.warrant) {
      html += `
      <div class="alert alert-danger blink">
        ⚠️ พบหมายจับในระบบ กรุณานำตัวไปพบเจ้าหน้าที่ตำรวจศาลโดยเร็ว
      </div>
      <ul class="list-group mb-3">
        <li class="list-group-item"><strong>เลขหมายจับ:</strong> ${checkData.warrant.warrant_number}</li>
        <li class="list-group-item"><strong>คดีดำ:</strong> ${checkData.warrant.black_case}</li>
        <li class="list-group-item"><strong>คดีแดง:</strong> ${checkData.warrant.red_case}</li>
        <li class="list-group-item"><strong>ชื่อผู้ถูกจับ:</strong> ${checkData.warrant.name}</li>
      </ul>
      `;
      alertSound.play().catch(e => console.log("เสียงแจ้งเตือนไม่ทำงาน", e));
    }
    // เช็คนัดหมาย COJ
    else if (checkData.cojResult && checkData.cojResult.data && checkData.cojResult.data.length > 0) {
      html += `<div class="alert alert-warning blink">⚠️ พบข้อมูลนัดหมายในสมุดนัดศาล โปรดตรวจสอบรายละเอียดกับเจ้าหน้าที่</div><ul class="list-group mb-3">`;
      for (const item of checkData.cojResult.data) {
        html += `<li class="list-group-item"><strong>เลขคดี:</strong> ${item.caseNumber || '-'} | <strong>วันที่นัด:</strong> ${item.appointDate || '-'}</li>`;
      }
      html += '</ul>';
    } else {
      html += `<div class="alert alert-success">✅ ไม่พบหมายจับ และไม่มีข้อมูลนัดหมายในสมุดนัดศาล</div>`;
    }

    resultEl.innerHTML = html;
    statusEl.textContent = "✅ อ่านข้อมูลสำเร็จ";
    statusEl.className = "text-success";

  } catch (error) {
    statusEl.textContent = "❌ ไม่สามารถอ่านบัตรได้";
    statusEl.className = "text-danger";
    resultEl.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
  }
}

function clearData() {
  const statusEl = document.getElementById('status');
  const resultEl = document.getElementById('resultArea');
  statusEl.textContent = "🧹 ข้อมูลถูกล้างแล้ว";
  statusEl.className = "text-muted";
  resultEl.innerHTML = `<div class="text-muted">ยังไม่มีข้อมูลแสดง</div>`;
}
</script>

</body>
</html>

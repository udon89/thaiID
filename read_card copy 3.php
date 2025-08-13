<?php
// อ่านข้อมูลบัตรจาก API
$api = @file_get_contents('http://localhost:8080/read.json');
if (!$api) {
    echo "<p style='color:red'>❌ ไม่สามารถเชื่อมต่อ API หรือยังไม่ได้เสียบบัตร</p>";
    exit;
}

$data = json_decode($api, true);
$citizenId = $data['citizen_id'] ?? '';
$firstName = $data['first_name'] ?? '';
$lastName = $data['last_name'] ?? '';

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
$stmt = $pdo->prepare("SELECT * FROM warrants WHERE citizen_id = ?");
$stmt->execute([$citizenId]);
$warrant = $stmt->fetch();

// ตรวจสอบกับ COJ ถ้าไม่พบหมายจับ
$cojResult = null;
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
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <title>ตรวจสอบข้อมูลผู้มาติดต่อ</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      font-family: 'Kanit', sans-serif;
      background: #f0f4ff;
      padding: 30px;
    }
    .card {
      max-width: 800px;
      margin: auto;
      box-shadow: 0 0 12px rgba(0,0,0,0.1);
    }
    .status {
      font-weight: bold;
      text-align: center;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>

<div class="card p-4">
  <h4 class="text-center mb-3">📇 ตรวจสอบผู้มาติดต่อราชการ</h4>

  <div class="d-flex justify-content-center gap-2 mb-3">
    <button class="btn btn-primary" onclick="readCard()">📤 อ่านบัตรประชาชน</button>
    <button class="btn btn-secondary" onclick="clearData()">🧹 ล้างข้อมูล</button>
  </div>

  <div id="status" class="status text-muted">👈 กรุณาเสียบบัตรแล้วกด "อ่านบัตรประชาชน"</div>
  <div id="resultArea">
    <div class="text-center text-muted">ยังไม่มีข้อมูลแสดง</div>
  </div>
</div>

<script>
function readCard() {
  const statusEl = document.getElementById("status");
  const resultEl = document.getElementById("resultArea");

  statusEl.textContent = "🔄 กำลังอ่านข้อมูลจากบัตร...";
  statusEl.className = "status text-secondary";
  resultEl.innerHTML = `<div class="text-center text-secondary">📥 กำลังโหลดข้อมูล...</div>`;

  fetch('http://localhost:8080/read.json')
    .then(response => {
      if (!response.ok) throw new Error("ไม่สามารถอ่านข้อมูลจาก API ได้ หรือยังไม่ได้เสียบบัตร");
      return response.json();
    })
    .then(data => {
      statusEl.textContent = "✅ อ่านข้อมูลสำเร็จ";
      statusEl.className = "status text-success";

      // ส่งไปยัง process_card.php เพื่อประมวลผล
      return fetch("process_card.php", {
        method: "POST",
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
          citizen_id: data.citizen_id,
          first_name: data.first_name,
          last_name: data.last_name
        })
      });
    })
    .then(res => res.text())
    .then(html => {
      resultEl.innerHTML = html;
    })
    .catch(err => {
      statusEl.textContent = "❌ ไม่สามารถอ่านบัตรได้";
      statusEl.className = "status text-danger";
      resultEl.innerHTML = `<div class="alert alert-danger text-center">${err.message}</div>`;
    });
}

function clearData() {
  document.getElementById("status").textContent = "🧹 ข้อมูลถูกล้างแล้ว";
  document.getElementById("status").className = "status text-muted";
  document.getElementById("resultArea").innerHTML = `<div class="text-center text-muted">ยังไม่มีข้อมูลแสดง</div>`;
}
</script>

</body>
</html>


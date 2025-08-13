<?php
// รับข้อมูล POST จาก check_card.php
$citizenId = $_POST['citizen_id'] ?? '';
$firstName = $_POST['first_name'] ?? '';
$lastName = $_POST['last_name'] ?? '';

if (empty($citizenId)) {
    echo '<div class="alert alert-danger text-center">❌ ไม่พบข้อมูลจากบัตร</div>';
    exit;
}

// ตรวจสอบในฐานข้อมูลหมายจับ
try {
    $pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
    $stmt = $pdo->prepare("SELECT * FROM warrants WHERE citizen_id = ?");
    $stmt->execute([$citizenId]);
    $warrant = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<div class='alert alert-danger text-center'>❌ Database Error: " . $e->getMessage() . "</div>";
    exit;
}

// ตรวจสอบกับ API COJ หากไม่พบหมายจับ
$cojResult = null;
if (!$warrant && $firstName && $lastName) {
    $token = "ใส่_Bearer_Token_ของคุณที่นี่"; // TODO: เปลี่ยนเป็น Token จริง

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

<!-- แสดงผลลัพธ์ -->
<ul class="list-group mb-3">
    <li class="list-group-item"><strong>เลขบัตร:</strong> <?= htmlspecialchars($citizenId) ?></li>
    <li class="list-group-item"><strong>ชื่อ-นามสกุล:</strong> <?= htmlspecialchars("$firstName $lastName") ?></li>
</ul>

<?php if ($warrant): ?>
    <div class="alert alert-danger text-center fw-bold">
        ⚠️ พบหมายจับในระบบ ให้นำตัวไปพบเจ้าหน้าที่ตำรวจศาลโดยเร็ว
    </div>

    <ul class="list-group border border-danger p-3 rounded">
        <li><strong>เลขหมายจับ:</strong> <?= htmlspecialchars($warrant['warrant_number']) ?></li>
        <li><strong>คดีดำ:</strong> <?= htmlspecialchars($warrant['black_case']) ?></li>
        <li><strong>คดีแดง:</strong> <?= htmlspecialchars($warrant['red_case']) ?></li>
        <li><strong>ชื่อผู้ถูกจับ:</strong> <?= htmlspecialchars($warrant['name']) ?></li>
    </ul>

<?php elseif ($cojResult && !empty($cojResult['data'])): ?>
    <div class="alert alert-warning text-center fw-bold">
        ⚠️ พบข้อมูลในสมุดนัดศาล โปรดตรวจสอบกับเจ้าหน้าที่
    </div>

    <ul class="list-group mt-2">
        <?php foreach ($cojResult['data'] as $item): ?>
            <li class="list-group-item">
                <strong>เลขคดี:</strong> <?= htmlspecialchars($item['caseNumber'] ?? '-') ?> |
                <strong>วันนัด:</strong> <?= htmlspecialchars($item['appointDate'] ?? '-') ?>
            </li>
        <?php endforeach; ?>
    </ul>

<?php else: ?>
    <div class="alert alert-success text-center fw-bold fs-5">
        ✅ ไม่พบหมายจับ และไม่มีข้อมูลในสมุดนัดศาล
    </div>

    <form method="post" action="save_manual_entry.php" class="mt-3 border-top pt-3">
        <h5>🔍 ลงทะเบียนข้อมูลด้วยตนเอง</h5>
        <div class="mb-2">
            <label>เหตุผลที่มาติดต่อ:</label>
            <input type="text" name="reason" class="form-control" required>
        </div>
        <div class="mb-2">
            <label>ชื่อเจ้าหน้าที่รับ:</label>
            <input type="text" name="officer" class="form-control" required>
        </div>
        <input type="hidden" name="citizen_id" value="<?= htmlspecialchars($citizenId) ?>">
        <input type="hidden" name="full_name" value="<?= htmlspecialchars("$firstName $lastName") ?>">
        <button class="btn btn-primary mt-2">📝 บันทึกข้อมูล</button>
    </form>
<?php endif; ?>

<?php
$json = @file_get_contents('http://localhost:8080/read.json');
if (!$json) {
    echo "<p style='color:red'>❌ ไม่สามารถเชื่อมต่อ API หรือยังไม่ได้เสียบบัตร</p>";
    exit;
}

$data = json_decode($json, true);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แสดงข้อมูลบัตรประชาชน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Kanit', sans-serif; background: #f0f4ff; padding: 20px; }
        .card { max-width: 500px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card p-4 mt-5">
        <h4 class="text-center mb-4">📇 ข้อมูลจากบัตรประชาชน ผู้มาติดติดราชการศาลจังหวัดเชียงราย</h4>
        <ul class="list-group list-group-flush">
            <li class="list-group-item"><strong>เลขบัตร:</strong> <?= htmlspecialchars($data['citizen_id']) ?></li>
            <li class="list-group-item"><strong>คำนำหน้า:</strong> <?= htmlspecialchars($data['full_name']) ?></li>
            <li class="list-group-item"><strong>เพศ:</strong> <?= htmlspecialchars($data['gender'] == 'male' ? 'ชาย' : 'หญิง') ?></li>
        </ul>
    </div>
</body>
</html>

<?php
require 'vendor/autoload.php'; // Composer autoload
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    $spreadsheet = IOFactory::load($file);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray();

    // ข้ามหัวตาราง (row 0)
    for ($i = 1; $i < count($rows); $i++) {
        [$warrant_number, $name, $citizen_id, $black_case, $red_case] = $rows[$i];

        if (!empty($citizen_id)) {
            $stmt = $pdo->prepare("INSERT INTO warrants (warrant_number, name, citizen_id, black_case, red_case)
                                   VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$warrant_number, $name, $citizen_id, $black_case, $red_case]);
        }
    }
    echo "<p style='color: green;'>✅ นำเข้าข้อมูลสำเร็จ</p>";
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>นำเข้าหมายจับ</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h3>📤 อัปโหลดไฟล์ Excel หมายจับ</h3>
    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="excel_file" class="form-control" accept=".xlsx" required>
        <button type="submit" class="btn btn-primary mt-3">นำเข้าข้อมูล</button>
    </form>
</body>
</html>

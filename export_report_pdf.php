<?php
require_once('tcpdf/tcpdf.php');
session_start();

if (!isset($_SESSION['user'])) {
    die("กรุณาเข้าสู่ระบบก่อน");
}
$userName = $_SESSION['user']['full_name'] ?? 'ไม่ทราบชื่อ';

// ฟังก์ชันแปลงวันที่แบบไทย
function thaiDateFull($dateStr) {
    $months = [
        "", "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];
    $ts = strtotime($dateStr);
    $day = date('j', $ts);
    $month = $months[(int)date('n', $ts)];
    $year = (int)date('Y', $ts) + 543;
    return "วันที่ {$day} เดือน{$month} พ.ศ. {$year}";
}

// รับช่วงวันที่
$date_start = $_GET['date_start'] ?? date('Y-m-01');
$date_end   = $_GET['date_end'] ?? date('Y-m-t');

// เชื่อมต่อฐานข้อมูล
$pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ดึงข้อมูล visitors
$stmt = $pdo->prepare("SELECT * FROM visitors WHERE DATE(visit_time) BETWEEN ? AND ?");
$stmt->execute([$date_start, $date_end]);
$data = $stmt->fetchAll();

// สรุปข้อมูล
$male = $female = 0;
$hasWarrant = [];

foreach ($data as $row) {
    if ($row['prefix'] === 'นาย') $male++;
    elseif (in_array($row['prefix'], ['นาง', 'นางสาว'])) $female++;

    // ตรวจหมายจับ
    $stmtCheck = $pdo->prepare("SELECT * FROM warrants WHERE citizen_id = ?");
    $stmtCheck->execute([$row['citizen_id']]);
    if ($stmtCheck->fetch()) {
        $hasWarrant[] = $row;
    }
}

$totalWarrants = count($hasWarrant);
$monthName = thaiDateFull($date_start);
$reportDate = thaiDateFull(date('Y-m-d'));

// สร้าง PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
$pdf->SetTitle('รายงานผู้มาติดต่อราชการ');
$pdf->AddPage();
$pdf->SetFont('thsarabunnew', '', 16);

// 🦅 แสดงรูปครุฑ
$pdf->Image('garuda.png', 90, 10, 30); // ปรับตำแหน่งตามต้องการ
$pdf->Ln(30); // ขยับลง

$html = <<<HTML
<div style="text-align: right;">{$reportDate}</div>

<h3 style="text-align: center; margin-bottom: 10px;">บันทึกข้อความ</h3>

<p><strong>ส่วนราชการ:</strong> ศาลจังหวัดเชียงราย</p>
<p><strong>ที่:</strong> ……………………………………….</p>
<p><strong>วันที่:</strong> {$reportDate}</p>
<p><strong>เรื่อง:</strong> รายงานผู้มาติดต่อราชการ ประจำเดือน {$monthName}</p>
<p><strong>เรียน:</strong> ผู้อำนวยการสำนักอำนวยการประจำศาลจังหวัดเชียงราย</p>

<p style="text-indent: 2em;">
ข้าพเจ้าขอรายงานข้อมูลผู้มาติดต่อราชการ ประจำเดือน <strong>{$monthName}</strong> ดังนี้
</p>

<ul>
  <li>เป็นชาย จำนวน <strong>{$male}</strong> คน</li>
  <li>เป็นหญิง จำนวน <strong>{$female}</strong> คน</li>
  <li>พบผู้มีรายชื่อตามหมายจับ จำนวน <strong>{$totalWarrants}</strong> คน</li>
</ul>
HTML;

if ($totalWarrants > 0) {
    $html .= "<p><u>รายชื่อผู้มีหมายจับ:</u></p><ol>";
    foreach ($hasWarrant as $w) {
        $html .= "<li>" . htmlspecialchars($w['full_name']) . "</li>";
    }
    $html .= "</ol>";
} else {
    $html .= "<p>ไม่พบผู้มีหมายจับในช่วงเวลาดังกล่าว</p>";
}

$html .= <<<HTML
<br><p style="text-align: right; margin-top: 40px;">ผู้รายงาน: <strong>{$userName}</strong></p>
HTML;

// แสดงผล PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output("รายงานผู้มาติดต่อราชการ_{$monthName}.pdf", 'I');
?>

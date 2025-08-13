<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $citizen_id = $_POST['citizen_id'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $reason = $_POST['reason'] ?? '';

    if (!$citizen_id || !$full_name || !$reason) {
        die("⚠️ กรุณากรอกข้อมูลให้ครบถ้วน");
    }

    try {
        $pdo = new PDO("mysql:host=localhost;dbname=warrants_db;charset=utf8mb4", "root", "");
        $stmt = $pdo->prepare("INSERT INTO manual_entries (citizen_id, full_name, reason) VALUES (?, ?, ?)");
        $stmt->execute([$citizen_id, $full_name, $reason]);

        echo "<p style='color:green; font-weight:bold;'>✅ บันทึกข้อมูลสำเร็จ</p>";
        echo "<a href='read_card.php'>🔙 กลับ</a>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>❌ เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage() . "</p>";
    }
} else {
    echo "กรุณาส่งข้อมูลด้วย POST";
}
?>

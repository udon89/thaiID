<?php
$json = @file_get_contents("http://localhost:8080/read.json");
if (!$json) {
    die("❌ ไม่สามารถเชื่อมต่อ API หรือไม่มีข้อมูลบัตร");
}
$data = json_decode($json, true);
print_r($data);

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8" />
    <title>ระบบอ่านบัตรประชาชน | ศาลจังหวัดเชียงราย</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Kanit&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Kanit', sans-serif;
            background-color: #f0f4ff;
            padding: 20px;
        }
        .card {
            max-width: 600px;
            margin: 40px auto;
            box-shadow: 0 0 12px rgba(0,0,0,0.1);
        }
        .status {
            font-weight: 600;
            text-align: center;
            margin-bottom: 12px;
        }
        button {
            width: 100%;
        }
    </style>
</head>
<body>

<div class="card p-4">
    <h3 class="text-center mb-4">📇 ข้อมูลจากบัตรประชาชน ผู้มาติดราชการศาลจังหวัดเชียงราย</h3>

    <div id="status" class="status text-secondary">🔄 กรุณากดปุ่มเพื่ออ่านบัตร</div>

    <ul class="list-group list-group-flush mb-4">
        <li class="list-group-item"><strong>เลขบัตรประชาชน:</strong> <span id="citizen_id">-</span></li>
        <li class="list-group-item"><strong>ชื่อ-นามสกุล:</strong> <span id="full_name">-</span></li>
        <li class="list-group-item"><strong>เพศ:</strong> <span id="gender">-</span></li>
    </ul>

    <button class="btn btn-primary" onclick="readCard()">📥 อ่านข้อมูลบัตร</button>
</div>

<script>
function readCard() {
    const statusEl = document.getElementById('status');
    statusEl.textContent = '🔄 กำลังอ่านบัตร...';
    statusEl.className = 'status text-secondary';

    fetch('http://localhost:8080/read.json')
        .then(response => {
            if (!response.ok) throw new Error('ไม่สามารถเชื่อมต่อ API หรือยังไม่ได้เสียบบัตร');
            return response.json();
        })
        .then(data => {
            document.getElementById('citizen_id').innerText = data.citizen_id || '-';
            document.getElementById('full_name').innerText = data.full_name || '-';
            document.getElementById('gender').innerText = data.gender === 'male' ? 'ชาย' : 'หญิง';
            

            statusEl.textContent = '✅ อ่านบัตรสำเร็จ';
            statusEl.className = 'status text-success';
        })
        .catch(err => {
            statusEl.textContent = '⚠️ ไม่พบบัตร หรือ API ไม่ตอบสนอง';
            statusEl.className = 'status text-danger';

            document.getElementById('citizen_id').innerText = '-';
            document.getElementById('full_name').innerText = '-';
            document.getElementById('gender').innerText = '-';
            
        });
}
</script>

</body>
</html>

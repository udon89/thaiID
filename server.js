const express = require('express');
const cors = require('cors');
const axios = require('axios');
const { ThaiCardReader, EVENTS, MODE } = require('@privageapp/thai-national-id-reader');

const app = express();
const port = 8080;
app.use(cors());

let cardData = null;
let cojResult = null;

const reader = new ThaiCardReader();
reader.readMode = MODE.PERSONAL;
reader.startListener();

async function fetchCojApi(payload, headers, retries = 2, delayMs = 2000) {
  for (let i = 0; i < retries; i++) {
    try {
      console.log(`⚡️ กำลังเรียก COJ API ครั้งที่ ${i + 1}`);
      const response = await axios.post(
        "http://10.35.44.6:8089/cojProceed/api/v1/proceed/searchElectronicAppointDateByCase/search?version=1",
        payload,
        { headers, timeout: 30000 } // 30 วินาที
      );
      return response.data;
    } catch (error) {
      if (i === retries - 1) {
        console.error(`❌ ไม่สามารถดึงข้อมูลจาก COJ API: ${error.message}`);
        throw error;
      } else {
        console.warn(`⚠️ ลองใหม่ครั้งที่ ${i + 1} หลัง timeout หรือ error: ${error.message}`);
        await new Promise(res => setTimeout(res, delayMs));
      }
    }
  }
}

reader.on(EVENTS.READING_COMPLETE, async (data) => {
  cardData = {
    citizen_id: data.citizenId,
    full_name: `${data.titleTH} ${data.firstNameTH} ${data.lastNameTH}`,
    first_name: data.firstNameTH,
    last_name: data.lastNameTH,
    title: data.titleTH,
    gender: data.gender,
    dob: data.dateOfBirth,
    timestamp: new Date().toISOString()
  };

  console.log(`[✓] อ่านบัตร: ${cardData.full_name}`);

  const token = "eyJwZmlkIjo1MCwibmFtZSI6IjIwMTIiLCJ0eXAiOiJKV1QiLCJleHAiOjE3NTA4NTE1NjYsImlhdCI6MTc1MDg0OTc2NiwiYWxnIjoiSFMyNTYiLCJwZnR5cCI6Mn0.eyJwZmlkIjo1MCwiaXNzIjoiaHR0cHM6Ly9wcmF4aXMuY28udGgvIiwibmFtZSI6IjIwMTIiLCJwZnR5cCI6Mn0.clr5VNu6setWHelgPKQqQnQoUt3nW6xugiAS1xSq9lo";

  const payload = {
    courtCode: "001",
    caseNumber: "",
    caseType: "",
    dateStart: "2025-06-01",
    dateEnd: "2025-06-30",
    fullName: `${data.firstNameTH} ${data.lastNameTH}`
  };

  const headers = {
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json'
  };

  try {
    cojResult = await fetchCojApi(payload, headers);
    console.log(`📌 พบข้อมูลจาก COJ: ${JSON.stringify(cojResult).substring(0, 100)}...`);
  } catch {
    cojResult = null;
  }
});

app.get('/read.json', (req, res) => {
  if (cardData) {
    res.json({ card: cardData, coj: cojResult });
  } else {
    res.status(404).json({ error: 'ยังไม่ได้เสียบบัตร หรือกำลังอ่านบัตร' });
  }
});

app.listen(port, () => {
  console.log(`✅ API พร้อมที่ http://localhost:${port}/read.json`);
});

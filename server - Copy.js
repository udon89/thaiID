// server.js
const express = require('express');
const cors = require('cors');
const { ThaiCardReader, EVENTS, MODE } = require('@privageapp/thai-national-id-reader');

const app = express();
const port = 8080;

// เปิดใช้งาน CORS เพื่อให้ fetch จากเว็บอื่นได้
app.use(cors());

let cardData = null;

const reader = new ThaiCardReader();
reader.readMode = MODE.PERSONAL;
reader.startListener();

reader.on(EVENTS.READING_COMPLETE, (data) => {
  cardData = {
    citizen_id: data.citizenId,
    full_name: `${data.titleTH} ${data.firstNameTH} ${data.lastNameTH}`,
    first_name: data.firstNameTH,
    last_name: data.lastNameTH,
    title: data.titleTH,
    gender: data.gender,
    dob: data.dateOfBirth,
    issuer: data.issuer,
    issue_date: data.cardIssueDate,
    expire_date: data.cardExpireDate,
    timestamp: new Date().toISOString()
  };
  console.log(`[✓] อ่านบัตรใหม่: ${cardData.full_name}`);
});

app.get('/read.json', (req, res) => {
  if (cardData) {
    res.json(cardData);
  } else {
    res.status(404).json({ error: 'ยังไม่ได้เสียบบัตร หรือกำลังอ่านบัตร' });
  }
});

app.listen(port, () => {
  console.log(`✅ API พร้อมใช้งานที่ http://localhost:${port}/read.json`);
});

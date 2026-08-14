<?php
/**
 * Web GUI Tester for PembdaHUB Free WhatsApp Notifications
 * Access: http://localhost/pembdahub/public/test_wa_gui.php
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\WhatsAppService;

$phone = $_POST['phone'] ?? '';
$type = $_POST['type'] ?? 'attendance';
$customMsg = $_POST['custom_msg'] ?? '';

$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($phone)) {
    $service = new WhatsAppService();

    if ($type === 'attendance') {
        $message = "📢 *NOTIFIKASI KEHADIRAN SISWA (PEMBDAHUB)*\n\nHalo Orang Tua/Wali dari *Ahmad Fajar*,\n\nMenginfokan bahwa siswa telah *HADIR* di Perguruan Pembda hari ini pukul 07.15 WIB.\n\nTerima kasih. 🙏";
    } elseif ($type === 'spp') {
        $message = "✅ *PEMBAYARAN SPP BERHASIL! (PEMBDAHUB)*\n\nHalo Bpk/Ibu Wali Murid,\n\nPembayaran SPP bulan Agustus 2026 sebesar *Rp 350.000* untuk siswa *Ahmad Fajar* telah BERHASIL diterima oleh Bendahara Sekolah.\n\nTerima kasih atas pembayaran tepat waktu! 🙏";
    } elseif ($type === 'grade') {
        $message = "📊 *PENGUMUMAN NILAI UJIAN (PEMBDAHUB)*\n\nHalo *Ahmad Fajar*,\n\nNilai Ulangan Harian Matematika Anda telah diterbitkan:\n💯 Nilai: *92 (Sangat Baik)*\n\nTingkatkan terus prestasimu! 🚀";
    } else {
        $message = $customMsg ?: "📢 *UJI COBA WHATSAPP ENGINE PEMBDAHUB ($0 COST)*\n\nPesan pengujian dari sistem PembdaHUB lokal berhasil terkirim!";
    }

    $result = $service->sendMessage($phone, $message);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Uji Coba Pengiriman WA PembdaHUB</title>
    <style>
        body { background: #0b141a; color: #e9edef; font-family: system-ui, sans-serif; padding: 30px; max-width: 600px; margin: 0 auto; }
        .card { background: #111b21; border: 1px solid #222d34; padding: 25px; border-radius: 12px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 13px; color: #8696a0; }
        input[type="text"], select, textarea { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #2a3942; background: #202c33; color: white; box-sizing: border-border; font-size: 14px; }
        button { width: 100%; background: #00a884; color: white; border: none; padding: 12px; border-radius: 8px; font-weight: bold; font-size: 14px; cursor: pointer; }
        button:hover { background: #008f70; }
        .alert { padding: 12px; border-radius: 8px; margin-top: 15px; font-size: 13px; }
        .alert-success { background: rgba(37, 211, 102, 0.2); border: 1px solid #25d366; color: #25d366; }
        .alert-error { background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="color: white; margin-top:0;">🧪 Uji Coba Pengiriman WA PembdaHUB</h2>
        <p style="color: #8696a0; font-size: 13px;">Gunakan halaman ini untuk menguji pengiriman pesan WA otomatis ke HP Anda.</p>

        <form method="POST">
            <div class="form-group">
                <label>Nomor WhatsApp Tujuan (Contoh: 081234567890):</label>
                <input type="text" name="phone" required placeholder="08xxxxxxxxxx" value="<?php echo htmlspecialchars($phone); ?>">
            </div>

            <div class="form-group">
                <label>Pilih Jenis Notifikasi Uji Coba:</label>
                <select name="type">
                    <option value="attendance" <?php if($type==='attendance') echo 'selected'; ?>>📌 Notifikasi Absensi Siswa</option>
                    <option value="spp" <?php if($type==='spp') echo 'selected'; ?>>💳 Notifikasi Pembayaran SPP</option>
                    <option value="grade" <?php if($type==='grade') echo 'selected'; ?>>📊 Pengumuman Nilai Ujian</option>
                    <option value="custom" <?php if($type==='custom') echo 'selected'; ?>>💬 Pesan Kustom (Ketik Sendiri)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Pesan Kustom (Opsional jika memilih Pesan Kustom):</label>
                <textarea name="custom_msg" rows="3" placeholder="Tulis pesan uji coba di sini..."><?php echo htmlspecialchars($customMsg); ?></textarea>
            </div>

            <button type="submit">🚀 Kirim Pesan Uji Coba Sekarang</button>
        </form>

        <?php if ($result): ?>
            <?php if (!empty($result['success'])): ?>
                <div class="alert alert-success">
                    ✅ <strong>BERHASIL TERKIRIM!</strong> Pesan WA uji coba telah dikirim ke nomor HP Anda. Cek aplikasi WhatsApp di HP Anda sekarang!
                </div>
            <?php else: ?>
                <div class="alert alert-error">
                    ❌ <strong>GAGAL:</strong> <?php echo htmlspecialchars($result['response']['message'] ?? $result['error'] ?? 'Terjadi kesalahan'); ?><br>
                    <small>Pastikan Anda sudah scan QR Code di <a href="http://localhost:3000/qr" target="_blank" style="color:white;">http://localhost:3000/qr</a></small>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - Konfirmasi Pembayaran PSB</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
        }
        .content {
            padding: 30px;
        }
        .status-badge {
            display: inline-block;
            background: #dcfce7;
            color: #166534;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .payment-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .row:last-child {
            border-bottom: none;
        }
        .footer {
            background: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 13px;
        }
        .btn {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>✅ Pembayaran Berhasil Diverifikasi</h1>
            <p style="margin:5px 0 0 0; opacity:0.9;">Penerimaan Siswa Baru - {{ $applicant->school->name ?? 'Yayasan PEMBDA' }}</p>
        </div>

        <div class="content">
            <div class="status-badge">✓ Status: LUNAS & TERVERIFIKASI</div>

            <p>Halo <b>{{ $applicant->full_name }}</b>,</p>
            <p>Terima kasih! Pembayaran biaya pendaftaran PSB Anda telah diverifikasi oleh Bendahara Yayasan Perguruan PEMBDA Nias.</p>

            <div class="payment-card">
                <div class="row">
                    <span style="color:#64748b;">No. Registrasi:</span>
                    <span style="font-weight:bold; font-family:monospace;">{{ $applicant->registration_number }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Nama Calon Siswa:</span>
                    <span style="font-weight:bold;">{{ $applicant->full_name }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Unit Sekolah Tujuan:</span>
                    <span style="font-weight:bold;">{{ $applicant->school->name ?? '-' }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Waktu Verifikasi:</span>
                    <span style="font-weight:bold;">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
                </div>
            </div>

            <div style="background:#f0fdf4; border-left:4px solid #10b981; padding:15px; border-radius:4px; margin:20px 0;">
                <h4 style="margin:0 0 5px 0; color:#166534;">Tahap Selanjutnya:</h4>
                <p style="margin:0; font-size:13px; color:#15803d;">
                    Panitia akan memvalidasi kelengkapan berkas fisik / upload Anda. Jadwal ujian masuk dan lokasi tes akan dikirimkan melalui WhatsApp dan Email terdaftar.
                </p>
            </div>

            <center>
                <a href="{{ route('public.registration.check') }}" class="btn">Cek Status Pendaftaran</a>
            </center>
        </div>

        <div class="footer">
            <p style="margin:0 0 5px 0;">&copy; {{ date('Y') }} Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
            <p style="margin:0; opacity:0.7;">Jl. K.H. Dewantara No. 1, Gunungsitoli, Sumatera Utara</p>
        </div>
    </div>
</body>
</html>

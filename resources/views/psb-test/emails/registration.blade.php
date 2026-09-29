<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - Konfirmasi Pendaftaran PSB</title>
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
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .reg-number {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .reg-number h2 {
            margin: 0 0 10px 0;
            font-size: 32px;
            letter-spacing: 2px;
        }
        .info-box {
            background: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .step {
            display: flex;
            align-items: start;
            margin: 15px 0;
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .step-number {
            background: #10b981;
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 15px;
            flex-shrink: 0;
        }
        .payment-box {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .bank-info {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .footer {
            background: #1f2937;
            color: white;
            padding: 20px;
            text-align: center;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 10px 0;
        }
        .contact-info {
            background: #eff6ff;
            border: 2px solid #3b82f6;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        {{-- Header --}}
        <div class="header">
            <h1>✅ PENDAFTARAN BERHASIL!</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">PSB/PPDB TP. 2026/2027</p>
        </div>

        {{-- Content --}}
        <div class="content">
            <p>Kepada Yth. <strong>{{ $applicant->full_name }}</strong>,</p>
            
            <p>Terima kasih telah mendaftar di <strong>Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</strong>!</p>

            <div class="reg-number">
                <p style="margin: 0; font-size: 14px; opacity: 0.9;">Nomor Registrasi Anda</p>
                <h2>{{ $applicant->registration_number }}</h2>
                <p style="margin: 5px 0 0 0; font-size: 12px; opacity: 0.8;">⚠️ Simpan nomor ini untuk cek status pendaftaran</p>
            </div>

            <div class="info-box">
                <h3 style="margin-top: 0;">📋 Data Pendaftaran:</h3>
                <table style="width: 100%;">
                    <tr>
                        <td style="padding: 5px 0;"><strong>Nama Lengkap</strong></td>
                        <td style="padding: 5px 0;">: {{ $applicant->full_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>NISN</strong></td>
                        <td style="padding: 5px 0;">: {{ $applicant->nisn }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Sekolah Tujuan</strong></td>
                        <td style="padding: 5px 0;">: {{ $applicant->school->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;"><strong>Tanggal Daftar</strong></td>
                        <td style="padding: 5px 0;">: {{ $applicant->created_at->format('d/m/Y H:i') }} WIB</td>
                    </tr>
                </table>
            </div>

            <h3 style="color: #10b981;">🎯 LANGKAH SELANJUTNYA:</h3>

            <div class="step">
                <div class="step-number">1</div>
                <div>
                    <h4 style="margin: 0 0 10px 0;">Lakukan Pembayaran</h4>
                    <p style="margin: 0;">Biaya pendaftaran <strong>Rp {{ number_format($applicant->school_id == 3 ? 300000 : 50000, 0, ',', '.') }}</strong>. Transfer ke rekening di bawah.</p>
                </div>
            </div>

            <div class="payment-box">
                <h4 style="margin-top: 0; color: #b45309;">💳 Informasi Pembayaran</h4>
                <div class="bank-info">
                    <p style="margin: 5px 0; color: #6b7280; font-size: 13px;">Bank BCA</p>
                    <p style="margin: 5px 0; font-size: 24px; font-weight: bold; color: #1f2937;">1234567890</p>
                    <p style="margin: 5px 0; color: #6b7280; font-size: 14px;">a.n. Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
                </div>
                <div style="background: white; padding: 15px; border-radius: 8px; margin-top: 10px;">
                    <p style="margin: 5px 0; color: #6b7280; font-size: 13px;">Jumlah Transfer</p>
                    <p style="margin: 5px 0; font-size: 28px; font-weight: bold; color: #10b981;">Rp {{ number_format($applicant->school_id == 3 ? 300000 : 50000, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div>
                    <h4 style="margin: 0 0 10px 0;">Kirim Bukti Pembayaran</h4>
                    <p style="margin: 0;">Foto/Screenshot bukti transfer dan kirim ke:</p>
                    <ul style="margin: 10px 0; padding-left: 20px;">
                        <li><strong>WhatsApp:</strong> 082373642864</li>
                        <li><strong>Email:</strong> psb@pembdanias.sch.id</li>
                    </ul>
                    <p style="margin: 5px 0; font-size: 13px; color: #6b7280;">Sertakan nomor registrasi: <strong>{{ $applicant->registration_number }}</strong></p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div>
                    <h4 style="margin: 0 0 10px 0;">Tunggu Verifikasi</h4>
                    <p style="margin: 0;">Pembayaran akan diverifikasi maksimal <strong>1x24 jam</strong> (hari kerja). Anda akan menerima email & WhatsApp konfirmasi.</p>
                </div>
            </div>

            <div class="step">
                <div class="step-number">4</div>
                <div>
                    <h4 style="margin: 0 0 10px 0;">Siapkan Dokumen</h4>
                    <p style="margin: 0 0 5px 0;">Dokumen yang perlu disiapkan:</p>
                    <ul style="margin: 5px 0; padding-left: 20px;">
                        <li>Fotocopy Kartu Keluarga (KK)</li>
                        <li>Fotocopy Akta Kelahiran</li>
                        <li>Fotocopy Ijazah/SKHUN (bila sudah ada)</li>
                        <li>Pas Foto 3x4 (3 lembar, warna)</li>
                    </ul>
                </div>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <a href="http://127.0.0.1:8000/pendaftaran/cek-status" class="button">
                    🔍 CEK STATUS PENDAFTARAN
                </a>
            </div>

            <div class="contact-info">
                <h4 style="margin-top: 0; color: #1e40af;">📞 Butuh Bantuan?</h4>
                <p style="margin: 5px 0;"><strong>WhatsApp:</strong> 082373642864</p>
                <p style="margin: 5px 0;"><strong>Telepon:</strong> (0639) xxxxx</p>
                <p style="margin: 5px 0;"><strong>Email:</strong> psb@pembdanias.sch.id</p>
                <p style="margin: 10px 0 0 0; font-size: 13px; color: #6b7280;">
                    <strong>Jam Operasional:</strong> Senin - Jumat (08:00 - 16:00 WIB), Sabtu (08:00 - 12:00 WIB)
                </p>
            </div>

            <p style="margin-top: 30px;">Hormat kami,<br><strong>Panitia PSB/PPDB<br>YAYASAN PEMBDA NIAS</strong></p>
        </div>

        {{-- Footer --}}
        <div class="footer">
            <p style="margin: 0;">© 2026 Yayasan Perguruan Pembangunan Daerah Nias</p>
            <p style="margin: 5px 0 0 0; opacity: 0.8; font-size: 12px;">Membangun Generasi Nias yang Cerdas, Berkarakter, dan Berdaya Saing</p>
        </div>
    </div>
</body>
</html>


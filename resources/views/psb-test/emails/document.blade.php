<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - Verifikasi Berkas PSB</title>
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
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
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
        .info-card {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
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
            background: #0284c7;
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
            <h1>📑 Berkas Pendaftaran Terverifikasi</h1>
            <p style="margin:5px 0 0 0; opacity:0.9;">Penerimaan Siswa Baru - {{ $applicant->school->name ?? 'Yayasan PEMBDA' }}</p>
        </div>

        <div class="content">
            <p>Halo <b>{{ $applicant->full_name }}</b>,</p>
            <p>Kabar baik! Seluruh dokumen persyaratan pendaftaran PSB Anda telah diperiksa dan dinyatakan <b>LENGKAP & VALID</b> oleh Panitia PSB.</p>

            <div class="info-card">
                <div class="row">
                    <span style="color:#64748b;">No. Registrasi:</span>
                    <span style="font-weight:bold; font-family:monospace;">{{ $applicant->registration_number }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Nama Calon Siswa:</span>
                    <span style="font-weight:bold;">{{ $applicant->full_name }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Asal Sekolah:</span>
                    <span style="font-weight:bold;">{{ $applicant->previous_school }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Status Berkas:</span>
                    <span style="color:#0369a1; font-weight:bold;">✓ VALID & TERVERIFIKASI</span>
                </div>
            </div>

            <p style="font-size:13px; color:#475569;">
                Kartu Peserta Tes dan Nomor Ujian Anda sekarang sudah dapat diunduh melalui portal PSB. Pastikan membawa kartu ujian dan identitas diri saat mengikuti tes seleksi.
            </p>

            <center>
                <a href="{{ route('public.registration.check') }}" class="btn">Unduh Kartu Peserta Ujian</a>
            </center>
        </div>

        <div class="footer">
            <p style="margin:0 0 5px 0;">&copy; {{ date('Y') }} Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
            <p style="margin:0; opacity:0.7;">Jl. K.H. Dewantara No. 1, Gunungsitoli, Sumatera Utara</p>
        </div>
    </div>
</body>
</html>

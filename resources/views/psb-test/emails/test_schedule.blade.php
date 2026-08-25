<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - Jadwal Ujian Masuk PSB</title>
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
            background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
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
        .schedule-box {
            background: #faf5ff;
            border: 2px solid #c084fc;
            border-radius: 10px;
            padding: 20px;
            margin: 20px 0;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #e9d5ff;
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
            background: #7c3aed;
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
            <h1>🗓️ Jadwal Ujian Masuk PSB</h1>
            <p style="margin:5px 0 0 0; opacity:0.9;">Penerimaan Siswa Baru - {{ $applicant->school->name ?? 'Yayasan PEMBDA' }}</p>
        </div>

        <div class="content">
            <p>Halo <b>{{ $applicant->full_name }}</b>,</p>
            <p>Berikut adalah jadwal pelaksanaan tes seleksi penerimaan siswa baru yang wajib Anda ikuti:</p>

            <div class="schedule-box">
                <div class="row">
                    <span style="color:#6b21a8; font-weight:bold;">No. Registrasi:</span>
                    <span style="font-weight:bold; font-family:monospace;">{{ $applicant->registration_number }}</span>
                </div>
                <div class="row">
                    <span style="color:#6b21a8; font-weight:bold;">Tanggal Ujian:</span>
                    <span style="font-weight:bold; color:#581c87;">{{ $applicant->test_date ? \Carbon\Carbon::parse($applicant->test_date)->translatedFormat('l, d F Y') : 'Sesuai Jadwal Gelombang' }}</span>
                </div>
                <div class="row">
                    <span style="color:#6b21a8; font-weight:bold;">Waktu:</span>
                    <span style="font-weight:bold;">08:00 WIB - Selesai</span>
                </div>
                <div class="row">
                    <span style="color:#6b21a8; font-weight:bold;">Lokasi / Tempat:</span>
                    <span style="font-weight:bold;">Aula Kampus Yayasan PEMBDA Nias</span>
                </div>
                <div class="row">
                    <span style="color:#6b21a8; font-weight:bold;">Materi Ujian:</span>
                    <span>Tes Potensi Akademik, Bahasa & Wawancara</span>
                </div>
            </div>

            <div style="background:#fffbeb; border-left:4px solid #f59e0b; padding:12px; border-radius:4px; font-size:12px; color:#92400e; margin:15px 0;">
                <b>Tata Tertib Ujian:</b>
                <ul style="margin:5px 0 0 0; padding-left:18px;">
                    <li>Hadir 30 menit sebelum ujian dimulai.</li>
                    <li>Membawa Kartu Peserta Ujian dan Alat Tulis.</li>
                    <li>Mengenakan pakaian seragam sekolah asal yang rapi dan bersepatu.</li>
                </ul>
            </div>

            <center>
                <a href="{{ route('public.registration.check-status') }}" class="btn">Lihat Lokasi & Cetak Kartu</a>
            </center>
        </div>

        <div class="footer">
            <p style="margin:0 0 5px 0;">&copy; {{ date('Y') }} Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
            <p style="margin:0; opacity:0.7;">Jl. K.H. Dewantara No. 1, Gunungsitoli, Sumatera Utara</p>
        </div>
    </div>
</body>
</html>

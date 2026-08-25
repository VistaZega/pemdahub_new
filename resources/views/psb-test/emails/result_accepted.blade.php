<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email - Pengumuman Kelulusan PSB</title>
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
            background: linear-gradient(135deg, #059669 0%, #047857 100%);
            color: white;
            padding: 35px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
        }
        .content {
            padding: 30px;
        }
        .congrats-card {
            background: #ecfdf5;
            border: 2px solid #34d399;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        .congrats-title {
            font-size: 22px;
            font-weight: bold;
            color: #065f46;
            margin: 0 0 10px 0;
        }
        .school-badge {
            display: inline-block;
            background: #059669;
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 16px;
            font-weight: bold;
            margin: 10px 0;
        }
        .details-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: left;
        }
        .row {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            font-size: 13px;
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
            background: #059669;
            color: white;
            padding: 14px 30px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 15px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>🎉 SURAT KEPUTUSAN KELULUSAN</h1>
            <p style="margin:5px 0 0 0; opacity:0.9;">Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
        </div>

        <div class="content">
            <div class="congrats-card">
                <div class="congrats-title">SELAMAT! ANDA DINYATAKAN DITERIMA</div>
                <p style="margin:0; font-size:14px; color:#047857;">Sebagai Peserta Didik Baru Tahun Ajaran {{ $applicant->academicYear->name ?? date('Y').'/'.(date('Y')+1) }}</p>
                <div class="school-badge">{{ $applicant->school->name ?? 'Perguruan PEMBDA' }}</div>
            </div>

            <p>Halo <b>{{ $applicant->full_name }}</b>,</p>
            <p>Berdasarkan hasil seleksi administrasi, tes potensi akademik, dan wawancara PSB, kami dengan bangga menyambut Anda menjadi bagian dari keluarga besar Yayasan Perguruan PEMBDA Nias.</p>

            <div class="details-box">
                <div class="row">
                    <span style="color:#64748b;">No. Registrasi:</span>
                    <span style="font-weight:bold; font-family:monospace;">{{ $applicant->registration_number }}</span>
                </div>
                <div class="row">
                    <span style="color:#64748b;">Nama Lengkap:</span>
                    <span style="font-weight:bold;">{{ $applicant->full_name }}</span>
                </div>
                @if($applicant->programKeahlian)
                <div class="row">
                    <span style="color:#64748b;">Program Keahlian:</span>
                    <span style="font-weight:bold;">{{ $applicant->programKeahlian->name }}</span>
                </div>
                @endif
                <div class="row">
                    <span style="color:#64748b;">Status Kelulusan:</span>
                    <span style="color:#059669; font-weight:bold;">DITERIMA (LULUS MURNI)</span>
                </div>
            </div>

            <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:15px; border-radius:4px; font-size:13px; color:#92400e; margin:20px 0;">
                <h4 style="margin:0 0 6px 0; color:#b45309;">Langkah Wajib: Daftar Ulang</h4>
                <p style="margin:0;">
                    Silakan lakukan proses <b>Daftar Ulang</b> secara online melalui portal PSB atau hadir langsung ke sekretariat PSB di Kampus PEMBDA dengan membawa dokumen fisik asli dan bukti kelulusan ini.
                </p>
            </div>

            <center>
                <a href="{{ route('public.registration.check') }}" class="btn">Unduh SK Kelulusan & Daftar Ulang</a>
            </center>
        </div>

        <div class="footer">
            <p style="margin:0 0 5px 0;">&copy; {{ date('Y') }} Yayasan Perguruan Pembangunan Daerah Nias (PEMBDA)</p>
            <p style="margin:0; opacity:0.7;">Jl. K.H. Dewantara No. 1, Gunungsitoli, Sumatera Utara</p>
        </div>
    </div>
</body>
</html>

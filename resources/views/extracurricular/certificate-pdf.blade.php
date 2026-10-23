<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Sertifikat Kelulusan {{ $ekskul->name }} - {{ $student->full_name }}</title>
    <style>
        @page {
            size: a4 landscape;
            margin: 1.2cm;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .cert-frame {
            border: 5px solid #1e1b4b;
            border-radius: 18px;
            padding: 10px;
            background: #fdfbf7;
        }
        .cert-inner {
            border: 2px solid #d97706;
            border-radius: 12px;
            padding: 28px 36px;
            text-align: center;
            position: relative;
        }
        .header-org {
            font-size: 13px;
            font-weight: bold;
            letter-spacing: 2px;
            color: #4338ca;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .header-school {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            margin-bottom: 12px;
        }
        .cert-title {
            font-size: 28px;
            font-weight: bold;
            color: #1e1b4b;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin: 6px 0 4px 0;
        }
        .cert-subtitle {
            font-size: 12px;
            font-weight: bold;
            color: #b45309;
            letter-spacing: 1.5px;
            margin-bottom: 16px;
        }
        .cert-given {
            font-size: 12px;
            color: #475569;
            margin-bottom: 6px;
        }
        .student-name {
            font-size: 28px;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #cbd5e1;
            display: inline-block;
            padding: 0 30px 5px 30px;
            margin: 4px 0 8px 0;
        }
        .student-meta {
            font-size: 11px;
            color: #334155;
            font-weight: bold;
            margin-bottom: 14px;
        }
        .ekskul-box {
            background-color: #eef2ff;
            border: 1px solid #c7d2fe;
            border-radius: 10px;
            padding: 12px 20px;
            margin: 8px auto 14px auto;
            width: 86%;
        }
        .ekskul-name {
            font-size: 18px;
            font-weight: bold;
            color: #312e81;
            margin-bottom: 6px;
        }
        .score-pill {
            display: inline-block;
            background-color: #065f46;
            color: #ffffff;
            font-size: 12px;
            font-weight: bold;
            padding: 4px 14px;
            border-radius: 20px;
            margin: 4px 0;
        }
        .desc-text {
            font-size: 11px;
            color: #334155;
            line-height: 1.45;
            margin-top: 6px;
            font-style: italic;
        }
        .sign-table {
            width: 100%;
            margin-top: 22px;
            border-collapse: collapse;
        }
        .sign-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: bottom;
            font-size: 11px;
            color: #1e293b;
        }
        .sign-name {
            font-weight: bold;
            text-decoration: underline;
            margin-top: 48px;
            font-size: 12px;
        }
        .cert-code {
            font-size: 10px;
            color: #64748b;
            font-family: monospace;
            margin-top: 6px;
        }
    </style>
</head>
<body>
    @php
        $isEnglishClub = str_contains(strtolower($ekskul->name), 'english');
        $schoolName = $school?->name ?? 'SMAS Swasta Pembda 1 Gunungsitoli';
        $classroomName = $student->currentClassroom->first()?->class_name ?? ($student->classroom?->class_name ?? '-');
        $advisorName = $ekskul->manager_name ?: ($ekskul->advisor_name ?: ($ekskul->advisor?->full_name ?? 'Pembina Ekstrakurikuler'));
        $principalName = $school?->principal_name ?? 'Kepala Sekolah';
        $issuedDate = ($member->certificate_issued_at ?? now())->translatedFormat('d F Y');
    @endphp
    <div class="cert-frame">
        <div class="cert-inner">
            <div class="header-org">YAYASAN PERGURUAN PEMBDA NIAS</div>
            <div class="header-school">{{ $schoolName }}</div>

            <div class="cert-title">
                {{ $isEnglishClub ? 'CERTIFICATE OF COMPLETION & EXCELLENCE' : 'SERTIFIKAT KELULUSAN EKSTRAKURIKULER' }}
            </div>
            <div class="cert-subtitle">
                NOMOR SERTIFIKAT: {{ $member->certificate_code }}
            </div>

            <div class="cert-given">
                {{ $isEnglishClub ? 'This certificate is proudly presented to / Diberikan dengan bangga kepada:' : 'Sertifikat ini diberikan dengan bangga kepada:' }}
            </div>

            <div class="student-name">{{ $student->full_name }}</div>

            <div class="student-meta">
                NISN: {{ $student->nisn ?: '-' }} &bull; Kelas: {{ $classroomName }} &bull; Jabatan: {{ $member->role_label }}
            </div>

            <div class="ekskul-box">
                <div class="ekskul-name">
                    {{ $ekskul->display_icon }} {{ strtoupper($ekskul->name) }}
                </div>

                @if($member->final_score !== null)
                <div class="score-pill">
                    Nilai Akhir Extra: {{ number_format($member->final_score, 1) }} / 100
                    &nbsp;|&nbsp;
                    Predikat: {{ strtoupper($member->predicate ?? 'BAIK') }} ({{ $member->predicate_letter }})
                </div>
                @endif

                @if($member->avg_assignment_score !== null || $member->avg_quiz_score !== null)
                <div style="font-size: 10px; color: #475569; margin-top: 4px; font-weight: bold;">
                    Rata-rata Tugas LMS: {{ $member->avg_assignment_score !== null ? number_format($member->avg_assignment_score, 1) : '-' }}
                    &nbsp;&bull;&nbsp;
                    Rata-rata Quiz LMS: {{ $member->avg_quiz_score !== null ? number_format($member->avg_quiz_score, 1) : '-' }}
                    @if($member->attendance_score !== null)
                    &nbsp;&bull;&nbsp;
                    Nilai Kehadiran: {{ number_format($member->attendance_score, 1) }}
                    @endif
                </div>
                @endif

                <div class="desc-text">
                    "{{ $member->grade_description ?: \App\Models\ExtracurricularMember::generateDefaultDescription($ekskul->name, $member->predicate ?? 'Baik') }}"
                </div>
            </div>

            <table class="sign-table">
                <tr>
                    <td>
                        <div>Mengetahui,</div>
                        <div><b>Kepala {{ $school?->short_name ?? 'Sekolah' }}</b></div>
                        <div class="sign-name">{{ $principalName }}</div>
                    </td>
                    <td>
                        <div style="margin-bottom: 6px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=75x75&data={{ urlencode(url('/') . ' | ' . $member->certificate_code . ' | ' . $student->full_name . ' | ' . $ekskul->name . ' | Nilai: ' . $member->final_score) }}"
                                 style="width: 68px; height: 68px; border: 1px solid #cbd5e1; padding: 3px; border-radius: 6px; background: #fff;"
                                 alt="QR Verification">
                        </div>
                        <div class="cert-code">Terverifikasi PembdaHUB LMS</div>
                    </td>
                    <td>
                        <div>Gunungsitoli, {{ $issuedDate }}</div>
                        <div><b>Guru Pembina {{ $ekskul->name }}</b></div>
                        <div class="sign-name">{{ $advisorName }}</div>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</body>
</html>

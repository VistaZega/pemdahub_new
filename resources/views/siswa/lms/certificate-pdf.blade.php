<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $certificate->title }}</title>
    <style>
        @page { margin: 2.5cm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #1e293b; }
        .border-outer { border: 3px solid #7c3aed; border-radius: 20px; padding: 40px; position: relative; }
        .border-inner { border: 2px solid #f59e0b; border-radius: 15px; padding: 30px; }
        h1 { font-size: 28px; color: #7c3aed; margin-bottom: 5px; }
        h2 { font-size: 22px; color: #1e293b; margin: 5px 0; }
        .student-name { font-size: 36px; color: #0f172a; font-weight: bold; margin: 15px 0; }
        .course-name { font-size: 24px; color: #6366f1; font-weight: bold; margin: 10px 0; }
        .detail { font-size: 14px; color: #64748b; margin: 4px 0; }
        .footer { margin-top: 40px; text-align: center; font-size: 12px; color: #94a3b8; }
        .code { font-size: 11px; color: #94a3b8; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="border-outer">
        <div class="border-inner" style="text-align:center;">
            <div style="font-size:60px;margin-bottom:10px;">🏆</div>
            <h1>SERTIFIKAT PENYELESAIAN</h1>
            <p style="font-size:14px;color:#64748b;">Dengan ini menyatakan bahwa</p>
            <div class="student-name">{{ $certificate->student->full_name }}</div>
            <p style="font-size:14px;color:#64748b;">Telah menyelesaikan kursus</p>
            <div class="course-name">{{ $certificate->course->course_name ?? $certificate->course->name }}</div>
            <p class="detail">{{ $certificate->course->subject->subject_name ?? '' }}</p>
            <p class="detail" style="margin-top:20px;">
                Progress: 100%  
                @if($certificate->final_score) | Nilai Akhir: {{ $certificate->final_score }} @endif
            </p>
            <p class="detail">Diterbitkan: {{ $certificate->issued_at ? $certificate->issued_at->format('d F Y') : '-' }}</p>
            <div class="code">No: {{ $certificate->certificate_code }}</div>
            <div class="footer">
                © {{ date('Y') }} Yayasan Perguruan Pembda Nias — PembdaHUB LMS
            </div>
        </div>
    </div>
</body>
</html>
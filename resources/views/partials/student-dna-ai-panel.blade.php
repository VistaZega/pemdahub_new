@php
    $sp = $analysis['subject_profile'] ?? [];
    $ev = $analysis['evidence_360'] ?? [];
    $rec = $analysis['recommendations'] ?? [];
    $aiReport = $analysis['ai_report'] ?? null;
    $aiMeta = $analysis['ai_meta'] ?? [];
    $topSubjects = $sp['top_subjects'] ?? [];
    $weakSubjects = $sp['weak_subjects'] ?? [];
    $dreamAlign = $rec['dream_alignment'] ?? null;
    $fallbackPayload = [
        'executive_summary'   => $analysis['archetype']['description'] ?? '',
        'unique_strengths'    => collect($topSubjects)->map(fn($s) => $s['subject_name'] . ' (Nilai ' . $s['score'] . ')' . ($s['delta'] !== null ? ' — selisih ' . ($s['delta'] >= 0 ? '+' : '') . $s['delta'] . ' dari rata-rata sekolah' : ''))->values()->toArray(),
        'growth_priorities'   => collect($weakSubjects)->map(fn($s) => $s['subject_name'] . ' (Nilai ' . $s['score'] . ') — disarankan penguatan materi inti secara bertahap')->values()->toArray(),
        'dream_alignment'     => $dreamAlign['summary'] ?? 'Siswa dapat mengisi cita-cita dan minat pada formulir asesmen mandiri agar penyelarasan lebih akurat.',
        'tailored_paths'      => collect($analysis['school_type'] === 'SMK' ? ($rec['career_tracks'] ?? []) : ($rec['college_majors'] ?? []))->map(fn($item) => [
            'title'  => $item['title'] ?? ($item['major'] ?? '-'),
            'reason' => $item['description'] ?? ('Rumpun ' . ($item['cluster'] ?? '') . ' — Tingkat Kesesuaian: ' . ($item['readiness'] ?? 'Sesuai')),
        ])->values()->toArray(),
        'action_plan_90_days' => $rec['action_plan_90_days'] ?? [],
        'parent_guidance'     => $rec['parent_guidance'] ?? '',
        'teacher_guidance'    => $rec['teacher_guidance'] ?? '',
    ];
@endphp

<div class="space-y-6"
     x-data="studentDnaAiPanel({
        endpoint: @js($aiEndpoint),
        initialReport: @js($aiReport),
        fallbackReport: @js($fallbackPayload),
        isStale: @js((bool) ($aiMeta['is_stale'] ?? true)),
        configured: @js((bool) ($aiMeta['provider_configured'] ?? false)),
        generatedAt: @js($aiMeta['generated_at'] ?? null),
        modelName: @js($aiMeta['model'] ?? 'ClassAI'),
        providerLabel: @js($aiMeta['provider_label'] ?? 'ClassAI Router')
     })"
     x-init="initPanel()">

    {{-- 1. Peta Mata Pelajaran Unggulan & Bidang Prioritas Penguatan --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 pb-3">
            <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-book-open text-sky-600"></i> Peta Capaian Mata Pelajaran Nyata
            </h3>
            <div class="flex flex-wrap items-center gap-2 text-[11px]">
                @if(!empty($analysis['vocational_major']))
                <span class="px-2.5 py-0.5 rounded-full bg-amber-50 border border-amber-200 text-amber-900 font-bold">
                    Jurusan: {{ $analysis['vocational_major']['label'] }} ({{ $analysis['vocational_major']['code'] }})
                </span>
                @endif
                @if(($sp['student_avg'] ?? null) !== null)
                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-800 font-bold">
                    Rerata Siswa: {{ $sp['student_avg'] }} ({{ $sp['subject_count'] }} Mapel)
                </span>
                @endif
            </div>
        </div>

        @if(($sp['subject_count'] ?? 0) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
            <div class="p-4 rounded-xl bg-emerald-50/60 border border-emerald-200 space-y-2">
                <p class="font-extrabold text-emerald-900 uppercase tracking-wider text-[11px]">Mata Pelajaran Unggulan</p>
                <div class="space-y-1.5">
                    @foreach($topSubjects as $s)
                    <div class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-emerald-100">
                        <span class="font-bold text-slate-800">{{ $s['subject_name'] }}</span>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($s['delta'] !== null)
                            <span class="text-[10px] font-bold {{ $s['delta'] >= 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $s['delta'] >= 0 ? '+' : '' }}{{ $s['delta'] }} vs Sekolah
                            </span>
                            @endif
                            <span class="px-2 py-0.5 rounded bg-emerald-600 text-white font-extrabold">{{ $s['score'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="p-4 rounded-xl bg-amber-50/60 border border-amber-200 space-y-2">
                <p class="font-extrabold text-amber-900 uppercase tracking-wider text-[11px]">Bidang Prioritas Penguatan</p>
                @if(!empty($weakSubjects))
                <div class="space-y-1.5">
                    @foreach($weakSubjects as $s)
                    <div class="flex items-center justify-between bg-white px-3 py-2 rounded-lg border border-amber-100">
                        <span class="font-bold text-slate-800">{{ $s['subject_name'] }}</span>
                        <div class="flex items-center gap-2 shrink-0">
                            @if($s['delta'] !== null)
                            <span class="text-[10px] font-bold text-slate-500">
                                {{ $s['delta'] >= 0 ? '+' : '' }}{{ $s['delta'] }} vs Sekolah
                            </span>
                            @endif
                            <span class="px-2 py-0.5 rounded bg-amber-600 text-white font-extrabold">{{ $s['score'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-slate-600">Capaian nilai antar-mata pelajaran masih relatif merata; diperlukan lebih banyak data nilai mapel untuk pemetaan pembanding.</p>
                @endif
            </div>
        </div>
        @else
        <p class="text-xs text-slate-500">Belum ada nilai mata pelajaran yang tercatat pada database untuk siswa ini.</p>
        @endif

        {{-- Rekam Jejak Bukti 360° Nyata --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs pt-1">
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Tugas LMS</p>
                <p class="font-extrabold text-slate-900 mt-0.5">{{ $ev['lms_count'] ?? 0 }} Tugas</p>
                <p class="text-[10px] text-slate-500">Tepat waktu: {{ ($ev['lms_ontime_rate'] ?? null) !== null ? $ev['lms_ontime_rate'] . '%' : '-' }}</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Ujian CBT</p>
                <p class="font-extrabold text-slate-900 mt-0.5">{{ ($ev['cbt_avg'] ?? null) !== null ? $ev['cbt_avg'] : '-' }}</p>
                <p class="text-[10px] text-slate-500">Dari {{ $ev['cbt_count'] ?? 0 }} sesi ujian</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-[10px] font-bold text-slate-500 uppercase">Prestasi Tercatat</p>
                <p class="font-extrabold text-slate-900 mt-0.5">{{ count($ev['achievements'] ?? []) }} Penghargaan</p>
                <p class="text-[10px] text-slate-500 truncate">{{ $ev['achievements'][0]['title'] ?? 'Belum ada catatan' }}</p>
            </div>
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200">
                <p class="text-[10px] font-bold text-slate-500 uppercase">{{ $analysis['school_type'] === 'SMK' ? 'PKL / Tugas Akhir' : 'Penelitian / Ekskul' }}</p>
                @if(!empty($ev['pkl']))
                <p class="font-extrabold text-slate-900 mt-0.5 truncate">{{ $ev['pkl']['company'] }}</p>
                <p class="text-[10px] text-slate-500">{{ $ev['pkl']['approved_logs'] }} logbook disetujui</p>
                @elseif(!empty($ev['final_project']))
                <p class="font-extrabold text-slate-900 mt-0.5 truncate">{{ $ev['final_project']['title'] }}</p>
                <p class="text-[10px] text-slate-500">{{ $ev['final_project']['stage'] }}</p>
                @else
                <p class="font-extrabold text-slate-900 mt-0.5">{{ ($ev['extracurriculars'] ?? collect())->count() }} Ekskul</p>
                <p class="text-[10px] text-slate-500 truncate">{{ ($ev['extracurriculars'] ?? collect())->first()?->extracurricular?->name ?? 'Belum terdaftar' }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- 2. Panel Analisis Mendalam Personal (ClassAI Engine) --}}
    <div class="bg-white rounded-2xl shadow-sm border border-indigo-200 p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 pb-3">
            <div>
                <h3 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <i class="fas fa-microchip text-indigo-600"></i> Analisis Mendalam Personal (<span x-text="providerLabel"></span>)
                </h3>
                <p class="text-[11px] text-slate-500 mt-0.5">
                    Disusun khusus dari gabungan nilai per-mapel, aktivitas LMS/CBT, proyek/PKL, prestasi, dan cita-cita siswa.
                    <template x-if="generatedAt">
                        <span class="font-semibold text-slate-700">&bull; Diperbarui: <span x-text="generatedAt"></span> (<span x-text="modelName"></span>)</span>
                    </template>
                </p>
            </div>
            <button type="button" @click="refreshAi(true)" :disabled="loading"
                    class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 disabled:opacity-60 text-white text-xs font-bold transition inline-flex items-center gap-2 shrink-0">
                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-rotate'"></i>
                <span x-text="loading ? 'Menyusun Analisis Personal...' : 'Perbarui Analisis ClassAI'"></span>
            </button>
        </div>

        <template x-if="errorMsg">
            <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-xs text-amber-900" x-text="errorMsg"></div>
        </template>

        <div class="space-y-4 text-xs">
            {{-- Ringkasan Profil Belajar Personal --}}
            <div class="p-4 rounded-xl bg-indigo-50/60 border border-indigo-100 space-y-1.5">
                <p class="font-extrabold text-indigo-950 uppercase tracking-wider text-[11px]">Ringkasan Profil Belajar Personal</p>
                <p class="text-slate-800 leading-relaxed" x-text="activeReport.executive_summary"></p>
            </div>

            {{-- Keunggulan Nyata & Bidang Prioritas Penguatan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <p class="font-extrabold text-slate-900 text-[11px] uppercase tracking-wider">Keunggulan Nyata Siswa</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 leading-relaxed">
                        <template x-for="(item, i) in (activeReport.unique_strengths || [])" :key="i">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>

                <div class="p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
                    <p class="font-extrabold text-slate-900 text-[11px] uppercase tracking-wider">Bidang Prioritas Penguatan</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-700 leading-relaxed">
                        <template x-for="(item, i) in (activeReport.growth_priorities || [])" :key="i">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Keselarasan Cita-Cita --}}
            <div class="p-4 rounded-xl bg-purple-50/60 border border-purple-200 space-y-1.5">
                <p class="font-extrabold text-purple-950 uppercase tracking-wider text-[11px]">Keselarasan Cita-Cita dengan Capaian Saat Ini</p>
                <p class="text-slate-800 leading-relaxed" x-text="activeReport.dream_alignment"></p>
            </div>

            {{-- Rencana Pendampingan 3 Bulan (90 Hari) --}}
            <div class="p-4 rounded-xl bg-emerald-50/50 border border-emerald-200 space-y-2">
                <p class="font-extrabold text-emerald-950 uppercase tracking-wider text-[11px]">Rencana Pendampingan 3 Bulan (90 Hari)</p>
                <ol class="list-decimal list-inside space-y-1 text-slate-800 leading-relaxed">
                    <template x-for="(step, i) in (activeReport.action_plan_90_days || [])" :key="i">
                        <li x-text="step"></li>
                    </template>
                </ol>
            </div>

            {{-- Saran Sesuai Peran Pembaca --}}
            @if(in_array($viewerRole ?? 'admin', ['admin', 'guru']))
            <div class="p-4 rounded-xl bg-sky-50 border border-sky-200 space-y-1">
                <p class="font-extrabold text-sky-950 text-[11px] uppercase tracking-wider">Catatan Pedagogis untuk Wali Kelas &amp; Guru</p>
                <p class="text-slate-800 leading-relaxed" x-text="activeReport.teacher_guidance"></p>
            </div>
            @endif
            @if(in_array($viewerRole ?? 'admin', ['orangtua', 'admin']))
            <div class="p-4 rounded-xl bg-blue-50 border border-blue-200 space-y-1">
                <p class="font-extrabold text-blue-950 text-[11px] uppercase tracking-wider">Panduan Pendampingan Orang Tua di Rumah</p>
                <p class="text-slate-800 leading-relaxed" x-text="activeReport.parent_guidance"></p>
            </div>
            @endif

            {{-- Catatan Anti-Salah Tafsir --}}
            <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-[11px] text-slate-600 leading-relaxed">
                <strong>Catatan Pemahaman Laporan:</strong> Profil DNA Akademik 360° merupakan alat bantu pemetaan potensi dan arah pendampingan belajar berdasarkan data saat ini. Hasil ini bersifat dinamis dan berkembang seiring proses belajar siswa, bukan penilaian tetap atau penentu tunggal masa depan.
            </div>
        </div>
    </div>
</div>

<script>
function studentDnaAiPanel(cfg) {
    return {
        endpoint: cfg.endpoint,
        report: cfg.initialReport,
        fallbackReport: cfg.fallbackReport,
        isStale: cfg.isStale,
        configured: cfg.configured,
        generatedAt: cfg.generatedAt,
        modelName: cfg.modelName,
        providerLabel: cfg.providerLabel,
        loading: false,
        errorMsg: '',
        get activeReport() {
            return this.report || this.fallbackReport;
        },
        initPanel() {
            if (this.configured && (!this.report || this.isStale)) {
                this.refreshAi(false);
            }
        },
        async refreshAi(force) {
            if (this.loading) return;
            this.loading = true;
            this.errorMsg = '';
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';
                const res = await fetch(this.endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token
                    },
                    body: JSON.stringify({ force: !!force })
                });
                const data = await res.json();
                if (res.ok && data.success && data.ai_report) {
                    this.report = data.ai_report;
                    this.isStale = false;
                    if (data.ai_meta) {
                        this.generatedAt = data.ai_meta.generated_at || this.generatedAt;
                        this.modelName = data.ai_meta.model || this.modelName;
                        this.providerLabel = data.ai_meta.provider_label || this.providerLabel;
                    }
                } else {
                    this.errorMsg = data.message || 'Menampilkan ringkasan terstruktur berbasis data sekolah karena layanan ClassAI belum merespons.';
                }
            } catch (e) {
                this.errorMsg = 'Koneksi ke layanan ClassAI terganggu; menampilkan ringkasan terstruktur berbasis data sekolah.';
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>

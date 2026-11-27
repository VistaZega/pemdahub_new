@php
    $sp = $analysis['subject_profile'] ?? [];
    $rec = $analysis['recommendations'] ?? [];
    $aiReport = $analysis['ai_report'] ?? null;
    $aiMeta = $analysis['ai_meta'] ?? [];
    $topSubjects = $sp['top_subjects'] ?? [];
    $weakSubjects = $sp['weak_subjects'] ?? [];
    $dreamAlign = $rec['dream_alignment'] ?? null;
    $fallbackPayload = [
        'executive_summary'   => $analysis['archetype']['description'] ?? '',
        'unique_strengths'    => collect($topSubjects)->map(fn($s) => $s['subject_name'] . ' (Nilai ' . $s['score'] . ')' . ($s['delta'] !== null ? ' — selisih ' . ($s['delta'] >= 0 ? '+' : '') . $s['delta'] . ' vs rerata sekolah' : ''))->values()->toArray(),
        'growth_priorities'   => collect($weakSubjects)->map(fn($s) => $s['subject_name'] . ' (Nilai ' . $s['score'] . ') — disarankan penguatan materi inti secara bertahap')->values()->toArray(),
        'dream_alignment'     => $dreamAlign['summary'] ?? 'Siswa dapat melengkapi isian minat dan cita-cita agar penyelarasan arah studi dan karier lebih spesifik.',
        'tailored_paths'      => collect($analysis['school_type'] === 'SMK' ? ($rec['career_tracks'] ?? []) : ($rec['college_majors'] ?? []))->map(fn($item) => [
            'title'  => $item['title'] ?? ($item['major'] ?? '-'),
            'reason' => $item['description'] ?? ('Rumpun ' . ($item['cluster'] ?? '') . ' — Tingkat Kesesuaian: ' . ($item['readiness'] ?? 'Sesuai')),
        ])->values()->toArray(),
        'action_plan_90_days' => $rec['action_plan_90_days'] ?? [],
        'parent_guidance'     => $rec['parent_guidance'] ?? '',
        'teacher_guidance'    => $rec['teacher_guidance'] ?? '',
    ];
@endphp

<div class="space-y-5"
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

    {{-- Section A: Peta Capaian Mata Pelajaran Nyata --}}
    <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-2">
            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                <i class="fas fa-book-open text-sky-700 text-xs"></i>
                <span>Peta Capaian Mata Pelajaran Nyata</span>
            </h3>
            <div class="flex flex-wrap items-center gap-2 text-xs">
                @if(!empty($analysis['vocational_major']))
                <span class="px-2.5 py-0.5 rounded bg-amber-50 border border-amber-300 text-amber-900 font-bold">
                    Jurusan {{ $analysis['vocational_major']['code'] }} · {{ $analysis['vocational_major']['label'] }}
                </span>
                @endif
                @if(($sp['student_avg'] ?? null) !== null)
                <span class="px-2.5 py-0.5 rounded bg-slate-100 border border-slate-300 text-slate-800 font-semibold tabular-nums">
                    Rerata Siswa: {{ $sp['student_avg'] }} ({{ $sp['subject_count'] }} Mapel)
                </span>
                @endif
            </div>
        </div>

        @if(($sp['subject_count'] ?? 0) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200 text-xs">
            {{-- Kolom Kiri: Mata Pelajaran Unggulan --}}
            <div class="p-5 space-y-2.5">
                <p class="font-bold text-emerald-900">Mata Pelajaran Unggulan</p>
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-lg bg-white">
                    @foreach($topSubjects as $s)
                    <div class="px-3.5 py-2.5 flex items-center justify-between gap-3">
                        <span class="font-semibold text-slate-800 leading-snug">{{ $s['subject_name'] }}</span>
                        <div class="flex items-center gap-2 shrink-0 tabular-nums">
                            @if($s['delta'] !== null)
                            <span class="text-[11px] font-semibold {{ $s['delta'] >= 0 ? 'text-emerald-700' : 'text-slate-500' }}">
                                {{ $s['delta'] >= 0 ? '+' : '' }}{{ $s['delta'] }} vs Sekolah
                            </span>
                            @endif
                            <span class="px-2 py-0.5 rounded bg-emerald-50 border border-emerald-300 text-emerald-900 font-bold">{{ $s['score'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Kolom Kanan: Bidang Prioritas Penguatan --}}
            <div class="p-5 space-y-2.5">
                <p class="font-bold text-amber-900">Bidang Prioritas Penguatan</p>
                @if(!empty($weakSubjects))
                <div class="divide-y divide-slate-100 border border-slate-200 rounded-lg bg-white">
                    @foreach($weakSubjects as $s)
                    <div class="px-3.5 py-2.5 flex items-center justify-between gap-3">
                        <span class="font-semibold text-slate-800 leading-snug">{{ $s['subject_name'] }}</span>
                        <div class="flex items-center gap-2 shrink-0 tabular-nums">
                            @if($s['delta'] !== null)
                            <span class="text-[11px] font-semibold text-slate-500">
                                {{ $s['delta'] >= 0 ? '+' : '' }}{{ $s['delta'] }} vs Sekolah
                            </span>
                            @endif
                            <span class="px-2 py-0.5 rounded bg-amber-50 border border-amber-300 text-amber-900 font-bold">{{ $s['score'] }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-slate-600 leading-relaxed">Capaian nilai antar-mata pelajaran relatif merata; diperlukan lebih banyak data nilai mapel untuk pemetaan pembanding.</p>
                @endif
            </div>
        </div>
        @else
        <div class="p-5 text-xs text-slate-600">
            Belum ada nilai mata pelajaran yang tercatat pada database untuk siswa ini. Setelah guru menginput nilai atau asesmen LMS/CBT, peta capaian per mata pelajaran akan tampil otomatis di sini.
        </div>
        @endif
    </div>

    {{-- Section B: Analisis Mendalam Personal (ClassAI Router) --}}
    <div class="bg-white border border-slate-300 rounded-xl overflow-hidden">
        <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fas fa-microchip text-sky-700 text-xs"></i>
                    <span>Analisis Mendalam Personal (<span x-text="providerLabel"></span>)</span>
                </h3>
                <p class="text-[11px] text-slate-600 mt-0.5">
                    Disintesis khusus dari nilai per mata pelajaran, aktivitas LMS/CBT, proyek/PKL, prestasi, dan cita-cita siswa.
                    <template x-if="generatedAt">
                        <span class="font-semibold text-slate-800"> · Diperbarui <span x-text="generatedAt"></span> (<span x-text="modelName"></span>)</span>
                    </template>
                </p>
            </div>
            <button type="button" @click="refreshAi(true)" :disabled="loading"
                    class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 disabled:opacity-60 text-white text-xs font-semibold transition inline-flex items-center gap-2 shrink-0 focus:outline-none focus:ring-2 focus:ring-sky-500">
                <i class="fas" :class="loading ? 'fa-spinner fa-spin' : 'fa-rotate'"></i>
                <span x-text="loading ? 'Menyusun Analisis...' : 'Perbarui Analisis ClassAI'"></span>
            </button>
        </div>

        <template x-if="errorMsg">
            <div class="px-5 py-2.5 bg-amber-50 border-b border-amber-200 text-xs text-amber-900" x-text="errorMsg"></div>
        </template>

        {{-- Skeleton State saat ClassAI menyusun analisis --}}
        <div x-show="loading" class="p-5 space-y-4 animate-pulse">
            <div class="space-y-2">
                <div class="h-3.5 bg-slate-200 rounded w-1/3"></div>
                <div class="h-3 bg-slate-200 rounded w-full"></div>
                <div class="h-3 bg-slate-200 rounded w-5/6"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                <div class="h-24 bg-slate-100 border border-slate-200 rounded-lg"></div>
                <div class="h-24 bg-slate-100 border border-slate-200 rounded-lg"></div>
            </div>
            <div class="h-16 bg-slate-100 border border-slate-200 rounded-lg"></div>
        </div>

        {{-- Konten Analisis Personal --}}
        <div x-show="!loading" class="divide-y divide-slate-200 text-xs">
            {{-- Ringkasan Profil Belajar Personal --}}
            <div class="p-5 space-y-1.5">
                <p class="font-bold text-slate-900">Ringkasan Profil Belajar Personal</p>
                <p class="text-slate-700 leading-relaxed max-w-prose text-[13px]" x-text="activeReport.executive_summary"></p>
            </div>

            {{-- Keunggulan Nyata & Bidang Prioritas Penguatan --}}
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-slate-200">
                <div class="p-5 space-y-2">
                    <p class="font-bold text-slate-900">Keunggulan Nyata Siswa</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-700 leading-relaxed">
                        <template x-for="(item, i) in (activeReport.unique_strengths || [])" :key="i">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>

                <div class="p-5 space-y-2">
                    <p class="font-bold text-slate-900">Bidang Prioritas Penguatan</p>
                    <ul class="list-disc list-inside space-y-1.5 text-slate-700 leading-relaxed">
                        <template x-for="(item, i) in (activeReport.growth_priorities || [])" :key="i">
                            <li x-text="item"></li>
                        </template>
                    </ul>
                </div>
            </div>

            {{-- Keselarasan Cita-Cita --}}
            <div class="p-5 bg-slate-50/70 space-y-1.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="font-bold text-slate-900">Keselarasan Cita-Cita dengan Capaian Saat Ini</p>
                    @if(!empty($dreamAlign['status_label']))
                    <span class="px-2.5 py-0.5 rounded bg-sky-50 border border-sky-200 text-sky-900 font-semibold text-[11px]">
                        {{ $dreamAlign['status_label'] }}
                    </span>
                    @endif
                </div>
                <p class="text-slate-700 leading-relaxed max-w-prose" x-text="activeReport.dream_alignment"></p>
            </div>

            {{-- Rencana Pendampingan 3 Bulan (90 Hari) --}}
            <div class="p-5 space-y-3">
                <p class="font-bold text-slate-900">Rencana Pendampingan 3 Bulan (90 Hari)</p>
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-slate-200 border border-slate-200 rounded-lg bg-slate-50/50">
                    <template x-for="(step, i) in (activeReport.action_plan_90_days || [])" :key="i">
                        <div class="p-3.5 space-y-1">
                            <div class="font-bold text-sky-800 text-[11px]" x-text="'Tahap Bulan ke-' + (i + 1)"></div>
                            <p class="text-slate-700 leading-relaxed" x-text="step"></p>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Saran Sesuai Peran Pembaca --}}
            @if(in_array($viewerRole ?? 'admin', ['admin', 'guru', 'orangtua']))
            <div class="grid grid-cols-1 {{ ($viewerRole ?? 'admin') === 'admin' ? 'md:grid-cols-2 md:divide-x' : '' }} divide-y md:divide-y-0 divide-slate-200">
                @if(in_array($viewerRole ?? 'admin', ['admin', 'guru']))
                <div class="p-5 space-y-1.5">
                    <p class="font-bold text-slate-900">Catatan Pedagogis untuk Wali Kelas &amp; Guru</p>
                    <p class="text-slate-700 leading-relaxed" x-text="activeReport.teacher_guidance"></p>
                </div>
                @endif
                @if(in_array($viewerRole ?? 'admin', ['orangtua', 'admin']))
                <div class="p-5 space-y-1.5">
                    <p class="font-bold text-slate-900">Panduan Pendampingan Orang Tua di Rumah</p>
                    <p class="text-slate-700 leading-relaxed" x-text="activeReport.parent_guidance"></p>
                </div>
                @endif
            </div>
            @endif

            {{-- Catatan Anti-Salah Tafsir --}}
            <div class="px-5 py-3 bg-slate-50 text-[11px] text-slate-600 leading-relaxed">
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

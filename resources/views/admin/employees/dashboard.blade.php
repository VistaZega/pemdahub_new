@extends('layouts.admin')

@section('title', 'Dashboard SDM - Kepegawaian')

@push('styles')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap');

    .sdm-page {
        font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
        background: linear-gradient(135deg, #f0f4ff 0%, #fdf4ff 50%, #f0fff4 100%);
        min-height: 100vh;
    }

    /* ─── CLAY CARDS ─── */
    .clay-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 6px 6px 0px 0px rgba(99,102,241,0.12), 0 4px 24px rgba(0,0,0,0.06);
        border: 2px solid rgba(255,255,255,0.9);
        transition: transform 0.18s ease, box-shadow 0.18s ease;
        overflow: hidden;
        position: relative;
    }
    .clay-card:hover {
        transform: translateY(-4px);
        box-shadow: 8px 10px 0px 0px rgba(99,102,241,0.14), 0 8px 32px rgba(0,0,0,0.09);
    }
    .clay-card-blue {
        box-shadow: 6px 6px 0px 0px rgba(59,130,246,0.18), 0 4px 24px rgba(0,0,0,0.06);
    }
    .clay-card-blue:hover { box-shadow: 8px 10px 0px 0px rgba(59,130,246,0.22), 0 8px 32px rgba(0,0,0,0.09); }
    .clay-card-emerald {
        box-shadow: 6px 6px 0px 0px rgba(16,185,129,0.18), 0 4px 24px rgba(0,0,0,0.06);
    }
    .clay-card-emerald:hover { box-shadow: 8px 10px 0px 0px rgba(16,185,129,0.22), 0 8px 32px rgba(0,0,0,0.09); }
    .clay-card-violet {
        box-shadow: 6px 6px 0px 0px rgba(139,92,246,0.18), 0 4px 24px rgba(0,0,0,0.06);
    }
    .clay-card-violet:hover { box-shadow: 8px 10px 0px 0px rgba(139,92,246,0.22), 0 8px 32px rgba(0,0,0,0.09); }
    .clay-card-amber {
        box-shadow: 6px 6px 0px 0px rgba(245,158,11,0.18), 0 4px 24px rgba(0,0,0,0.06);
    }
    .clay-card-amber:hover { box-shadow: 8px 10px 0px 0px rgba(245,158,11,0.22), 0 8px 32px rgba(0,0,0,0.09); }
    .clay-card-rose {
        box-shadow: 6px 6px 0px 0px rgba(244,63,94,0.18), 0 4px 24px rgba(0,0,0,0.06);
    }
    .clay-card-rose:hover { box-shadow: 8px 10px 0px 0px rgba(244,63,94,0.22), 0 8px 32px rgba(0,0,0,0.09); }

    /* ─── STAT CARDS ─── */
    .stat-number {
        font-size: 2.8rem;
        font-weight: 900;
        line-height: 1;
        letter-spacing: -0.04em;
    }
    .stat-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        flex-shrink: 0;
    }

    /* ─── HERO BANNER ─── */
    .sdm-hero {
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 50%, #a855f7 100%);
        border-radius: 28px;
        padding: 36px 40px;
        position: relative;
        overflow: hidden;
        box-shadow: 8px 8px 0 rgba(79,70,229,0.25), 0 8px 40px rgba(79,70,229,0.3);
    }
    .sdm-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 220px; height: 220px;
        background: rgba(255,255,255,0.07);
        border-radius: 50%;
    }
    .sdm-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; left: -20px;
        width: 160px; height: 160px;
        background: rgba(255,255,255,0.05);
        border-radius: 50%;
    }

    /* ─── PROGRESS BAR ─── */
    .sdm-progress-track {
        height: 12px;
        background: #f1f5f9;
        border-radius: 100px;
        overflow: hidden;
    }
    .sdm-progress-fill {
        height: 100%;
        border-radius: 100px;
        transition: width 1s ease;
    }

    /* ─── QUICK NAV ─── */
    .quick-nav-btn {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 20px 12px;
        border-radius: 20px;
        border: 2px solid transparent;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.18s ease;
        background: #f8fafc;
    }
    .quick-nav-btn:hover {
        transform: translateY(-3px);
        background: white;
        border-color: currentColor;
        box-shadow: 0 8px 20px rgba(0,0,0,0.08);
    }
    .quick-nav-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    .quick-nav-label {
        font-size: 11px;
        font-weight: 700;
        text-align: center;
        line-height: 1.3;
    }

    /* ─── PERSON LIST ITEM ─── */
    .person-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 14px;
        border-radius: 16px;
        background: #f8fafc;
        transition: all 0.15s ease;
    }
    .person-item:hover {
        background: #f1f5f9;
        transform: translateX(3px);
    }
    .person-avatar {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }

    /* ─── SCHOOL ROW ─── */
    .school-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 18px;
        background: #f8fafc;
        border: 2px solid transparent;
        transition: all 0.15s ease;
    }
    .school-row:hover {
        background: #fff;
        border-color: #e0e7ff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .school-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    /* ─── SECTION TITLE ─── */
    .section-title {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.12em;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
    }
    .section-title::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 16px;
        border-radius: 4px;
        background: currentColor;
    }

    /* ─── EDU PILL ─── */
    .edu-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border-radius: 100px;
        font-size: 12px;
        font-weight: 800;
        transition: all 0.15s ease;
    }
    .edu-pill:hover { transform: scale(1.05); }
</style>
@endpush

@section('content')
<div class="sdm-page -mt-4 -mx-4 md:-mx-6 p-4 md:p-8">

    {{-- ═══════════════════════════════════════════ --}}
    {{-- HERO BANNER                                 --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="sdm-hero mb-8">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 text-white text-xs font-bold mb-4 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Dashboard Sumber Daya Manusia (SDM)
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white mb-2 leading-tight">
                    👥 Kepegawaian<br>Perguruan PEMBDA Nias
                </h1>
                <p class="text-indigo-200 text-sm font-medium max-w-md">
                    Ringkasan data pegawai, distribusi SDM per unit, monitoring cuti aktif, dan kontrak yang segera berakhir.
                </p>
            </div>

            <div class="flex items-center gap-4 shrink-0 flex-wrap">
                <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-5 py-3.5 text-center border border-white/20">
                    <div class="text-3xl font-black text-white">{{ $stats['total'] }}</div>
                    <div class="text-xs font-bold text-indigo-200 mt-1">Total Pegawai</div>
                </div>
                <div class="flex flex-col gap-2">
                    <a href="{{ route('admin.employees.index') }}" class="inline-flex items-center gap-2 bg-white text-indigo-700 rounded-xl px-4 py-2 text-xs font-bold shadow-lg hover:shadow-xl transition hover:-translate-y-0.5">
                        <i class="fas fa-users"></i> Kelola Pegawai
                    </a>
                    <a href="{{ route('admin.employees.leaves.index') }}" class="inline-flex items-center gap-2 bg-white/20 text-white border border-white/30 rounded-xl px-4 py-2 text-xs font-bold hover:bg-white/30 transition">
                        <i class="fas fa-calendar-days"></i> Kelola Cuti
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- 4 STAT CARDS                                --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5 mb-8">
        {{-- Total Pegawai --}}
        <div class="clay-card clay-card-blue p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="stat-icon-wrap bg-blue-100">
                    <i class="fas fa-users text-blue-600"></i>
                </div>
                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded-full uppercase tracking-wide">Aktif</span>
            </div>
            <div class="stat-number text-blue-700">{{ $stats['total'] }}</div>
            <p class="text-xs font-semibold text-slate-500 mt-1.5">Total Seluruh Pegawai</p>
            <div class="mt-3 flex items-center gap-1.5 text-[10px] text-blue-500 font-bold">
                <i class="fas fa-arrow-trend-up"></i> Semua Unit & Sekolah
            </div>
        </div>

        {{-- Guru / Pengajar --}}
        <div class="clay-card clay-card-emerald p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="stat-icon-wrap bg-emerald-100">
                    <i class="fas fa-chalkboard-teacher text-emerald-600"></i>
                </div>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full uppercase tracking-wide">Guru</span>
            </div>
            <div class="stat-number text-emerald-700">{{ $stats['guru'] }}</div>
            <p class="text-xs font-semibold text-slate-500 mt-1.5">Guru / Tenaga Pengajar</p>
            @php $pctGuru = $stats['total'] > 0 ? round($stats['guru'] / $stats['total'] * 100) : 0; @endphp
            <div class="mt-3">
                <div class="sdm-progress-track">
                    <div class="sdm-progress-fill bg-gradient-to-r from-emerald-400 to-teal-500" style="width:{{ $pctGuru }}%"></div>
                </div>
                <div class="text-[10px] text-emerald-600 font-bold mt-1">{{ $pctGuru }}% dari total</div>
            </div>
        </div>

        {{-- TU / Staff --}}
        <div class="clay-card clay-card-amber p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="stat-icon-wrap bg-amber-100">
                    <i class="fas fa-briefcase text-amber-600"></i>
                </div>
                <span class="text-[10px] font-bold text-amber-600 bg-amber-50 px-2 py-1 rounded-full uppercase tracking-wide">Staff</span>
            </div>
            <div class="stat-number text-amber-700">{{ $stats['staff'] }}</div>
            <p class="text-xs font-semibold text-slate-500 mt-1.5">TU / Tenaga Kependidikan</p>
            @php $pctStaff = $stats['total'] > 0 ? round($stats['staff'] / $stats['total'] * 100) : 0; @endphp
            <div class="mt-3">
                <div class="sdm-progress-track">
                    <div class="sdm-progress-fill bg-gradient-to-r from-amber-400 to-orange-500" style="width:{{ $pctStaff }}%"></div>
                </div>
                <div class="text-[10px] text-amber-600 font-bold mt-1">{{ $pctStaff }}% dari total</div>
            </div>
        </div>

        {{-- Staf Yayasan --}}
        <div class="clay-card clay-card-violet p-5">
            <div class="flex items-start justify-between mb-3">
                <div class="stat-icon-wrap bg-violet-100">
                    <i class="fas fa-building text-violet-600"></i>
                </div>
                <span class="text-[10px] font-bold text-violet-600 bg-violet-50 px-2 py-1 rounded-full uppercase tracking-wide">Yayasan</span>
            </div>
            <div class="stat-number text-violet-700">{{ $stats['yayasan'] }}</div>
            <p class="text-xs font-semibold text-slate-500 mt-1.5">Staf Sekretariat Yayasan</p>
            @php $pctYayasan = $stats['total'] > 0 ? round($stats['yayasan'] / $stats['total'] * 100) : 0; @endphp
            <div class="mt-3">
                <div class="sdm-progress-track">
                    <div class="sdm-progress-fill bg-gradient-to-r from-violet-400 to-purple-500" style="width:{{ $pctYayasan }}%"></div>
                </div>
                <div class="text-[10px] text-violet-600 font-bold mt-1">{{ $pctYayasan }}% dari total</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- QUICK NAVIGATION                            --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="clay-card p-5 mb-8">
        <div class="section-title text-indigo-400"><span>Menu Cepat</span></div>
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <a href="{{ route('admin.employees.index') }}" class="quick-nav-btn text-indigo-700">
                <div class="quick-nav-icon bg-indigo-100">
                    <i class="fas fa-users text-indigo-600"></i>
                </div>
                <span class="quick-nav-label text-indigo-700">Daftar Pegawai</span>
            </a>
            <a href="{{ route('admin.employees.create') }}" class="quick-nav-btn text-emerald-700">
                <div class="quick-nav-icon bg-emerald-100">
                    <i class="fas fa-user-plus text-emerald-600"></i>
                </div>
                <span class="quick-nav-label text-emerald-700">Tambah Pegawai</span>
            </a>
            <a href="{{ route('admin.employees.attendance.index') }}" class="quick-nav-btn text-blue-700">
                <div class="quick-nav-icon bg-blue-100">
                    <i class="fas fa-fingerprint text-blue-600"></i>
                </div>
                <span class="quick-nav-label text-blue-700">Presensi Pegawai</span>
            </a>
            <a href="{{ route('admin.employees.leaves.index') }}" class="quick-nav-btn text-amber-700">
                <div class="quick-nav-icon bg-amber-100">
                    <i class="fas fa-calendar-check text-amber-600"></i>
                </div>
                <span class="quick-nav-label text-amber-700">Data Cuti</span>
            </a>
            <a href="{{ route('admin.employees.leaves.rekap') }}" class="quick-nav-btn text-rose-700">
                <div class="quick-nav-icon bg-rose-100">
                    <i class="fas fa-chart-bar text-rose-600"></i>
                </div>
                <span class="quick-nav-label text-rose-700">Rekap Cuti</span>
            </a>
            <a href="{{ route('admin.employees.attendance.rekap') }}" class="quick-nav-btn text-violet-700">
                <div class="quick-nav-icon bg-violet-100">
                    <i class="fas fa-table-list text-violet-600"></i>
                </div>
                <span class="quick-nav-label text-violet-700">Rekap Presensi</span>
            </a>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- MAIN CONTENT GRID                           --}}
    {{-- ═══════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- ─── LEFT COL (2/3) ─── --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Pegawai Per Unit --}}
            <div class="clay-card p-6">
                <div class="section-title text-blue-400"><span>Pegawai Per Unit / Sekolah</span></div>
                <div class="space-y-3">
                    @php $maxCount = $schools->max('employee_count') ?: 1; @endphp
                    @foreach($schools as $school)
                    @php
                        $isYayasan = $school->isYayasan();
                        $pct = round($school->employee_count / $maxCount * 100);
                        $colorClass = $isYayasan ? 'violet' : 'indigo';
                    @endphp
                    <div class="school-row">
                        <div class="school-icon {{ $isYayasan ? 'bg-violet-100' : 'bg-indigo-100' }}">
                            <i class="fas {{ $isYayasan ? 'fa-building text-violet-600' : 'fa-school text-indigo-600' }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5">
                                <span class="font-bold text-slate-800 text-sm truncate">{{ $school->name }}</span>
                                @if($isYayasan)
                                    <span class="shrink-0 px-2 py-0.5 bg-violet-100 text-violet-700 text-[10px] font-black rounded-full uppercase tracking-wide">Yayasan</span>
                                @endif
                            </div>
                            <div class="sdm-progress-track">
                                <div class="sdm-progress-fill {{ $isYayasan ? 'bg-gradient-to-r from-violet-400 to-purple-500' : 'bg-gradient-to-r from-indigo-400 to-blue-500' }}" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-2xl font-black {{ $isYayasan ? 'text-violet-700' : 'text-indigo-700' }}">{{ $school->employee_count }}</span>
                            <div class="text-[10px] text-slate-400 font-semibold">orang</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Gender & Status side by side --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                {{-- Gender --}}
                <div class="clay-card p-6">
                    <div class="section-title text-pink-400"><span>Distribusi Gender</span></div>
                    @php
                        $totalGender = ($genderDist['L'] ?? 0) + ($genderDist['P'] ?? 0);
                        $pctL = $totalGender > 0 ? round(($genderDist['L'] ?? 0) / $totalGender * 100) : 0;
                        $pctP = $totalGender > 0 ? round(($genderDist['P'] ?? 0) / $totalGender * 100) : 0;
                    @endphp
                    <div class="space-y-5">
                        {{-- Laki-laki --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-blue-100 flex items-center justify-center">
                                        <i class="fas fa-mars text-blue-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">Laki-laki</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-blue-700">{{ $genderDist['L'] ?? 0 }}</span>
                                    <span class="text-xs text-slate-400 ml-1">orang</span>
                                </div>
                            </div>
                            <div class="sdm-progress-track">
                                <div class="sdm-progress-fill bg-gradient-to-r from-blue-400 to-indigo-500" style="width:{{ $pctL }}%"></div>
                            </div>
                            <div class="text-[10px] text-blue-500 font-bold mt-1">{{ $pctL }}%</div>
                        </div>
                        {{-- Perempuan --}}
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-xl bg-pink-100 flex items-center justify-center">
                                        <i class="fas fa-venus text-pink-600 text-sm"></i>
                                    </div>
                                    <span class="text-sm font-bold text-slate-700">Perempuan</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-2xl font-black text-pink-700">{{ $genderDist['P'] ?? 0 }}</span>
                                    <span class="text-xs text-slate-400 ml-1">orang</span>
                                </div>
                            </div>
                            <div class="sdm-progress-track">
                                <div class="sdm-progress-fill bg-gradient-to-r from-pink-400 to-rose-500" style="width:{{ $pctP }}%"></div>
                            </div>
                            <div class="text-[10px] text-pink-500 font-bold mt-1">{{ $pctP }}%</div>
                        </div>
                    </div>
                </div>

                {{-- Status Kepegawaian --}}
                <div class="clay-card p-6">
                    <div class="section-title text-indigo-400"><span>Status Kepegawaian</span></div>
                    @php
                        $statusLabels = [
                            'tetap_yayasan' => ['label' => 'Tetap Yayasan', 'icon' => 'fa-award', 'color' => 'violet'],
                            'honorer' => ['label' => 'Honorer', 'icon' => 'fa-file-contract', 'color' => 'amber'],
                            'kontrak' => ['label' => 'Kontrak', 'icon' => 'fa-handshake', 'color' => 'blue'],
                            'pns' => ['label' => 'PNS', 'icon' => 'fa-landmark', 'color' => 'emerald'],
                        ];
                        $totalStatus = $statusDist->sum();
                    @endphp
                    @if($statusDist->isEmpty())
                        <div class="text-center py-6 text-slate-400">
                            <i class="fas fa-database text-3xl mb-2 opacity-40"></i>
                            <p class="text-sm">Belum ada data</p>
                        </div>
                    @else
                    <div class="space-y-3">
                        @foreach($statusDist as $status => $count)
                        @php
                            $cfg = $statusLabels[$status] ?? ['label' => ucfirst($status), 'icon' => 'fa-user', 'color' => 'slate'];
                            $pctS = $totalStatus > 0 ? round($count / $totalStatus * 100) : 0;
                            $c = $cfg['color'];
                            $colorMap = [
                                'violet' => ['bg-violet-100', 'text-violet-600', 'bg-gradient-to-r from-violet-400 to-purple-500', 'text-violet-700'],
                                'amber' => ['bg-amber-100', 'text-amber-600', 'bg-gradient-to-r from-amber-400 to-orange-500', 'text-amber-700'],
                                'blue' => ['bg-blue-100', 'text-blue-600', 'bg-gradient-to-r from-blue-400 to-indigo-500', 'text-blue-700'],
                                'emerald' => ['bg-emerald-100', 'text-emerald-600', 'bg-gradient-to-r from-emerald-400 to-teal-500', 'text-emerald-700'],
                                'slate' => ['bg-slate-100', 'text-slate-600', 'bg-gradient-to-r from-slate-400 to-slate-500', 'text-slate-700'],
                            ];
                            [$iconBg, $iconColor, $barClass, $countColor] = $colorMap[$c] ?? $colorMap['slate'];
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
                                <i class="fas {{ $cfg['icon'] }} {{ $iconColor }} text-xs"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold text-slate-700">{{ $cfg['label'] }}</span>
                                    <span class="text-sm font-black {{ $countColor }}">{{ $count }}</span>
                                </div>
                                <div class="sdm-progress-track">
                                    <div class="sdm-progress-fill {{ $barClass }}" style="width:{{ $pctS }}%"></div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Distribusi Pendidikan --}}
            @if($educationDist->count())
            <div class="clay-card p-6">
                <div class="section-title text-violet-400"><span>Distribusi Pendidikan Terakhir</span></div>
                @php
                    $eduCfg = [
                        'S3' => ['violet', '🎓', 'Doktoral'],
                        'S2' => ['purple', '🎓', 'Magister'],
                        'S1' => ['indigo', '📚', 'Sarjana'],
                        'D4' => ['blue', '📖', 'Diploma IV'],
                        'D3' => ['sky', '📗', 'Diploma III'],
                        'D2' => ['cyan', '📘', 'Diploma II'],
                        'D1' => ['teal', '📙', 'Diploma I'],
                        'SMA' => ['emerald', '🏫', 'SMA/SMK/MA'],
                        'SMP' => ['green', '🏢', 'SMP'],
                        'SD'  => ['lime', '📝', 'SD'],
                    ];
                @endphp
                <div class="flex flex-wrap gap-3">
                    @foreach($educationDist as $level => $count)
                    @php
                        [$ec, $emoji, $tooltip] = $eduCfg[$level] ?? ['slate', '📄', $level];
                    @endphp
                    <div class="edu-pill bg-{{ $ec }}-100 border-2 border-{{ $ec }}-200" title="{{ $tooltip }}">
                        <span class="text-base leading-none">{{ $emoji }}</span>
                        <span class="text-{{ $ec }}-700">{{ $level }}</span>
                        <span class="w-6 h-6 rounded-full bg-{{ $ec }}-600 text-white text-[10px] flex items-center justify-center font-black">{{ $count }}</span>
                    </div>
                    @endforeach
                </div>
                <p class="text-[11px] text-slate-400 mt-4 font-medium">
                    <i class="fas fa-info-circle mr-1"></i>
                    Berdasarkan pendidikan formal terakhir yang tercatat di profil masing-masing pegawai.
                </p>
            </div>
            @endif
        </div>

        {{-- ─── RIGHT COL (1/3) ─── --}}
        <div class="space-y-6">

            {{-- Cuti Hari Ini --}}
            <div class="clay-card clay-card-amber p-6">
                <div class="section-title text-amber-400"><span>Cuti Aktif Hari Ini</span></div>
                @forelse($onLeaveToday as $leave)
                <div class="person-item mb-2">
                    <div class="person-avatar bg-amber-100 text-amber-800">
                        {{ strtoupper(substr($leave->employee->full_name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-800 text-sm truncate">{{ $leave->employee->full_name }}</div>
                        <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                            <span class="text-[10px] font-bold bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full">
                                {{ $leave->leave_type_label }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $leave->employee->school->name ?? '' }}</span>
                        </div>
                    </div>
                    <i class="fas fa-umbrella-beach text-amber-400 shrink-0"></i>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">☀️</div>
                    <p class="text-sm font-bold text-slate-600">Tidak ada pegawai cuti hari ini</p>
                    <p class="text-xs text-slate-400 mt-1">Semua pegawai hadir bekerja</p>
                </div>
                @endforelse
                <a href="{{ route('admin.employees.leaves.index') }}" class="flex items-center justify-center gap-2 mt-4 w-full py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-xl text-xs font-bold transition">
                    <i class="fas fa-arrow-right"></i> Lihat Semua Data Cuti
                </a>
            </div>

            {{-- Kontrak Akan Berakhir --}}
            <div class="clay-card clay-card-rose p-6">
                <div class="section-title text-rose-400"><span>⚠️ Kontrak Akan Berakhir</span></div>
                @forelse($expiringContracts as $contract)
                @php
                    $daysLeft = $contract->end_date->diffInDays(now());
                    $urgentClass = $daysLeft <= 7 ? 'bg-rose-100 text-rose-700' : 'bg-orange-100 text-orange-700';
                @endphp
                <div class="person-item mb-2">
                    <div class="person-avatar bg-rose-100 text-rose-800">
                        {{ strtoupper(substr($contract->employee->full_name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-bold text-slate-800 text-sm truncate">{{ $contract->employee->full_name }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">
                            Berakhir {{ $contract->end_date->format('d M Y') }}
                        </div>
                    </div>
                    <span class="shrink-0 px-2.5 py-1 rounded-xl text-[10px] font-black {{ $urgentClass }} whitespace-nowrap">
                        {{ $daysLeft }}h lagi
                    </span>
                </div>
                @empty
                <div class="text-center py-8">
                    <div class="text-4xl mb-3">🛡️</div>
                    <p class="text-sm font-bold text-slate-600">Tidak ada kontrak berakhir</p>
                    <p class="text-xs text-slate-400 mt-1">Semua kontrak masih aktif (30 hari)</p>
                </div>
                @endforelse
                <a href="{{ route('admin.employees.index', ['status' => 'kontrak']) }}" class="flex items-center justify-center gap-2 mt-4 w-full py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition">
                    <i class="fas fa-arrow-right"></i> Kelola Data Kontrak
                </a>
            </div>

            {{-- Fun Fact SDM --}}
            <div class="clay-card p-5" style="background: linear-gradient(135deg,#f0f9ff,#e0e7ff); box-shadow: 6px 6px 0 rgba(99,102,241,0.12);">
                <div class="section-title text-indigo-400 mb-4"><span>Ringkasan Cepat</span></div>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-3 bg-white/80 rounded-xl">
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                            <i class="fas fa-chalkboard text-indigo-500 w-4"></i> Tenaga Pengajar
                        </span>
                        <span class="font-black text-indigo-700 text-lg">{{ $stats['guru'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/80 rounded-xl">
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                            <i class="fas fa-briefcase text-amber-500 w-4"></i> Tenaga Kependidikan
                        </span>
                        <span class="font-black text-amber-700 text-lg">{{ $stats['staff'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/80 rounded-xl">
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                            <i class="fas fa-building text-violet-500 w-4"></i> Staf Yayasan
                        </span>
                        <span class="font-black text-violet-700 text-lg">{{ $stats['yayasan'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/80 rounded-xl">
                        <span class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                            <i class="fas fa-school text-emerald-500 w-4"></i> Jumlah Unit
                        </span>
                        <span class="font-black text-emerald-700 text-lg">{{ $schools->count() }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>
@endsection

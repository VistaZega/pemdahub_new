@extends('layouts.admin')

@section('title', 'Manajemen Sistem Blok')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap');
    .block-page { font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #fff7ed 0%, #fffbeb 50%, #fef3c7 100%); padding: 24px 24px 8px; min-height: 100vh; }
    
    .block-hero {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 40%, #d97706 80%, #b45309 100%);
        border-radius: 24px; padding: 28px 32px; margin-bottom: 24px;
        position: relative; overflow: hidden;
        box-shadow: 0 20px 40px -10px rgba(234,88,12,0.4);
    }
    .block-hero::before { content: ''; position: absolute; inset: 0; background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.1'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E"); }
    .hero-orb-1 { position:absolute; width:300px; height:300px; border-radius:50%; background:rgba(255,255,255,0.08); top:-100px; right:-50px; }
    .hero-orb-2 { position:absolute; width:200px; height:200px; border-radius:50%; background:rgba(255,255,255,0.06); bottom:-80px; left:30%; }
    
    .glass-card { background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.5); border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; margin-bottom: 24px; }
    .glass-header { padding: 16px 24px; border-bottom: 1px solid rgba(0,0,0,0.05); background: rgba(255,255,255,0.5); display: flex; align-items: center; justify-content: space-between; }
    .glass-title { font-size: 16px; font-weight: 700; color: #1f2937; display: flex; align-items: center; gap: 8px; }
    
    .form-input { width: 100%; padding: 10px 16px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 14px; font-weight: 500; transition: all 0.2s; color: #374151; background: white; }
    .form-input:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.15); outline: none; }
    
    .btn-primary { background: linear-gradient(135deg, #f97316, #ea580c); color: white; padding: 10px 20px; border-radius: 12px; font-weight: 600; font-size: 14px; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; border: none; cursor: pointer; box-shadow: 0 4px 12px rgba(234,88,12,0.3); }
    .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(234,88,12,0.4); }
    
    .btn-manage { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(37,99,235,0.2); transition: all 0.2s; text-decoration: none; }
    .btn-manage:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(37,99,235,0.3); color: white; }

    .btn-view { background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(124,58,237,0.2); transition: all 0.2s; text-decoration: none; }
    .btn-view:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(124,58,237,0.3); color: white; }

    .week-card { border-radius: 16px; padding: 16px; text-align: center; border: 2px solid transparent; transition: all 0.3s; position: relative; overflow: hidden; }
    .week-normal { background: linear-gradient(135deg, #eff6ff, #dbeafe); border-color: #bfdbfe; color: #1e3a8a; }
    .week-swapped { background: linear-gradient(135deg, #fff7ed, #ffedd5); border-color: #fed7aa; color: #9a3412; }
    .week-current { transform: scale(1.05); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); border-width: 3px; z-index: 10; }
    .week-current.week-normal { border-color: #3b82f6; }
    .week-current.week-swapped { border-color: #f97316; }
    
    .current-badge { position: absolute; top: 0; left: 50%; transform: translateX(-50%); background: #1f2937; color: white; font-size: 10px; font-weight: 800; padding: 2px 8px; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px; text-transform: uppercase; letter-spacing: 0.5px; }

    table th { background: #f8fafc; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; padding: 12px 16px; border-bottom: 2px solid #e2e8f0; text-align: left; }
    table td { padding: 12px 16px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; vertical-align: middle; }
    table tr:hover td { background-color: #f8fafc; }
</style>

<div class="block-page">
    <!-- Hero Header -->
    <div class="block-hero">
        <div class="hero-orb-1"></div>
        <div class="hero-orb-2"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-th-large text-white text-2xl"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-white tracking-tight">Sistem Jadwal Blok</h1>
                    <p class="text-orange-100 text-sm font-medium mt-1">
                        {{ $school->name ?? 'Sekolah' }} &bull; TP. {{ $academicYear->year ?? '' }} &bull; {{ $semester->semester_name ?? '' }}
                    </p>
                </div>
            </div>
            @if($blockSchedule)
            <a href="{{ route('admin.block-schedule.view') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/20 hover:bg-white/30 backdrop-blur-sm rounded-xl text-white text-sm font-semibold transition-all">
                <i class="fas fa-calendar-alt"></i> Lihat Jadwal Blok
            </a>
            @endif
        </div>
    </div>

    @if(session('success'))
    <div class="mb-5 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center gap-3 shadow-sm">
        <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"><i class="fas fa-check text-emerald-600"></i></div>
        <span class="font-medium text-sm">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="mb-5 px-4 py-3 rounded-xl bg-red-50 border border-red-200 text-red-800 flex items-center gap-3 shadow-sm">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0"><i class="fas fa-times text-red-600"></i></div>
        <span class="font-medium text-sm">{{ session('error') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Configuration Card (1/3 width on LG) -->
        <div class="lg:col-span-1">
            <div class="glass-card">
                <div class="glass-header">
                    <h2 class="glass-title"><i class="fas fa-cog text-orange-500"></i> Konfigurasi Sistem Blok</h2>
                </div>
                <div class="p-6">
                    <form action="{{ $blockSchedule ? route('admin.block-schedule.update', $blockSchedule->id) : route('admin.block-schedule.store') }}" method="POST">
                        @csrf
                        @if($blockSchedule)
                            @method('PUT')
                        @endif
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nama Konfigurasi</label>
                                <input type="text" name="name" class="form-input" required 
                                       value="{{ old('name', $blockSchedule->name ?? 'Blok ' . ($semester->semester_name ?? '') ) }}">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Mulai</label>
                                    <input type="date" name="start_date" class="form-input" required 
                                           value="{{ old('start_date', $blockSchedule ? $blockSchedule->start_date->format('Y-m-d') : '') }}">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tanggal Selesai</label>
                                    <input type="date" name="end_date" class="form-input" required 
                                           value="{{ old('end_date', $blockSchedule ? $blockSchedule->end_date->format('Y-m-d') : '') }}">
                                </div>
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">
                                    <i class="fas fa-sync-alt text-orange-400 mr-1"></i> Interval Rotasi (Minggu)
                                </label>
                                <select name="swap_interval_weeks" class="form-input" required>
                                    @for($i = 1; $i <= 8; $i++)
                                        <option value="{{ $i }}" {{ old('swap_interval_weeks', $blockSchedule->swap_interval_weeks ?? 2) == $i ? 'selected' : '' }}>
                                            Setiap {{ $i }} Minggu
                                        </option>
                                    @endfor
                                </select>
                                <p class="text-[11px] text-gray-400 mt-1.5">
                                    <i class="fas fa-info-circle"></i> Bisa diubah kapan saja — kalender rotasi otomatis menyesuaikan.
                                </p>
                            </div>
                            
                            <div class="pt-2">
                                <button type="submit" class="w-full justify-center btn-primary">
                                    <i class="fas fa-save"></i> {{ $blockSchedule ? 'Update Konfigurasi' : 'Simpan Konfigurasi' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Informasi Rotasi -->
            <div class="glass-card">
                <div class="glass-header">
                    <h2 class="glass-title"><i class="fas fa-info-circle text-blue-500"></i> Informasi Rotasi</h2>
                </div>
                <div class="p-5 space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0 mt-1">
                            <i class="fas fa-users text-blue-600 text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Rotasi Normal</h4>
                            <p class="text-xs text-gray-600 mt-0.5">Grup A → Ruang Kelas (Teori), Grup B → Ruang Lab (Praktik)</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-8 h-8 rounded-lg bg-orange-100 flex items-center justify-center shrink-0 mt-1">
                            <i class="fas fa-exchange-alt text-orange-600 text-sm"></i>
                        </div>
                        <div>
                            <h4 class="text-sm font-bold text-gray-800">Rotasi Ditukar (Swapped)</h4>
                            <p class="text-xs text-gray-600 mt-0.5">Grup A → Ruang Lab (Praktik), Grup B → Ruang Kelas (Teori)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Main Content Area (2/3 width on LG) -->
        <div class="lg:col-span-2 space-y-6">
            
            @if($blockSchedule)
            <!-- Timeline Rotasi -->
            <div class="glass-card">
                <div class="glass-header">
                    <h2 class="glass-title"><i class="fas fa-calendar-week text-orange-500"></i> Kalender Rotasi Blok</h2>
                    <div class="text-xs font-semibold bg-orange-100 px-3 py-1 rounded-full text-orange-700">
                        Interval: {{ $blockSchedule->swap_interval_weeks }} Minggu
                    </div>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
                        @foreach($swapPeriods as $index => $period)
                            @php 
                                $periodNum = $index + 1;
                                $isSwapped = $period['rotation'] === 'swapped';
                                $periodWeekStart = $period['week_number'];
                                $isCurrent = ($currentWeek >= $periodWeekStart && $currentWeek < $periodWeekStart + $blockSchedule->swap_interval_weeks);
                            @endphp
                            <div class="week-card {{ $isSwapped ? 'week-swapped' : 'week-normal' }} {{ $isCurrent ? 'week-current' : '' }}">
                                @if($isCurrent)
                                    <div class="current-badge">Sekarang</div>
                                @endif
                                <div class="text-xs font-bold opacity-70 mb-1 mt-1">Periode {{ $periodNum }}</div>
                                <div class="text-[10px] font-semibold leading-tight px-1 mb-2">
                                    {{ $period['start']->format('d M') }} - {{ $period['end']->format('d M') }}
                                </div>
                                <div class="w-8 h-8 rounded-full mx-auto flex items-center justify-center {{ $isSwapped ? 'bg-orange-200 text-orange-700' : 'bg-blue-200 text-blue-700' }}">
                                    <i class="fas {{ $isSwapped ? 'fa-exchange-alt' : 'fa-check' }} text-sm"></i>
                                </div>
                                <div class="text-[10px] mt-2 font-semibold">
                                    {{ $isSwapped ? 'Ditukar' : 'Normal' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Classrooms Table -->
            <div class="glass-card">
                <div class="glass-header">
                    <h2 class="glass-title"><i class="fas fa-door-open text-orange-500"></i> Daftar Kelas</h2>
                    <div class="text-xs font-semibold bg-gray-100 px-3 py-1 rounded-full text-gray-600">
                        {{ $classrooms->count() }} Kelas
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr>
                                <th>Nama Kelas</th>
                                <th class="text-center">Jml Siswa</th>
                                <th class="text-center">Grup A</th>
                                <th class="text-center">Grup B</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($classrooms as $classroom)
                                @php
                                    $total = $classroom->total_students;
                                    $gA = $classroom->group_a_count;
                                    $gB = $classroom->group_b_count;
                                    $unassigned = $total - ($gA + $gB);
                                    $isComplete = $total > 0 && $unassigned === 0;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="font-bold text-gray-800">{{ $classroom->class_name }}</div>
                                        <div class="text-xs text-gray-400">{{ $classroom->class_code }}</div>
                                    </td>
                                    <td class="text-center font-semibold">{{ $total }}</td>
                                    <td class="text-center">
                                        <span class="text-blue-600 font-bold">{{ $gA }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="text-orange-600 font-bold">{{ $gB }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($total == 0)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-gray-100 text-gray-500">Kosong</span>
                                        @elseif($isComplete)
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">✓ Selesai</span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-red-100 text-red-700 border border-red-200">Belum ({{ $unassigned }})</span>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('admin.block-schedule.groups', $classroom->id) }}" class="btn-manage" title="Bagi Grup">
                                                <i class="fas fa-users-cog"></i> Bagi Grup
                                            </a>
                                            <a href="{{ route('admin.block-schedule.view', ['classroom_id' => $classroom->id]) }}" class="btn-view" title="Lihat Jadwal">
                                                <i class="fas fa-calendar-alt"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-8">
                                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-3">
                                            <i class="fas fa-exclamation-circle text-gray-400 text-2xl"></i>
                                        </div>
                                        <p class="text-gray-500 font-medium">Tidak ada kelas ditemukan di unit sekolah ini.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <!-- Empty State -->
            <div class="glass-card p-10 text-center flex flex-col items-center justify-center" style="min-height:400px">
                <div class="w-24 h-24 mb-6 rounded-full bg-orange-100 flex items-center justify-center shadow-inner">
                    <i class="fas fa-cubes text-orange-500 text-4xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-2">Sistem Blok Belum Dikonfigurasi</h3>
                <p class="text-gray-500 max-w-md mx-auto mb-6 text-sm leading-relaxed">
                    Untuk menggunakan fitur Sistem Jadwal Blok, silakan atur konfigurasi pada form di samping terlebih dahulu
                    (Tanggal Mulai, Tanggal Selesai, dan Interval Rotasi).
                </p>
                <div class="inline-flex items-center gap-2 text-sm font-semibold text-orange-600 bg-orange-50 px-4 py-2 rounded-lg border border-orange-100">
                    <i class="fas fa-arrow-left"></i> Isi Form Konfigurasi
                </div>
            </div>
            @endif
            
        </div>
    </div>
</div>
@endsection

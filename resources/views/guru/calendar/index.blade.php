@extends('layouts.guru')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-6">

    {{-- Header Banner (Vibrant Neo-Brutalism for Guru) --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-emerald-900 via-teal-800 to-indigo-900 rounded-[2rem] shadow-2xl p-8 border-2 border-black">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-emerald-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-teal-500/20 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-amber-400 text-black text-xs font-black px-3 py-1 rounded-xl border border-black uppercase tracking-wider">Tahun Ajaran {{ $academicYear->year }}</span>
                    @if($academicYear->is_active)
                    <span class="bg-emerald-400 text-black text-xs font-black px-3 py-1 rounded-xl border border-black uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-black animate-pulse"></span> Aktif
                    </span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-300 shadow-xl">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                    Kalender Pendidikan ({{ $school->name }})
                </h1>
                <p class="text-emerald-100/90 mt-2 text-xs md:text-sm font-medium max-w-2xl">
                    Informasi agenda akademik sekolah, jadwal ujian, hari efektif belajar mengajar (HEB), dan kalender libur resmi.
                </p>
            </div>
        </div>
    </div>

    {{-- Statistik Hari Efektif Belajar (HEB) Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-indigo-50/80 rounded-3xl border-2 border-black p-5 shadow-md flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-black text-indigo-900 uppercase tracking-wider bg-indigo-200 px-2.5 py-0.5 rounded-lg border border-indigo-400 inline-block">Semester Ganjil</span>
                <div class="text-3xl font-black text-indigo-950">{{ $activeDaysGanjil }} <span class="text-sm font-bold text-indigo-700">Hari Efektif</span></div>
                <p class="text-[11px] font-bold text-indigo-700">Hari kegiatan belajar mengajar aktif</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-indigo-600 text-white flex items-center justify-center border-2 border-black shadow-sm text-2xl font-black shrink-0">
                <i class="fas fa-book-open"></i>
            </div>
        </div>

        <div class="bg-emerald-50/80 rounded-3xl border-2 border-black p-5 shadow-md flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-black text-emerald-900 uppercase tracking-wider bg-emerald-200 px-2.5 py-0.5 rounded-lg border border-emerald-400 inline-block">Semester Genap</span>
                <div class="text-3xl font-black text-emerald-950">{{ $activeDaysGenap }} <span class="text-sm font-bold text-emerald-700">Hari Efektif</span></div>
                <p class="text-[11px] font-bold text-emerald-700">Hari kegiatan belajar mengajar aktif</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 text-white flex items-center justify-center border-2 border-black shadow-sm text-2xl font-black shrink-0">
                <i class="fas fa-graduation-cap"></i>
            </div>
        </div>

        <div class="bg-amber-50/80 rounded-3xl border-2 border-black p-5 shadow-md flex items-center justify-between relative overflow-hidden">
            <div class="space-y-1">
                <span class="text-[10px] font-black text-amber-900 uppercase tracking-wider bg-amber-200 px-2.5 py-0.5 rounded-lg border border-amber-400 inline-block">Total 1 Tahun Ajaran</span>
                <div class="text-3xl font-black text-amber-950">{{ $activeDaysTotal }} <span class="text-sm font-bold text-amber-700">Hari Efektif</span></div>
                <p class="text-[11px] font-bold text-amber-700">Total akumulasi HEB tahunan</p>
            </div>
            <div class="w-14 h-14 rounded-2xl bg-amber-500 text-black flex items-center justify-center border-2 border-black shadow-sm text-2xl font-black shrink-0">
                <i class="fas fa-award"></i>
            </div>
        </div>
    </div>

    {{-- Main Calendar Card --}}
    <div class="bg-white rounded-3xl border-2 border-black p-6 shadow-xl space-y-6">
        
        {{-- Category Legend Badges --}}
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-slate-50 rounded-2xl border-2 border-black">
            <div class="flex flex-wrap items-center gap-2 text-xs font-black">
                <span class="text-black uppercase tracking-wider mr-1"><i class="fas fa-tags"></i> Kategori Agenda:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-purple-100 text-purple-900"><span class="w-3 h-3 rounded-full bg-purple-600 border border-black"></span> Monday Inspiration</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-rose-100 text-rose-900"><span class="w-3 h-3 rounded-full bg-rose-600 border border-black"></span> Libur Yayasan</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-orange-100 text-orange-900"><span class="w-3 h-3 rounded-full bg-orange-500 border border-black"></span> Kegiatan Yayasan</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-emerald-100 text-emerald-900"><span class="w-3 h-3 rounded-full bg-emerald-600 border border-black"></span> Kegiatan Sekolah</span>
            </div>
            <div class="text-[11px] font-bold text-slate-600">
                <i class="fas fa-info-circle text-teal-600"></i> Klik agenda di kalender untuk melihat rincian detail
            </div>
        </div>

        {{-- FullCalendar Mount Point --}}
        <div id="calendar" class="w-full"></div>
    </div>

</div>

{{-- Read-Only Modal --}}
<div id="eventModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 transition-opacity flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl overflow-hidden max-w-lg w-full transform transition-all">
        <div class="px-6 py-4 bg-black text-white flex justify-between items-center border-b-2 border-black">
            <h3 class="text-sm font-black uppercase tracking-wider flex items-center gap-2 text-amber-400" id="modalTitle">
                <i class="fas fa-calendar-day"></i> Detail Kegiatan
            </h3>
            <button type="button" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-rose-600 text-white flex items-center justify-center transition border border-white/20" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6 space-y-4 text-xs font-bold text-black">
            <div class="p-4 bg-slate-50 rounded-2xl border-2 border-black space-y-2">
                <div id="detailLevel" class="inline-block px-2.5 py-0.5 rounded-lg border border-black text-[10px] uppercase font-black"></div>
                <h4 id="detailTitle" class="text-base font-black text-black"></h4>
                <div class="flex items-center gap-2 text-slate-700">
                    <i class="fas fa-clock text-amber-500"></i> <span id="detailDates"></span>
                </div>
            </div>
            
            <div>
                <label class="block text-[10px] font-black text-slate-500 uppercase tracking-wider mb-1">Keterangan / Rincian:</label>
                <div id="detailDescription" class="p-3 bg-white rounded-xl border border-black text-slate-800"></div>
            </div>

            <div class="flex justify-end pt-2 border-t-2 border-black">
                <button type="button" class="bg-black hover:bg-slate-800 text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition border-2 border-black" onclick="closeModal()">Tutup</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<style>
    .fc-event { cursor: pointer; border-radius: 8px !important; border: 1.5px solid #000000 !important; font-weight: 800 !important; font-size: 0.75rem !important; padding: 2px 4px !important; transition: transform 0.15s ease; }
    .fc-event:hover { transform: scale(1.02); }
    .fc-toolbar-title { font-size: 1.15rem !important; font-weight: 900 !important; color: #000000 !important; text-transform: uppercase; tracking-wide; }
    
    th.fc-col-header-cell { background-color: #0f766e !important; border: 1.5px solid #000000 !important; }
    th.fc-col-header-cell .fc-col-header-cell-cushion { color: #ffffff !important; font-weight: 900; padding: 10px 4px; text-transform: uppercase; font-size: 0.75rem; }
    
    td.fc-daygrid-day:not(.fc-day-sat):not(.fc-day-sun) { background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; }
    td.fc-day-sat, td.fc-day-sun { background-color: #fff1f2 !important; border: 1px solid #fecdd3 !important; }
    td.fc-day-sat .fc-daygrid-day-number, td.fc-day-sun .fc-daygrid-day-number { color: #e11d48 !important; font-weight: 900; }
    
    .fc-day-today { background-color: #fef3c7 !important; border: 2px solid #f59e0b !important; }

    .fc-button-primary { background-color: #000000 !important; border: 2px solid #000000 !important; border-radius: 12px !important; font-weight: 900 !important; text-transform: uppercase !important; font-size: 0.7rem !important; }
    .fc-button-primary:hover { background-color: #0f766e !important; border-color: #000000 !important; }
    .fc-button-active { background-color: #0f766e !important; border-color: #000000 !important; }
    
    .fc-multimonth-title { color: #0f766e !important; font-weight: 900 !important; background-color: #ccfbf1; padding: 6px; border-radius: 12px; text-align: center; margin-bottom: 8px; border: 1.5px solid #000; text-transform: uppercase; }
    .fc-multimonth-daygrid { border: 2px solid #000000; border-radius: 16px; overflow: hidden; }

    .fc-scrollgrid, .fc-scrollgrid-table, .fc-scrollgrid-sync-table { width: 100% !important; table-layout: fixed !important; }
    .fc-view-harness { width: 100% !important; }
    .fc-event-title { white-space: normal !important; overflow: hidden; }
</style>
@endpush

@push('scripts')
<script>
    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            firstDay: 1,
            initialView: 'dayGridMonth',
            validRange: {
                start: '{{ \Carbon\Carbon::parse($academicYear->start_date)->format("Y-m-d") }}',
                end: '{{ \Carbon\Carbon::parse($academicYear->end_date)->addDay()->format("Y-m-d") }}'
            },
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,semester,year,listMonth'
            },
            buttonText: {
                dayGridMonth: 'Bulan',
                listMonth: 'Daftar Timeline',
                today: 'Hari Ini'
            },
            views: {
                semester: {
                    type: 'multiMonth',
                    duration: { months: 6 },
                    buttonText: 'Semester'
                },
                year: {
                    type: 'multiMonth',
                    duration: { months: 12 },
                    buttonText: '1 Tahun'
                }
            },
            locale: 'id',
            events: function(fetchInfo, successCallback, failureCallback) {
                fetch(`{{ route('guru.calendar.index') }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
            },
            eventClick: function(info) {
                let event = info.event;
                let props = event.extendedProps;

                document.getElementById('detailTitle').innerText = event.title;
                document.getElementById('detailDates').innerText = event.startStr + (event.endStr ? ' s.d. ' + event.endStr : '');
                
                let lvl = props.level === 'yayasan' ? 'Agenda Yayasan' : 'Agenda Sekolah';
                let lvlElem = document.getElementById('detailLevel');
                lvlElem.innerText = lvl;
                lvlElem.className = props.level === 'yayasan' ? 'inline-block px-2.5 py-0.5 rounded-lg border border-black text-[10px] uppercase font-black bg-purple-200 text-purple-900' : 'inline-block px-2.5 py-0.5 rounded-lg border border-black text-[10px] uppercase font-black bg-emerald-200 text-emerald-900';
                
                document.getElementById('detailDescription').innerText = props.description || 'Tidak ada keterangan tambahan.';
                document.getElementById('eventModal').classList.remove('hidden');
            }
        });
        calendar.render();

        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                setTimeout(() => { calendar.updateSize(); }, 50);
            });
            resizeObserver.observe(calendarEl.parentElement);
        }
    });
</script>
@endpush

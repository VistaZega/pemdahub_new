@extends('layouts.admin')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto space-y-6">

    {{-- Header Banner (Vibrant Neo-Brutalism) --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-indigo-900 via-indigo-800 to-violet-900 rounded-[2rem] shadow-2xl p-8 border-2 border-black">
        <!-- Background Ornaments -->
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 rounded-full bg-indigo-500/20 blur-3xl"></div>
        <div class="absolute bottom-0 left-0 -ml-16 -mb-16 w-64 h-64 rounded-full bg-violet-500/20 blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <span class="bg-amber-400 text-black text-xs font-black px-3 py-1 rounded-xl border border-black uppercase tracking-wider">Tahun Ajaran {{ $academicYear->year }}</span>
                    @if($academicYear->is_active)
                    <span class="bg-emerald-400 text-black text-xs font-black px-3 py-1 rounded-xl border border-black uppercase tracking-wider flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-black animate-pulse"></span> AKtif
                    </span>
                    @endif
                </div>
                <h1 class="text-2xl md:text-3xl font-black text-white flex items-center gap-3">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center text-amber-300 shadow-xl">
                        <i class="fas fa-calendar-alt text-2xl"></i>
                    </div>
                    Kalender Pendidikan {{ $school->name }}
                </h1>
                <p class="text-indigo-100/90 mt-2 text-xs md:text-sm font-medium max-w-2xl">
                    Kelola seluruh agenda kegiatan sekolah, jadwal ujian, dan kalender libur secara terstruktur dan terintegrasi dengan yayasan.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.calendar.print') }}" target="_blank" class="inline-flex items-center gap-2 bg-white hover:bg-slate-100 text-black font-black px-5 py-3 rounded-2xl text-xs uppercase tracking-wider transition border-2 border-black shadow-md">
                    <i class="fas fa-print text-indigo-600 text-sm"></i>
                    <span>Cetak PDF</span>
                </a>
                <button type="button" onclick="openCreateModal()" class="inline-flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-black font-black px-5 py-3 rounded-2xl text-xs uppercase tracking-wider transition border-2 border-black shadow-md">
                    <i class="fas fa-plus text-black"></i>
                    <span>Tambah Jadwal</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Alert Validation Messages --}}
    @if (session('success'))
        <div class="bg-emerald-100 border-2 border-black text-black px-5 py-3.5 rounded-2xl text-xs font-black shadow-sm flex items-center justify-between">
            <span class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-600 text-base"></i> {{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-black hover:text-rose-600 font-bold">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-rose-100 border-2 border-black text-black px-5 py-3.5 rounded-2xl text-xs font-black shadow-sm flex items-center justify-between">
            <span class="flex items-center gap-2"><i class="fas fa-exclamation-triangle text-rose-600 text-base"></i> {{ session('error') }}</span>
            <button onclick="this.parentElement.remove()" class="text-black hover:text-rose-600 font-bold">&times;</button>
        </div>
    @endif
    @if ($errors->any())
        <div class="bg-rose-100 border-2 border-black text-black px-5 py-3.5 rounded-2xl text-xs font-black shadow-sm">
            <div class="font-black text-rose-800 uppercase tracking-wider mb-1 flex items-center gap-1.5"><i class="fas fa-times-circle"></i> Terjadi Kesalahan Input:</div>
            <ul class="list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
        
        {{-- Category Legend Badges & Quick Controls --}}
        <div class="flex flex-wrap items-center justify-between gap-4 p-4 bg-slate-50 rounded-2xl border-2 border-black">
            <div class="flex flex-wrap items-center gap-2 text-xs font-black">
                <span class="text-black uppercase tracking-wider mr-1"><i class="fas fa-tags"></i> Kategori:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-purple-100 text-purple-900"><span class="w-3 h-3 rounded-full bg-purple-600 border border-black"></span> Monday Inspiration</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-rose-100 text-rose-900"><span class="w-3 h-3 rounded-full bg-rose-600 border border-black"></span> Libur Yayasan</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-orange-100 text-orange-900"><span class="w-3 h-3 rounded-full bg-orange-500 border border-black"></span> Kegiatan Yayasan</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-sky-100 text-sky-900"><span class="w-3 h-3 rounded-full bg-sky-600 border border-black"></span> Libur Sekolah</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl border border-black bg-emerald-100 text-emerald-900"><span class="w-3 h-3 rounded-full bg-emerald-600 border border-black"></span> Kegiatan Sekolah</span>
            </div>
            <div class="text-[11px] font-bold text-slate-600">
                <i class="fas fa-info-circle text-indigo-600"></i> Klik tanggal/agenda di kalender untuk mengedit
            </div>
        </div>

        {{-- FullCalendar Mount Point --}}
        <div id="calendar" class="w-full"></div>
    </div>

</div>

{{-- Neo-Brutalism Modal Create / Edit Agenda --}}
<div id="eventModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-xs z-50 transition-opacity flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border-2 border-black shadow-2xl overflow-hidden max-w-lg w-full transform transition-all">
        <div class="px-6 py-4 bg-black text-white flex justify-between items-center border-b-2 border-black">
            <h3 class="text-sm font-black uppercase tracking-wider flex items-center gap-2 text-amber-400" id="modalTitle">
                <i class="fas fa-calendar-plus"></i> Tambah Jadwal Sekolah
            </h3>
            <button type="button" class="w-8 h-8 rounded-xl bg-white/10 hover:bg-rose-600 text-white flex items-center justify-center transition border border-white/20" onclick="closeModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="p-6">
            <form id="eventForm" method="POST" action="{{ route('admin.calendar.store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1" for="title">Judul Kegiatan <span class="text-rose-600">*</span></label>
                    <input id="title" name="title" class="w-full bg-slate-50 border-2 border-black rounded-xl px-4 py-2.5 text-xs font-black text-black focus:bg-white focus:ring-4 focus:ring-black/20 outline-none" type="text" placeholder="Contoh: Ujian Tengah Semester Ganjil" required />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1" for="start_date">Tanggal Mulai <span class="text-rose-600">*</span></label>
                        <input id="start_date" name="start_date" class="w-full bg-slate-50 border-2 border-black rounded-xl px-4 py-2.5 text-xs font-black text-black focus:bg-white focus:ring-4 focus:ring-black/20 outline-none" type="date" required />
                    </div>
                    <div>
                        <label class="block text-xs font-black text-black uppercase tracking-wider mb-1" for="end_date">Tanggal Selesai <span class="text-rose-600">*</span></label>
                        <input id="end_date" name="end_date" class="w-full bg-slate-50 border-2 border-black rounded-xl px-4 py-2.5 text-xs font-black text-black focus:bg-white focus:ring-4 focus:ring-black/20 outline-none" type="date" required />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1" for="type">Jenis Agenda <span class="text-rose-600">*</span></label>
                    <select id="type" name="type" class="w-full bg-slate-50 border-2 border-black rounded-xl px-4 py-2.5 text-xs font-black text-black focus:bg-white focus:ring-4 focus:ring-black/20 outline-none appearance-none cursor-pointer" required>
                        <option value="school_event">Kegiatan Sekolah (Rapat, Ujian, Upacara, dll)</option>
                        <option value="holiday">Libur Sekolah (Cuti Khusus Sekolah)</option>
                    </select>
                </div>

                <div class="p-3 bg-amber-50 rounded-2xl border border-black space-y-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none">
                        <input type="checkbox" name="is_holiday" id="is_holiday" value="1" class="w-4 h-4 rounded border-2 border-black text-rose-600 focus:ring-black" />
                        <span class="text-xs font-black text-rose-700 uppercase">Tidak Ada KBM (Hari Libur / Non-Efektif)</span>
                    </label>
                    <p class="text-[11px] font-bold text-slate-600 pl-6">Jika dicentang, tanggal ini tidak dihitung sebagai hari aktif belajar mengajar (HEB).</p>
                </div>

                <div>
                    <label class="block text-xs font-black text-black uppercase tracking-wider mb-1" for="description">Keterangan Tambahan</label>
                    <textarea id="description" name="description" class="w-full bg-slate-50 border-2 border-black rounded-xl px-4 py-2 text-xs font-black text-black focus:bg-white focus:ring-4 focus:ring-black/20 outline-none" rows="3" placeholder="Catatan atau rincian tambahan kegiatan..."></textarea>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t-2 border-black">
                    <button type="button" class="bg-slate-100 hover:bg-slate-200 text-black font-black px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider transition border-2 border-black" onclick="closeModal()">Batal</button>
                    <button type="button" id="btnDelete" class="bg-rose-600 hover:bg-rose-700 text-white font-black px-4 py-2.5 rounded-xl text-xs uppercase tracking-wider transition border-2 border-black shadow-sm hidden" onclick="deleteEvent()">Hapus</button>
                    <button type="submit" class="bg-black hover:bg-emerald-600 text-white font-black px-6 py-2.5 rounded-xl text-xs uppercase tracking-wider transition border-2 border-black shadow-md flex items-center gap-1.5">
                        <i class="fas fa-save text-amber-400"></i> Simpan
                    </button>
                </div>
            </form>
            
            <form id="deleteForm" method="POST" action="" class="hidden">
                @csrf
                @method('DELETE')
            </form>
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
    
    /* Header background indigo pekat, teks putih */
    th.fc-col-header-cell { background-color: #312e81 !important; border: 1.5px solid #000000 !important; }
    th.fc-col-header-cell .fc-col-header-cell-cushion { color: #ffffff !important; font-weight: 900; padding: 10px 4px; text-transform: uppercase; font-size: 0.75rem; }
    
    /* Hari Aktif (Weekday) BODY hijau lembut */
    td.fc-daygrid-day:not(.fc-day-sat):not(.fc-day-sun) { background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; }
    
    /* Mark Weekends BODY pink lembut, teks tanggal merah */
    td.fc-day-sat, td.fc-day-sun { background-color: #fff1f2 !important; border: 1px solid #fecdd3 !important; }
    td.fc-day-sat .fc-daygrid-day-number, td.fc-day-sun .fc-daygrid-day-number { color: #e11d48 !important; font-weight: 900; }
    
    /* Today Cell Highlight */
    .fc-day-today { background-color: #fef3c7 !important; border: 2px solid #f59e0b !important; }

    /* Button Customization */
    .fc-button-primary { background-color: #000000 !important; border: 2px solid #000000 !important; border-radius: 12px !important; font-weight: 900 !important; text-transform: uppercase !important; font-size: 0.7rem !important; }
    .fc-button-primary:hover { background-color: #4f46e5 !important; border-color: #000000 !important; }
    .fc-button-active { background-color: #4f46e5 !important; border-color: #000000 !important; }
    
    /* Multi-month styling */
    .fc-multimonth-title { color: #312e81 !important; font-weight: 900 !important; background-color: #e0e7ff; padding: 6px; border-radius: 12px; text-align: center; margin-bottom: 8px; border: 1.5px solid #000; text-transform: uppercase; }
    .fc-multimonth-daygrid { border: 2px solid #000000; border-radius: 16px; overflow: hidden; }

    /* Fix FullCalendar width and spacing issues */
    .fc-scrollgrid, .fc-scrollgrid-table, .fc-scrollgrid-sync-table { width: 100% !important; table-layout: fixed !important; }
    .fc-view-harness { width: 100% !important; }

    /* Event text wrapping */
    .fc-event-title { white-space: normal !important; overflow: hidden; }
</style>
@endpush

@push('scripts')
<script>
    // Flag untuk mengecek apakah user adalah SuperAdmin
    const IS_SUPERADMIN = {{ auth()->user()->isSuperAdmin() ? 'true' : 'false' }};
    
    // Base URL calendar agar benar di localhost maupun production
    const CALENDAR_BASE_URL = "{{ url('admin/calendar') }}";

    function openCreateModal() {
        closeModal();
        let todayStr = new Date().toISOString().split('T')[0];
        document.getElementById('start_date').value = todayStr;
        document.getElementById('end_date').value = todayStr;
        document.getElementById('eventModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('eventModal').classList.add('hidden');
        document.getElementById('eventForm').reset();
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('eventForm').action = "{{ route('admin.calendar.store') }}";
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-calendar-plus"></i> Tambah Jadwal Sekolah';
        document.getElementById('btnDelete').classList.add('hidden');
    }

    function deleteEvent() {
        if(confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
            document.getElementById('deleteForm').submit();
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        
        // Ambil posisi terakhir kalender dari sessionStorage
        var savedView = sessionStorage.getItem('calendarView_admin') || 'dayGridMonth';
        var savedDate = sessionStorage.getItem('calendarDate_admin');
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
            firstDay: 1, // Start week on Monday
            initialView: savedView,
            initialDate: savedDate || undefined,
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
                fetch(`{{ route('admin.calendar.index') }}?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`, {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                })
                .then(response => response.json())
                .then(data => successCallback(data))
                .catch(error => failureCallback(error));
            },
            datesSet: function(info) {
                sessionStorage.setItem('calendarView_admin', info.view.type);
                sessionStorage.setItem('calendarDate_admin', calendar.getDate().toISOString());
            },
            eventClick: function(info) {
                let event = info.event;
                let props = event.extendedProps;

                if (props.level === 'school' || IS_SUPERADMIN) {
                    let modalTitle = props.level === 'yayasan' ? '<i class="fas fa-edit"></i> Edit Jadwal Yayasan' : '<i class="fas fa-edit"></i> Edit Jadwal Sekolah';
                    document.getElementById('modalTitle').innerHTML = modalTitle;
                    
                    document.getElementById('formMethod').value = 'PUT';
                    document.getElementById('eventForm').action = `${CALENDAR_BASE_URL}/${event.id}`;
                    document.getElementById('title').value = props.original_title || event.title;
                    document.getElementById('start_date').value = event.startStr.substring(0, 10);
                    
                    if (event.end) {
                        let endDate = new Date(event.end);
                        endDate.setDate(endDate.getDate() - 1);
                        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
                    } else {
                        document.getElementById('end_date').value = event.startStr.substring(0, 10);
                    }
                    
                    let formType = props.type;
                    if (props.level === 'yayasan' && formType === 'yayasan_event') {
                        formType = 'school_event';
                    }
                    
                    document.getElementById('type').value = formType;
                    document.getElementById('is_holiday').checked = props.is_holiday;
                    document.getElementById('description').value = props.description || '';
                    
                    document.getElementById('btnDelete').classList.remove('hidden');
                    document.getElementById('deleteForm').action = `${CALENDAR_BASE_URL}/${event.id}`;
                    
                    document.getElementById('eventModal').classList.remove('hidden');
                } else {
                    alert('Ini adalah jadwal Yayasan. Anda tidak memiliki akses untuk mengubahnya.');
                }
            },
            dateClick: function(info) {
                closeModal();
                document.getElementById('start_date').value = info.dateStr;
                document.getElementById('end_date').value = info.dateStr;
                document.getElementById('eventModal').classList.remove('hidden');
            }
        });
        calendar.render();
        
        if (window.ResizeObserver) {
            const resizeObserver = new ResizeObserver(() => {
                setTimeout(() => {
                    calendar.updateSize();
                }, 50);
            });
            resizeObserver.observe(calendarEl.parentElement);
        } else {
            setTimeout(function() {
                calendar.updateSize();
            }, 250);
        }
    });
</script>
@endpush

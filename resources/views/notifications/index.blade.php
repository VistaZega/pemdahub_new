@extends('layouts.app')

@section('title', 'Pusat Notifikasi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-3xl p-6 md:p-8 shadow-xl">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center text-2xl">🔔</div>
                <div>
                    <h1 class="text-2xl font-black">Pusat Notifikasi</h1>
                    <p class="text-white/70 text-sm font-bold">
                        @if($unreadCount > 0)
                        {{ $unreadCount }} notifikasi belum dibaca
                        @else
                        Semua notifikasi sudah dibaca ✅
                        @endif
                    </p>
                </div>
            </div>
            <div class="flex gap-2">
                @if($unreadCount > 0)
                <form method="POST" action="{{ route('notifications.mark-all-read') }}">
                    @csrf
                    <button type="submit" class="bg-white/20 hover:bg-white/30 text-white font-bold px-4 py-2 rounded-xl text-xs transition">
                        <i class="fas fa-check-double mr-1"></i> Tandai Semua Dibaca
                    </button>
                </form>
                @endif
                <select onchange="window.location.href='{{ route('notifications.index') }}?type='+this.value" class="bg-white/10 border border-white/20 text-white rounded-xl px-3 py-2 text-xs font-bold">
                    <option value="">Semua Tipe</option>
                    <option value="info" {{ $type == 'info' ? 'selected' : '' }}>Informasi</option>
                    <option value="grade" {{ $type == 'grade' ? 'selected' : '' }}>Nilai</option>
                    <option value="assignment" {{ $type == 'assignment' ? 'selected' : '' }}>Tugas</option>
                    <option value="bill" {{ $type == 'bill' ? 'selected' : '' }}>Tagihan</option>
                    <option value="warning" {{ $type == 'warning' ? 'selected' : '' }}>Peringatan</option>
                    <option value="alert" {{ $type == 'alert' ? 'selected' : '' }}>Penting</option>
                </select>
            </div>
        </div>
    </div>

    <!-- ══════════ KARTU KONTROL WEB PUSH NOTIFIKASI HP ══════════ -->
    <div class="bg-white rounded-3xl p-6 border-2 border-slate-200 shadow-sm" x-data="{
        masterEnabled: {{ $pushPref->push_enabled ? 'true' : 'false' }},
        lmsEnabled: {{ $pushPref->push_lms ? 'true' : 'false' }},
        cbtEnabled: {{ $pushPref->push_cbt ? 'true' : 'false' }},
        forumEnabled: {{ $pushPref->push_forum ? 'true' : 'false' }},
        schoolEnabled: {{ $pushPref->push_school ? 'true' : 'false' }},
        devicesCount: {{ (int)$pushDevicesCount }},
        loading: false,
        async toggleMaster() {
            this.loading = true;
            this.masterEnabled = !this.masterEnabled;
            await window.PembdaPush.toggleModule('push_enabled', this.masterEnabled);
            this.loading = false;
        },
        async toggleModule(field, currentVal) {
            const nextVal = !this[currentVal];
            this[currentVal] = nextVal;
            await window.PembdaPush.toggleModule(field, nextVal);
        },
        async registerThisDevice() {
            this.loading = true;
            const ok = await window.PembdaPush.enablePush();
            if (ok) {
                this.devicesCount++;
                this.masterEnabled = true;
                alert('Berhasil! Perangkat HP/Browser ini kini terhubung untuk menerima notifikasi.');
            }
            this.loading = false;
        }
    }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div class="flex items-start sm:items-center gap-3.5">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 border border-indigo-200 text-indigo-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-mobile-screen"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-slate-800 text-base">Notifikasi HP (Web Push)</h3>
                        <span class="text-[10px] font-mono-code font-black px-2 py-0.5 rounded-full"
                              :class="masterEnabled ? 'bg-emerald-100 text-emerald-800 border border-emerald-300' : 'bg-slate-100 text-slate-600 border border-slate-300'">
                            <span x-text="masterEnabled ? 'AKTIF 🟢' : 'NON-AKTIF ⚪'"></span>
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Kirim update tugas LMS, ujian CBT, dan balasan PembdaSPACE langsung ke bilah status HP Anda.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button type="button" 
                        @click="toggleMaster()"
                        :disabled="loading"
                        class="px-4 py-2 rounded-xl text-xs font-black transition flex items-center gap-2 shadow-xs"
                        :class="masterEnabled ? 'bg-emerald-500 hover:bg-emerald-600 text-white' : 'bg-slate-200 hover:bg-slate-300 text-slate-700'">
                    <i class="fa-solid" :class="masterEnabled ? 'fa-bell' : 'fa-bell-slash'"></i>
                    <span x-text="masterEnabled ? 'Notifikasi Aktif' : 'Notifikasi Dinonaktifkan'"></span>
                </button>
            </div>
        </div>

        <!-- Detail Filter Per Modul -->
        <div class="pt-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
            <!-- LMS -->
            <div class="p-3 rounded-2xl border transition" :class="lmsEnabled && masterEnabled ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200 opacity-60'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📚</span>
                        <span class="text-xs font-black text-slate-800">Tugas & Nilai LMS</span>
                    </div>
                    <input type="checkbox" :checked="lmsEnabled" :disabled="!masterEnabled" @change="toggleModule('push_lms', 'lmsEnabled')" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Tugas baru, koreksi nilai, & modul KBM</p>
            </div>

            <!-- CBT -->
            <div class="p-3 rounded-2xl border transition" :class="cbtEnabled && masterEnabled ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200 opacity-60'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">💻</span>
                        <span class="text-xs font-black text-slate-800">Ujian Online CBT</span>
                    </div>
                    <input type="checkbox" :checked="cbtEnabled" :disabled="!masterEnabled" @change="toggleModule('push_cbt', 'cbtEnabled')" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Token sesi ujian & pengumuman kelulusan</p>
            </div>

            <!-- PembdaSPACE -->
            <div class="p-3 rounded-2xl border transition" :class="forumEnabled && masterEnabled ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200 opacity-60'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">⚡</span>
                        <span class="text-xs font-black text-slate-800">PembdaSPACE</span>
                    </div>
                    <input type="checkbox" :checked="forumEnabled" :disabled="!masterEnabled" @change="toggleModule('push_forum', 'forumEnabled')" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Balasan topik Anda & mention diskusi</p>
            </div>

            <!-- Pengumuman Sekolah -->
            <div class="p-3 rounded-2xl border transition" :class="schoolEnabled && masterEnabled ? 'bg-indigo-50/40 border-indigo-200' : 'bg-slate-50 border-slate-200 opacity-60'">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-base">📢</span>
                        <span class="text-xs font-black text-slate-800">Pengumuman</span>
                    </div>
                    <input type="checkbox" :checked="schoolEnabled" :disabled="!masterEnabled" @change="toggleModule('push_school', 'schoolEnabled')" class="rounded text-indigo-600 focus:ring-indigo-500 h-4 w-4">
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5">Info darurat resmi Kepala Sekolah & Yayasan</p>
            </div>
        </div>

        <!-- Info Device & Tombol Sambungkan -->
        <div class="mt-4 pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-3 text-xs">
            <span class="text-slate-500 flex items-center gap-1.5">
                <i class="fa-solid fa-mobile-screen-button text-indigo-500"></i>
                <span x-text="devicesCount > 0 ? devicesCount + ' perangkat HP terhubung' : 'Belum ada perangkat HP terdaftar'"></span>
            </span>
            <button type="button" @click="registerThisDevice()" class="text-indigo-600 hover:text-indigo-800 font-bold underline flex items-center gap-1">
                <i class="fa-solid fa-link"></i> Hubungkan Notifikasi HP Ini
            </button>
        </div>
    </div>

    @if($notifications->count() > 0)
    <div class="space-y-3">
        @foreach($notifications as $n)
        <div class="bg-white rounded-2xl p-5 border-2 shadow-sm transition hover:shadow-md {{ $n->is_read ? 'border-slate-200' : 'border-indigo-300 bg-indigo-50/30' }}" id="notif-{{ $n->id }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0
                        {{ $n->type == 'grade' ? 'bg-emerald-100' : ($n->type == 'warning' ? 'bg-amber-100' : ($n->type == 'bill' ? 'bg-purple-100' : ($n->type == 'assignment' ? 'bg-blue-100' : 'bg-slate-100'))) }}">
                        @if($n->type == 'grade') 📝
                        @elseif($n->type == 'warning') ⚠️
                        @elseif($n->type == 'bill') 💰
                        @elseif($n->type == 'assignment') 📋
                        @elseif($n->type == 'alert') 🔴
                        @else ℹ️
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h4 class="font-bold text-sm text-slate-800">{{ $n->title }}</h4>
                            @if(!$n->is_read)
                            <span class="w-2 h-2 bg-indigo-500 rounded-full"></span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 mt-1">{{ $n->message }}</p>
                        <p class="text-[10px] text-slate-400 font-bold mt-1.5">{{ $n->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @if(!$n->is_read)
                <form method="POST" action="{{ route('notifications.mark-read', $n->id) }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 bg-indigo-100 hover:bg-indigo-200 text-indigo-600 rounded-lg flex items-center justify-center text-xs transition" title="Tandai dibaca">
                        <i class="fas fa-check"></i>
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @else
    <div class="bg-white rounded-3xl p-10 border-2 border-dashed border-slate-300 text-center">
        <div class="text-5xl mb-4">🔔</div>
        <h3 class="font-black text-lg text-slate-700 mb-2">Tidak Ada Notifikasi</h3>
        <p class="text-sm text-slate-500">Belum ada notifikasi untuk Anda.</p>
    </div>
    @endif
</div>
@endsection
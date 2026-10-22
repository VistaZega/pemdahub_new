@auth
<!-- Floating Global Web Push Notification Banner for Mobile & Desktop -->
<div id="pembda-push-prompt-banner" 
     x-data="{
        visible: false,
        loading: false,
        init() {
            // Tunggu 3 detik setelah halaman terbuka agar tidak mengagetkan user
            setTimeout(() => {
                this.checkStatus();
            }, 3000);
        },
        checkStatus() {
            if (!window.PembdaPush || !window.PembdaPush.isSupported()) return;
            
            // Cek apakah permission belum pernah ditentukan (masih 'default')
            if (Notification.permission === 'default') {
                const dismissed = localStorage.getItem('pembda_push_prompt_dismissed');
                if (dismissed && (Date.now() - parseInt(dismissed)) < (3 * 24 * 60 * 60 * 1000)) {
                    return; // Ditunda 3 hari
                }
                this.visible = true;
            }
        },
        async activate() {
            this.loading = true;
            const success = await window.PembdaPush.enablePush();
            this.loading = false;
            if (success) {
                this.visible = false;
            }
        },
        dismiss() {
            localStorage.setItem('pembda_push_prompt_dismissed', Date.now());
            this.visible = false;
        }
     }"
     x-show="visible"
     x-cloak
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-4"
     x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0"
     x-transition:leave-end="opacity-0 translate-y-4"
     class="fixed bottom-4 left-4 right-4 sm:left-auto sm:right-6 sm:w-96 z-50 bg-[#121316] text-white p-4 rounded-2xl shadow-[0_10px_25px_rgba(0,0,0,0.5)] border-2 border-indigo-400">
    
    <div class="flex items-start gap-3">
        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-500 to-purple-600 flex items-center justify-center text-lg shrink-0 shadow-sm">
            🔔
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="font-black text-sm text-white flex items-center gap-1.5">
                <span>Notifikasi HP PembdaHUB</span>
                <span class="text-[9px] font-mono-code font-black px-1.5 py-0.2 rounded bg-emerald-500 text-white">UPDATE</span>
            </h4>
            <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                Aktifkan notifikasi langsung di status bar HP Anda untuk info tugas LMS, ujian CBT & balasan PembdaSPACE.
            </p>

            <div class="flex items-center gap-2 mt-3">
                <button type="button" 
                        @click="activate()"
                        :disabled="loading"
                        class="px-3.5 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-black rounded-xl transition flex items-center gap-1.5 shadow-sm active:scale-95">
                    <span x-show="!loading">Aktifkan Sekarang 🚀</span>
                    <span x-show="loading">Menghubungkan...</span>
                </button>
                <button type="button" 
                        @click="dismiss()"
                        class="px-2.5 py-1.5 bg-white/10 hover:bg-white/20 text-slate-300 text-xs font-bold rounded-xl transition">
                    Nanti Saja
                </button>
            </div>
        </div>
    </div>
</div>
@endauth

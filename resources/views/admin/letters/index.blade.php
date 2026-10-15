@extends($layout ?? auth()->user()->layout ?? 'layouts.app')

@section('title', 'Surat Masuk Yayasan')

@section('content')
<div class="space-y-6">
    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-violet-700 via-indigo-700 to-purple-800 rounded-2xl p-6 text-white shadow-xl">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="bg-white/20 px-2.5 py-0.5 rounded text-xs font-semibold uppercase tracking-wider">Inboks Surat Masuk</span>
                    <span class="bg-amber-400/30 text-amber-200 border border-amber-400/30 px-2 py-0.5 rounded text-xs font-medium"><i class="fas fa-landmark mr-1"></i> Yayasan Perguruan PEMBDA Nias</span>
                </div>
                <h1 class="text-2xl font-bold">Surat & Edaran Resmi Yayasan</h1>
                <p class="text-violet-200 text-sm mt-1">Daftar instruksi, edaran, dan pengumuman resmi yang ditujukan bagi unit sekolah</p>
            </div>
        </div>
    </div>

    {{-- Table List --}}
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        @if($letters->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <i class="fas fa-envelope-open text-4xl text-gray-300 mb-3"></i>
            <p class="font-medium text-gray-700">Belum ada surat edaran masuk dari Yayasan.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 font-bold uppercase text-xs">
                    <tr>
                        <th class="py-3.5 px-4">No. Surat</th>
                        <th class="py-3.5 px-4">Perihal / Judul Surat</th>
                        <th class="py-3.5 px-4">Tgl Terbit</th>
                        <th class="py-3.5 px-4">Tenggat Waktu</th>
                        <th class="py-3.5 px-4">TTD Digital & QR</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($letters as $letter)
                    <tr class="hover:bg-violet-50/30 transition-colors">
                        <td class="py-4 px-4 font-mono font-bold text-gray-900 text-xs">
                            {{ $letter->letter_number }}
                        </td>
                        <td class="py-4 px-4">
                            <a href="{{ route('admin.letters.show', $letter->id) }}" class="font-bold text-gray-900 hover:text-violet-700 transition-colors">
                                {{ $letter->title }}
                            </a>
                        </td>
                        <td class="py-4 px-4 text-xs text-gray-600">
                            {{ \Carbon\Carbon::parse($letter->effective_date)->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-4 px-4 text-xs">
                            @if($letter->deadline_date)
                            <span class="inline-block bg-amber-100 text-amber-800 px-2 py-0.5 rounded font-bold">
                                <i class="fas fa-clock mr-1"></i>{{ \Carbon\Carbon::parse($letter->deadline_date)->translatedFormat('d M Y') }}
                            </span>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-4 px-4">
                            <span class="inline-flex items-center gap-1 text-emerald-700 text-xs font-bold bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                <i class="fas fa-qrcode"></i> Valid
                            </span>
                        </td>
                        <td class="py-4 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                <button type="button" onclick="copyVerificationLink('{{ $letter->verification_url }}')" class="bg-teal-50 hover:bg-teal-100 text-teal-700 font-bold px-2.5 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1 border border-teal-200" title="Salin Link Verifikasi Resmi">
                                    <i class="fas fa-link text-[11px]"></i>
                                    <span class="hidden sm:inline">Salin Link</span>
                                </button>
                                <a href="{{ $letter->wa_share_url }}" target="_blank" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-2.5 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1 shadow-sm" title="Bagikan ke WhatsApp">
                                    <i class="fab fa-whatsapp text-xs"></i>
                                    <span class="hidden sm:inline">Bagikan WA</span>
                                </a>
                                <a href="{{ route('admin.letters.show', $letter->id) }}" class="bg-violet-600 hover:bg-violet-700 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition-colors flex items-center gap-1">
                                    <i class="fas fa-book-open text-[11px]"></i>
                                    <span>Baca</span>
                                </a>
                                <a href="{{ route('admin.letters.print', $letter->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold px-2.5 py-1.5 rounded-lg text-xs transition-colors border border-gray-200" title="Cetak PDF">
                                    <i class="fas fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $letters->links() }}
        </div>
        @endif
    </div>
</div>

<script>
function showCopyToast(message) {
    let toast = document.getElementById('letter-copy-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'letter-copy-toast';
        toast.className = 'fixed bottom-5 right-5 z-50 bg-gray-950 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-emerald-500/60 transition-all duration-300 transform translate-y-10 opacity-0 pointer-events-none text-xs md:text-sm font-semibold';
        document.body.appendChild(toast);
    }
    toast.innerHTML = `<i class="fas fa-check-circle text-emerald-400 text-base"></i> <span>${message}</span>`;
    toast.classList.remove('translate-y-10', 'opacity-0', 'pointer-events-none');
    toast.classList.add('translate-y-0', 'opacity-100');
    setTimeout(() => {
        toast.classList.add('translate-y-10', 'opacity-0', 'pointer-events-none');
        toast.classList.remove('translate-y-0', 'opacity-100');
    }, 2800);
}

function copyVerificationLink(url) {
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(() => {
            showCopyToast('Link verifikasi surat resmi berhasil disalin!');
        }).catch(() => fallbackCopy(url, 'Link verifikasi surat resmi berhasil disalin!'));
    } else {
        fallbackCopy(url, 'Link verifikasi surat resmi berhasil disalin!');
    }
}

function fallbackCopy(text, msg) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.left = '-9999px';
    document.body.appendChild(ta);
    ta.focus();
    ta.select();
    try {
        document.execCommand('copy');
        showCopyToast(msg);
    } catch (err) {
        alert(msg + '\n\n' + text);
    }
    document.body.removeChild(ta);
}
</script>
@endsection

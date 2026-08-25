<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PSB Test - Simulasi Notifikasi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 min-h-screen">
    <nav class="bg-white shadow-lg border-b-4 border-indigo-500">
        <div class="container mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center text-white text-2xl">
                        🧪
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800">PSB Testing Dashboard</h1>
                        <p class="text-xs text-gray-500">Simulasi & Preview Notifikasi</p>
                    </div>
                </div>
                <a href="{{ route('public.registration.index') }}" class="px-4 py-2 bg-gray-100 rounded-lg hover:bg-gray-200 text-sm">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Form
                </a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        {{-- Info Banner --}}
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 mb-8 border-2 border-blue-200">
            <div class="flex items-start gap-4">
                <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center text-white text-2xl flex-shrink-0">
                    <i class="fas fa-info-circle"></i>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-blue-900 mb-2">Cara Testing Sistem Komunikasi PSB</h2>
                    <p class="text-blue-700 text-sm mb-3">
                        Halaman ini untuk test & preview notifikasi <strong>tanpa benar-benar mengirim</strong> email/WhatsApp/SMS.
                        Anda bisa melihat semua template dan simulasi flow komunikasi.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                            <p class="font-bold text-blue-800 mb-1"><i class="fas fa-eye mr-2"></i>Preview</p>
                            <p class="text-gray-600 text-xs">Lihat template notifikasi</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                            <p class="font-bold text-blue-800 mb-1"><i class="fas fa-play mr-2"></i>Simulasi</p>
                            <p class="text-gray-600 text-xs">Test flow tanpa kirim nyata</p>
                        </div>
                        <div class="bg-white rounded-lg p-3 border border-blue-200">
                            <p class="font-bold text-blue-800 mb-1"><i class="fas fa-file-alt mr-2"></i>Log</p>
                            <p class="text-gray-600 text-xs">Tracking simulasi di log file</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Applicant List --}}
        <div class="bg-white rounded-2xl shadow-xl p-6 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center gap-3">
                <span class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                    <i class="fas fa-users"></i>
                </span>
                Daftar Pendaftar (Test Data)
            </h2>

            @if($applicants->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b-2 border-gray-200">
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">No. Reg</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Nama</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Sekolah</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                                <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($applicants as $applicant)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-4">
                                    <span class="font-mono text-sm font-semibold text-blue-600">{{ $applicant->registration_number }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-gray-800">{{ $applicant->full_name }}</p>
                                    <p class="text-xs text-gray-500">NISN: {{ $applicant->nisn }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $applicant->school->name }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $applicant->email ?? '-' }}</td>
                                <td class="px-4 py-4 text-sm text-gray-600">{{ $applicant->phone ?? '-' }}</td>
                                <td class="px-4 py-4">
                                    <span class="px-2 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                                        {{ $applicant->status }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <button onclick="openTestModal('{{ $applicant->registration_number }}', '{{ addslashes($applicant->full_name) }}')" 
                                            class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-500 text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                                        <i class="fas fa-vial mr-2"></i>Test Notifikasi
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-inbox text-4xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-500 mb-4">Belum ada data pendaftar</p>
                    <a href="{{ route('public.registration.index') }}" class="inline-block px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-xl font-semibold hover:shadow-xl transition-all">
                        <i class="fas fa-plus mr-2"></i>Buat Pendaftaran Test
                    </a>
                </div>
            @endif
        </div>

        {{-- Quick Links --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-book text-purple-500"></i>
                    Dokumentasi
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <a href="/PSB_COMMUNICATION_FLOW.md" target="_blank" class="text-blue-600 hover:underline flex items-center gap-2">
                            <i class="fas fa-file-alt"></i>PSB_COMMUNICATION_FLOW.md
                        </a>
                    </li>
                    <li class="text-gray-600 pl-6">Template lengkap email, WA, SMS</li>
                </ul>
            </div>

            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-file-code text-orange-500"></i>
                    Log Files
                </h3>
                <ul class="space-y-2 text-sm">
                    <li>
                        <span class="font-mono text-xs bg-gray-100 px-2 py-1 rounded">storage/logs/psb-notification-simulation.log</span>
                    </li>
                    <li class="text-gray-600">Tracking semua simulasi notifikasi</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Test Modal --}}
    <div id="testModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-2xl font-bold mb-1">Test Notifikasi</h3>
                        <p class="text-sm opacity-90" id="modalApplicantName">-</p>
                        <p class="text-xs opacity-75 font-mono" id="modalRegNumber">-</p>
                    </div>
                    <button onclick="closeTestModal()" class="w-10 h-10 bg-white bg-opacity-20 rounded-full hover:bg-opacity-30 transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px)">
                <p class="text-gray-600 mb-6 text-sm">Pilih tipe notifikasi untuk melihat preview dan simulasi pengiriman:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Registration --}}
                    <div class="border-2 border-green-200 rounded-xl p-4 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center text-green-600 text-xl">
                                ✅
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">1. Konfirmasi Pendaftaran</h4>
                                <p class="text-xs text-gray-500">Email + WhatsApp</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="previewNotification('registration', 'email')" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </button>
                            <button onclick="previewNotification('registration', 'whatsapp')" class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i class="fab fa-whatsapp mr-1"></i>WA
                            </button>
                        </div>
                    </div>

                    {{-- Payment --}}
                    <div class="border-2 border-blue-200 rounded-xl p-4 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-xl">
                                💳
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">2. Pembayaran Terverifikasi</h4>
                                <p class="text-xs text-gray-500">Email + WhatsApp</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="previewNotification('payment', 'email')" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </button>
                            <button onclick="previewNotification('payment', 'whatsapp')" class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i class="fab fa-whatsapp mr-1"></i>WA
                            </button>
                        </div>
                    </div>

                    {{-- Document --}}
                    <div class="border-2 border-purple-200 rounded-xl p-4 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center text-purple-600 text-xl">
                                📄
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">3. Dokumen Lengkap</h4>
                                <p class="text-xs text-gray-500">Email + WhatsApp</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="previewNotification('document', 'email')" class="flex-1 px-3 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </button>
                            <button onclick="previewNotification('document', 'whatsapp')" class="flex-1 px-3 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i class="fab fa-whatsapp mr-1"></i>WA
                            </button>
                        </div>
                    </div>

                    {{-- Test Schedule --}}
                    <div class="border-2 border-orange-200 rounded-xl p-4 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center text-orange-600 text-xl">
                                📝
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">4. Jadwal Tes</h4>
                                <p class="text-xs text-gray-500">Email + WA + SMS</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="previewNotification('test_schedule', 'email')" class="flex-1 px-2 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </button>
                            <button onclick="previewNotification('test_schedule', 'whatsapp')" class="flex-1 px-2 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i class="fab fa-whatsapp mr-1"></i>WA
                            </button>
                            <button onclick="previewNotification('test_schedule', 'sms')" class="flex-1 px-2 py-2 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold hover:bg-purple-200">
                                <i class="fas fa-sms mr-1"></i>SMS
                            </button>
                        </div>
                    </div>

                    {{-- Result Accepted --}}
                    <div class="border-2 border-pink-200 rounded-xl p-4 hover:shadow-lg transition-all">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center text-pink-600 text-xl">
                                🎉
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">5. Pengumuman (Diterima)</h4>
                                <p class="text-xs text-gray-500">Email + WA + SMS</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="previewNotification('result_accepted', 'email')" class="flex-1 px-2 py-2 bg-blue-100 text-blue-700 rounded-lg text-xs font-semibold hover:bg-blue-200">
                                <i class="fas fa-envelope mr-1"></i>Email
                            </button>
                            <button onclick="previewNotification('result_accepted', 'whatsapp')" class="flex-1 px-2 py-2 bg-green-100 text-green-700 rounded-lg text-xs font-semibold hover:bg-green-200">
                                <i class="fab fa-whatsapp mr-1"></i>WA
                            </button>
                            <button onclick="previewNotification('result_accepted', 'sms')" class="flex-1 px-2 py-2 bg-purple-100 text-purple-700 rounded-lg text-xs font-semibold hover:bg-purple-200">
                                <i class="fas fa-sms mr-1"></i>SMS
                            </button>
                        </div>
                    </div>

                    {{-- Test All --}}
                    <div class="md:col-span-2 border-4 border-indigo-300 rounded-xl p-4 bg-gradient-to-br from-indigo-50 to-purple-50">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-12 h-12 bg-indigo-500 rounded-full flex items-center justify-center text-white text-xl">
                                🚀
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Simulasi Lengkap (Semua Fase)</h4>
                                <p class="text-xs text-gray-500">Test flow komunikasi dari awal sampai akhir</p>
                            </div>
                        </div>
                        <button onclick="simulateFullFlow()" class="w-full px-4 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white rounded-lg font-bold hover:shadow-xl transition-all">
                            <i class="fas fa-rocket mr-2"></i>Jalankan Simulasi Lengkap
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentRegNumber = '';

        function openTestModal(regNumber, name) {
            currentRegNumber = regNumber;
            document.getElementById('modalRegNumber').textContent = 'No. Reg: ' + regNumber;
            document.getElementById('modalApplicantName').textContent = name;
            document.getElementById('testModal').classList.remove('hidden');
        }

        function closeTestModal() {
            document.getElementById('testModal').classList.add('hidden');
        }

        function previewNotification(type, channel) {
            const url = `/psb-test/preview/${channel}/${type}/${currentRegNumber}`;
            window.open(url, '_blank', 'width=800,height=600');
        }

        async function simulateFullFlow() {
            if (!currentRegNumber) {
                alert('Pilih salah satu pendaftar terlebih dahulu.');
                return;
            }

            if (confirm('Jalankan simulasi pengiriman seluruh alur notifikasi (Registrasi, Pembayaran, Dokumen, Jadwal Tes, Kelulusan) untuk pendaftar ini?')) {
                const types = ['registration', 'payment_verified', 'document_verified', 'test_schedule', 'result_accepted'];
                let logs = [];

                for (const type of types) {
                    try {
                        const res = await fetch('{{ route("psb.test.simulate") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                registration_number: currentRegNumber,
                                notification_type: type
                            })
                        });
                        const data = await res.json();
                        if (data.success) {
                            logs.push(`✓ [${type}] Berhasil disimulasikan`);
                        }
                    } catch (e) {
                        logs.push(`✗ [${type}] Gagal: ${e.message}`);
                    }
                }

                alert(`Simulasi Selesai!\n\n${logs.join('\n')}\n\nLog simulasi lengkap tersimpan di storage/logs/psb-notification-simulation.log`);
            }
        }

        // Close modal on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTestModal();
            }
        });
    </script>
</body>
</html>


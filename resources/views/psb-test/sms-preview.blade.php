<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview SMS - {{ ucfirst($type) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-900 p-8">
    <div class="max-w-md mx-auto">
        {{-- Phone Frame --}}
        <div class="bg-black rounded-[3rem] shadow-2xl overflow-hidden border-8 border-gray-900 p-3">
            {{-- Screen --}}
            <div class="bg-white rounded-[2.5rem] overflow-hidden">
                {{-- Status Bar --}}
                <div class="bg-gray-50 px-6 py-2 flex items-center justify-between text-xs">
                    <span class="font-semibold">{{ now()->format('H:i') }}</span>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-signal"></i>
                        <i class="fas fa-wifi"></i>
                        <i class="fas fa-battery-full"></i>
                    </div>
                </div>

                {{-- SMS Header --}}
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-4 flex items-center gap-3">
                    <button class="text-white">
                        <i class="fas fa-arrow-left"></i>
                    </button>
                    <div class="flex-1">
                        <p class="font-semibold">Panitia PSB PEMBDA</p>
                        <p class="text-xs opacity-90">082373642864</p>
                    </div>
                    <button class="text-white">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                </div>

                {{-- SMS Content --}}
                <div class="bg-gray-50 p-4 min-h-96">
                    {{-- Date Divider --}}
                    <div class="text-center mb-4">
                        <span class="bg-gray-200 px-3 py-1 rounded-full text-xs text-gray-600">
                            {{ now()->format('d/m/Y') }}
                        </span>
                    </div>

                    {{-- SMS Bubble --}}
                    <div class="mb-4">
                        <div class="bg-white rounded-2xl rounded-tl-sm px-4 py-3 shadow-sm max-w-xs">
                            <p class="text-sm text-gray-800">{{ $message }}</p>
                            <div class="flex items-center justify-between mt-2">
                                <span class="text-xs text-gray-500">{{ now()->format('H:i') }}</span>
                                <i class="fas fa-check text-blue-500 text-xs"></i>
                            </div>
                        </div>
                    </div>

                    {{-- Character Count --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-sm">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-bold text-blue-800">📊 Info SMS</span>
                            <span class="text-xs bg-blue-200 px-2 py-1 rounded-full">
                                {{ strlen($message) }} karakter
                            </span>
                        </div>
                        <div class="text-xs text-blue-700 space-y-1">
                            <p><strong>Biaya SMS:</strong> {{ ceil(strlen($message) / 160) }} SMS (Rp {{ ceil(strlen($message) / 160) * 250 }})</p>
                            <p><strong>Kepada:</strong> {{ $applicant->full_name }}</p>
                            <p><strong>No. HP:</strong> {{ $applicant->phone }}</p>
                        </div>
                    </div>

                    {{-- Warning --}}
                    <div class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded-lg text-sm">
                        <p class="font-bold text-yellow-800 mb-1">📱 Preview SMS</p>
                        <p class="text-yellow-700 text-xs">Ini adalah preview. SMS tidak benar-benar dikirim.</p>
                        <p class="text-yellow-700 text-xs mt-1"><strong>Tipe:</strong> {{ ucfirst(str_replace('_', ' ', $type)) }}</p>
                    </div>
                </div>

                {{-- Reply Area --}}
                <div class="bg-white border-t border-gray-200 p-3 flex items-center gap-3">
                    <div class="flex-1 bg-gray-100 rounded-full px-4 py-2">
                        <input type="text" placeholder="Type message" class="bg-transparent w-full outline-none text-sm" disabled>
                    </div>
                    <button class="w-10 h-10 bg-blue-500 text-white rounded-full flex items-center justify-center" disabled>
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="mt-6 flex gap-3">
            <button onclick="window.close()" class="flex-1 px-4 py-3 bg-gray-600 text-white rounded-xl font-semibold hover:bg-gray-700">
                <i class="fas fa-times mr-2"></i>Tutup
            </button>
            <button onclick="copyMessage()" class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700">
                <i class="fas fa-copy mr-2"></i>Copy Text
            </button>
        </div>
    </div>

    <script>
        function copyMessage() {
            const message = `{{ $message }}`;
            navigator.clipboard.writeText(message).then(() => {
                alert('SMS berhasil dicopy!');
            });
        }
    </script>
</body>
</html>


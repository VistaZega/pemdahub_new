@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <a href="{{ route('admin.time-slots.index', ['school_id' => $selectedSchoolId, 'day' => $selectedDay]) }}" 
           class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Time Slot
        </a>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-plus-circle mr-2"></i>Tambah Time Slot - {{ $days[$selectedDay] }}
        </h1>
    </div>

    @if($errors->any())
    <div class="mb-4 p-4 bg-red-50 border-l-4 border-red-500 text-red-700 rounded">
        <div class="flex items-center mb-2">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <strong>Terdapat kesalahan:</strong>
        </div>
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('admin.time-slots.store') }}" method="POST">
            @csrf
            
            <input type="hidden" name="school_id" value="{{ $selectedSchoolId }}">
            <input type="hidden" name="day_of_week" value="{{ $selectedDay }}">
            <input type="hidden" name="academic_year_id" value="{{ $selectedYearId }}">

            <!-- Info Box -->
            <div class="mb-6 bg-blue-50 border-l-4 border-blue-500 p-4 rounded">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Urutan berikutnya:</strong> {{ $maxOrder + 1 }}. 
                    Anda bisa mengubahnya jika perlu.
                </p>
            </div>

            <!-- Slot Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Slot <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="slot_name" 
                       value="{{ old('slot_name') }}"
                       placeholder="Contoh: Les 1, Istirahat 1, Apel Pagi"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       required>
                <p class="mt-1 text-sm text-gray-500">Nama yang akan ditampilkan di jadwal</p>
            </div>

            <!-- Shift KBM -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Shift KBM <span class="text-red-500">*</span>
                </label>
                <select name="shift" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="pagi" {{ old('shift', 'pagi') == 'pagi' ? 'selected' : '' }}>Shift Pagi (Reguler)</option>
                    <option value="siang" {{ old('shift') == 'siang' ? 'selected' : '' }}>Shift Siang (Eksekutif)</option>
                    <option value="all" {{ old('shift') == 'all' ? 'selected' : '' }}>Semua Shift</option>
                </select>
            </div>

            <!-- Slot Type -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tipe Slot <span class="text-red-500">*</span>
                </label>
                <select name="slot_type" 
                        id="slotType"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                        required>
                    <option value="lesson" {{ old('slot_type') == 'lesson' ? 'selected' : '' }}>Pelajaran
                    </option>
                    <option value="break" {{ old('slot_type') == 'break' ? 'selected' : '' }}>Istirahat
                    </option>
                    <option value="ceremony" {{ old('slot_type') == 'ceremony' ? 'selected' : '' }}>Upacara / Kegiatan
                    </option>
                </select>
            </div>

            <!-- Slot Order -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Urutan Slot <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="slot_order" 
                       value="{{ old('slot_order', $maxOrder + 1) }}"
                       min="1"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                       required>
                <p class="mt-1 text-sm text-gray-500">Urutan tampilan di jadwal (1, 2, 3, dst)</p>
            </div>

            <!-- Time Range -->
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="start_time" 
                           value="{{ old('start_time') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="end_time" 
                           value="{{ old('end_time') }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>
            </div>

            <!-- Is Teaching Slot -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="hidden" name="is_teaching_slot" value="0">
                    <input type="checkbox" 
                           name="is_teaching_slot" 
                           value="1"
                           id="isTeachingSlot"
                           {{ old('is_teaching_slot', 1) == 1 ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">
                        <strong>Slot untuk Mengajar</strong>
                        <span class="block text-gray-500">Centang jika slot ini bisa digunakan untuk jadwal mengajar</span>
                    </span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.time-slots.index', ['school_id' => $selectedSchoolId, 'day' => $selectedDay]) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Simpan Time Slot
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Auto update is_teaching_slot based on slot_type
document.getElementById('slotType').addEventListener('change', function() {
    const isTeachingCheckbox = document.getElementById('isTeachingSlot');
    if (this.value === 'lesson') {
        isTeachingCheckbox.checked = true;
    } else {
        isTeachingCheckbox.checked = false;
    }
});
</script>
@endsection

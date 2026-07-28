@extends('layouts.admin')

@section('content')
<div class="space-y-6">
    <div class="mb-6">
        <a href="{{ route('admin.time-slots.index', ['school_id' => $timeSlot->school_id, 'day' => $timeSlot->day_of_week]) }}" 
           class="text-indigo-600 hover:text-indigo-800 flex items-center gap-2 mb-4">
            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Time Slot
        </a>
        <h1 class="text-2xl font-bold text-gray-800">
            <i class="fas fa-edit mr-2"></i>Edit Time Slot - {{ $days[$timeSlot->day_of_week] }}
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
        <form action="{{ route('admin.time-slots.update', $timeSlot) }}" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="school_id" value="{{ $timeSlot->school_id }}">
            <input type="hidden" name="day_of_week" value="{{ $timeSlot->day_of_week }}">
            <input type="hidden" name="academic_year_id" value="{{ $timeSlot->academic_year_id }}">

            <!-- Slot Name -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nama Slot <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="slot_name" 
                       value="{{ old('slot_name', $timeSlot->slot_name) }}"
                       placeholder="Contoh: Les 1, Istirahat 1, Apel Pagi"
                       class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                       required>
                <p class="mt-1 text-sm text-gray-500">Nama yang akan ditampilkan di jadwal</p>
            <!-- Shift KBM -->
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Shift KBM <span class="text-red-500">*</span>
                </label>
                <select name="shift" 
                        class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500">
                    <option value="pagi" {{ old('shift', $timeSlot->shift ?? 'pagi') == 'pagi' ? 'selected' : '' }}>Shift Pagi (Reguler)</option>
                    <option value="siang" {{ old('shift', $timeSlot->shift ?? '') == 'siang' ? 'selected' : '' }}>Shift Siang (Eksekutif)</option>
                    <option value="all" {{ old('shift', $timeSlot->shift ?? '') == 'all' ? 'selected' : '' }}>Semua Shift</option>
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
                    <option value="lesson" {{ old('slot_type', $timeSlot->slot_type) == 'lesson' ? 'selected' : '' }}>
                        Pelajaran
                    </option>
                    <option value="break" {{ old('slot_type', $timeSlot->slot_type) == 'break' ? 'selected' : '' }}>
                        Istirahat
                    </option>
                    <option value="ceremony" {{ old('slot_type', $timeSlot->slot_type) == 'ceremony' ? 'selected' : '' }}>
                        Upacara / Kegiatan
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
                       value="{{ old('slot_order', $timeSlot->slot_order) }}"
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
                           value="{{ old('start_time', substr($timeSlot->start_time, 0, 5)) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Waktu Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="time" 
                           name="end_time" 
                           value="{{ old('end_time', substr($timeSlot->end_time, 0, 5)) }}"
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
                           required>
                </div>
            </div>

            <!-- Is Teaching Slot -->
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="hidden" name="is_teaching_slot" value="0">
                    <input type="checkbox" 
                           name="is_teaching_slot" 
                           value="1"
                           id="isTeachingSlot"
                           {{ old('is_teaching_slot', $timeSlot->is_teaching_slot) == 1 ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">
                        <strong>Slot untuk Mengajar</strong>
                        <span class="block text-gray-500">Centang jika slot ini bisa digunakan untuk jadwal mengajar</span>
                    </span>
                </label>
            </div>

            <!-- Is Active -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" 
                           name="is_active" 
                           value="1"
                           {{ old('is_active', $timeSlot->is_active) == 1 ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    <span class="ml-2 text-sm text-gray-700">
                        <strong>Status Aktif</strong>
                        <span class="block text-gray-500">Nonaktifkan jika time slot tidak digunakan sementara</span>
                    </span>
                </label>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('admin.time-slots.index', ['school_id' => $timeSlot->school_id, 'day' => $timeSlot->day_of_week]) }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition">
                    <i class="fas fa-save mr-2"></i>Update Time Slot
                </button>
            </div>
        </form>
    </div>

    <!-- Usage Info -->
    @if($timeSlot->schedules()->count() > 0)
    <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <p class="text-sm text-yellow-700">
                    <strong>Perhatian:</strong> Time slot ini sedang digunakan di {{ $timeSlot->schedules()->count() }} jadwal. 
                    Perubahan waktu akan mempengaruhi jadwal yang ada.
                </p>
            </div>
        </div>
    </div>
    @endif
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

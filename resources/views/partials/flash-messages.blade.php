{{-- Flash Messages Component - Shared across all layouts --}}
@if(session('success'))
<div class="flash-message mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2" role="alert">
    <i class="fas fa-check-circle flex-shrink-0"></i>
    <span>{{ session('success') }}</span>
    <button type="button" class="ml-auto text-green-500 hover:text-green-700" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
@endif

@if(session('error'))
<div class="flash-message mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2" role="alert">
    <i class="fas fa-exclamation-circle flex-shrink-0"></i>
    <span>{{ session('error') }}</span>
    <button type="button" class="ml-auto text-red-500 hover:text-red-700" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
@endif

@if(session('warning'))
<div class="flash-message mb-4 bg-yellow-50 border border-yellow-200 text-yellow-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2" role="alert">
    <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
    <span>{{ session('warning') }}</span>
    <button type="button" class="ml-auto text-yellow-500 hover:text-yellow-700" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
@endif

@if(session('info'))
<div class="flash-message mb-4 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2" role="alert">
    <i class="fas fa-info-circle flex-shrink-0"></i>
    <span>{{ session('info') }}</span>
    <button type="button" class="ml-auto text-blue-500 hover:text-blue-700" onclick="this.parentElement.remove()" aria-label="Tutup">&times;</button>
</div>
@endif

@if(isset($errors) && $errors->any())
<div class="flash-message mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm" role="alert">
    <div class="flex items-center gap-2 mb-1">
        <i class="fas fa-exclamation-triangle flex-shrink-0"></i>
        <strong>Terdapat kesalahan:</strong>
    </div>
    <ul class="list-disc list-inside text-sm ml-5">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

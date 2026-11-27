@extends('layouts.admin')

@section('title', 'Preview Bank Soal')

@section('content')
<div class="space-y-6">
    <div class="bg-gradient-to-br from-violet-700 via-purple-700 to-indigo-800 rounded-2xl p-6 text-white">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <a href="{{ route('admin.cbt.banks') }}"
                   class="inline-flex items-center gap-2 text-violet-100 hover:text-white text-sm mb-4">
                    <i class="fas fa-arrow-left"></i> Kembali ke Bank Soal
                </a>
                <h1 class="text-2xl font-bold">{{ $bank->bank_name }}</h1>
                <p class="text-violet-100 mt-1">
                    {{ $bank->subject->name ?? 'Mata pelajaran tidak tersedia' }}
                    @if($bank->teacher && $bank->teacher->user)
                        · {{ $bank->teacher->user->name }}
                    @endif
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.cbt.banks.preview', ['bank' => $bank->id, 'show_key' => $showKey ? 0 : 1]) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm
                   {{ $showKey ? 'bg-amber-300 text-amber-950' : 'bg-white text-violet-800' }}">
                    <i class="fas {{ $showKey ? 'fa-eye-slash' : 'fa-key' }}"></i>
                    {{ $showKey ? 'Sembunyikan Kunci' : 'Tampilkan Kunci Jawaban' }}
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500">Total Soal</p>
            <p class="text-2xl font-bold text-gray-800">{{ $questions->count() }}</p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500">Soal Bermasalah</p>
            <p class="text-2xl font-bold {{ count($issues) ? 'text-red-600' : 'text-green-600' }}">
                {{ count($issues) }}
            </p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500">Soal Aktif</p>
            <p class="text-2xl font-bold text-gray-800">
                {{ $questions->where('is_active', true)->count() }}
            </p>
        </div>
        <div class="bg-white border rounded-xl p-4">
            <p class="text-xs text-gray-500">Mode</p>
            <p class="text-sm font-bold text-violet-700">Preview Admin</p>
        </div>
    </div>

    @if(count($issues))
        <div class="bg-red-50 border border-red-200 rounded-xl p-4">
            <h2 class="font-bold text-red-800 flex items-center gap-2">
                <i class="fas fa-exclamation-triangle"></i>
                Pemeriksaan Bank Soal
            </h2>
            <p class="text-sm text-red-700 mt-1">
                Ditemukan {{ count($issues) }} soal yang perlu diperiksa.
            </p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($issues as $questionId => $messages)
                    @php $number = $questions->search(fn($item) => $item->id == $questionId) + 1; @endphp
                    <a href="#question-{{ $questionId }}"
                       class="px-3 py-1.5 bg-white border border-red-200 rounded-lg text-xs text-red-700 hover:bg-red-100">
                        Soal {{ $number }}
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm">
            <i class="fas fa-check-circle mr-1"></i>
            Tidak ditemukan masalah berdasarkan pemeriksaan dasar.
        </div>
    @endif

    @if($questions->isEmpty())
        <div class="bg-white border rounded-2xl p-10 text-center">
            <i class="fas fa-folder-open text-4xl text-gray-300"></i>
            <h2 class="font-bold text-gray-700 mt-3">Belum ada soal</h2>
            <p class="text-sm text-gray-500 mt-1">Bank soal ini belum memiliki pertanyaan.</p>
        </div>
    @else
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            <aside class="lg:col-span-1">
                <div class="bg-white border rounded-2xl p-4 lg:sticky lg:top-4">
                    <h2 class="font-bold text-gray-800 mb-3">Navigasi Soal</h2>
                    <div class="grid grid-cols-5 sm:grid-cols-8 lg:grid-cols-4 gap-2">
                        @foreach($questions as $index => $question)
                            <a href="#question-{{ $question->id }}"
                               class="relative flex items-center justify-center h-10 rounded-lg border text-sm font-semibold
                               {{ isset($issues[$question->id]) ? 'bg-red-50 border-red-300 text-red-700' : 'bg-gray-50 border-gray-200 text-gray-700 hover:bg-violet-50' }}">
                                {{ $index + 1 }}
                                @if(isset($issues[$question->id]))
                                    <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-red-500 rounded-full"></span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                    <div class="mt-4 text-xs text-gray-500 space-y-1">
                        <p><span class="inline-block w-2.5 h-2.5 bg-red-500 rounded-full mr-1"></span> Perlu diperiksa</p>
                        <p><span class="inline-block w-2.5 h-2.5 bg-gray-300 rounded-full mr-1"></span> Pemeriksaan dasar lolos</p>
                    </div>
                </div>
            </aside>

            <main class="lg:col-span-3 space-y-5">
                @foreach($questions as $index => $question)
                    <article id="question-{{ $question->id }}"
                             class="bg-white border rounded-2xl overflow-hidden scroll-mt-5">
                        <div class="px-5 py-4 border-b bg-gray-50 flex flex-wrap items-center justify-between gap-2">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 rounded-xl bg-violet-100 text-violet-700 flex items-center justify-center font-bold">
                                    {{ $index + 1 }}
                                </span>
                                <div>
                                    <h3 class="font-bold text-gray-800">Soal {{ $index + 1 }}</h3>
                                    <p class="text-xs text-gray-500">
                                        {{ $question->question_type }} · {{ $question->points ?? 0 }} poin
                                    </p>
                                </div>
                            </div>
                            <span class="text-xs px-2.5 py-1 rounded-full
                                {{ $question->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                {{ $question->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="p-5 space-y-4">
                            <div class="prose max-w-none text-gray-800">
                                {!! nl2br(e($question->question_text ?? '')) !!}
                            </div>

                            @if($question->question_image_url)
                                <img src="{{ $question->question_image_url }}"
                                     alt="Gambar soal {{ $index + 1 }}"
                                     class="max-w-full max-h-96 rounded-xl border object-contain">
                            @endif

                            @if($question->question_audio_url)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2">AUDIO SOAL</p>
                                    <audio controls preload="none" class="w-full">
                                        <source src="{{ $question->question_audio_url }}">
                                        Browser tidak mendukung audio.
                                    </audio>
                                </div>
                            @endif

                            @if($question->question_video_url)
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 mb-2">VIDEO SOAL</p>
                                    <video controls preload="metadata" class="w-full max-h-96 rounded-xl bg-black">
                                        <source src="{{ $question->question_video_url }}">
                                        Browser tidak mendukung video.
                                    </video>
                                </div>
                            @endif

                            @if($question->options->isNotEmpty())
                                <div class="space-y-2">
                                    @foreach($question->options->sortBy('sort_order') as $option)
                                        <div class="flex items-start gap-3 p-3 rounded-xl border
                                            {{ $showKey && $option->is_correct ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white' }}">
                                            <span class="w-8 h-8 shrink-0 rounded-lg bg-gray-100 flex items-center justify-center font-bold text-sm text-gray-700">
                                                {{ $option->option_label }}
                                            </span>
                                            <div class="min-w-0 flex-1 text-sm text-gray-700">
                                                @if($option->option_text)
                                                    {!! nl2br(e($option->option_text)) !!}
                                                @endif
                                                @if($option->option_image_url)
                                                    <img src="{{ $option->option_image_url }}"
                                                         alt="Gambar pilihan {{ $option->option_label }}"
                                                         class="mt-2 max-w-full max-h-48 rounded-lg object-contain">
                                                @endif
                                            </div>
                                            @if($showKey && $option->is_correct)
                                                <span class="text-green-700 text-xs font-bold whitespace-nowrap">
                                                    <i class="fas fa-check-circle"></i> Benar
                                                </span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if($showKey && $question->answer_key)
                                <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                                    <p class="text-xs font-bold text-amber-800 mb-1">
                                        <i class="fas fa-key mr-1"></i> KUNCI / JAWABAN
                                    </p>
                                    <div class="text-sm text-amber-950">
                                        {!! nl2br(e($question->answer_key)) !!}
                                    </div>
                                </div>
                            @endif

                            @if(isset($issues[$question->id]))
                                <div class="rounded-xl border border-red-200 bg-red-50 p-3">
                                    <p class="text-xs font-bold text-red-800 mb-1">Perlu diperiksa:</p>
                                    <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                                        @foreach($issues[$question->id] as $issue)
                                            <li>{{ $issue }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        <div class="px-5 py-3 border-t bg-gray-50 flex justify-between">
                            @if($index > 0)
                                <a href="#question-{{ $questions[$index - 1]->id }}"
                                   class="text-sm text-violet-700 hover:underline">
                                    <i class="fas fa-arrow-up mr-1"></i> Soal Sebelumnya
                                </a>
                            @else
                                <span></span>
                            @endif

                            @if($index < $questions->count() - 1)
                                <a href="#question-{{ $questions[$index + 1]->id }}"
                                   class="text-sm text-violet-700 hover:underline">
                                    Soal Berikutnya <i class="fas fa-arrow-down ml-1"></i>
                                </a>
                            @else
                                <a href="#top"
                                   class="text-sm text-violet-700 hover:underline">
                                    Kembali ke Atas
                                </a>
                            @endif
                        </div>
                    </article>
                @endforeach
            </main>
        </div>
    @endif
</div>
@endsection

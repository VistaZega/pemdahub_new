<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tatap Muka Virtual: {{ $course->name }}</title>
    <!-- Tailwind CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- FontAwesome for Premium Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts Instrument Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Instrument Sans', sans-serif;
        }
    </style>
</head>
<body class="bg-slate-950 text-slate-100 h-screen flex flex-col overflow-hidden">

    {{-- Top Navigation Bar --}}
    <header class="bg-slate-900/90 backdrop-blur-md border-b border-slate-800 px-6 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-rose-600 rounded-xl flex items-center justify-center shadow-lg shadow-rose-900/30 animate-pulse">
                <i class="fas fa-video text-white"></i>
            </div>
            <div>
                <p class="text-rose-500 text-[10px] font-bold uppercase tracking-[0.2em] flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-rose-500 rounded-full animate-ping"></span> Live Tatap Muka
                </p>
                <h1 class="text-base font-bold text-white leading-none mt-0.5">{{ $course->course_name ?? $course->name }}</h1>
            </div>
        </div>

        <div class="flex items-center gap-3 ml-auto sm:ml-0">
            <span class="text-xs bg-slate-800 border border-slate-700 px-3 py-1.5 rounded-xl font-medium text-slate-300">
                <i class="far fa-user mr-1 text-slate-400"></i> {{ $displayName }}
            </span>

            {{-- Toggle Sidebar Button --}}
            <button onclick="toggleSidebar()" class="bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 font-bold px-3 py-1.5 rounded-xl text-xs transition-all flex items-center gap-2">
                <i class="fas fa-users text-indigo-400"></i>
                <span class="hidden sm:inline">Peserta Online</span>
            </button>
            
            {{-- End Meeting --}}
            <form action="{{ route('guru.lms.meeting.stop', $course->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengakhiri kelas tatap muka virtual ini untuk semua siswa?');">
                @csrf
                <button type="submit" class="bg-rose-600 hover:bg-rose-700 text-white font-bold px-4 py-2 rounded-xl text-xs transition-all shadow-md hover:shadow-lg flex items-center gap-2 hover:-translate-y-0.5 active:translate-y-0">
                    <i class="fas fa-phone-slash"></i> Akhiri Kelas
                </button>
            </form>
        </div>
    </header>

    {{-- Main Container --}}
    <div class="flex-1 flex flex-row overflow-hidden relative" style="height: calc(100vh - 76px);">
        {{-- Conference Area --}}
        <main id="conference-panel" class="flex-1 relative bg-slate-950" style="height: calc(100vh - 76px);">
            <div id="meet" class="w-full h-full" style="height: calc(100vh - 76px);"></div>
        </main>

        {{-- Resize Handler for Material Viewer --}}
        <div id="viewer-resize-handler" class="w-[8px] bg-slate-800/80 hover:bg-rose-500 cursor-col-resize select-none z-25 hidden relative transition-colors duration-150 group">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-5 h-12 bg-slate-900 border border-slate-700 group-hover:border-rose-500 rounded-md flex items-center justify-center cursor-col-resize shadow-lg z-30">
                <i class="fas fa-grip-lines-vertical text-[10px] text-slate-500 group-hover:text-rose-400"></i>
            </div>
        </div>

        {{-- Material Viewer Panel --}}
        <div id="material-viewer-panel" class="w-[550px] border-l border-slate-800 bg-slate-900 flex-col h-full z-10 hidden relative">
            {{-- Header of Viewer --}}
            <div class="p-3 border-b border-slate-800 flex items-center justify-between bg-slate-950/40">
                <h2 class="font-bold text-xs text-slate-200 tracking-wide flex items-center gap-1.5 min-w-0">
                    <i id="viewer-icon" class="far fa-file-alt text-rose-500"></i>
                    <span id="viewer-title" class="truncate">Viewer Materi</span>
                </h2>
                <div class="flex items-center gap-2">
                    <button id="viewer-toggle-video-btn" onclick="toggleJitsiPanel()" class="text-[10px] bg-slate-800 hover:bg-rose-600 border border-slate-700 text-rose-400 hover:text-white font-bold px-2.5 py-1 rounded-lg transition-all flex items-center gap-1">
                        <i class="fas fa-video-slash"></i> Sembunyikan Video
                    </button>
                    <a id="viewer-external-btn" href="#" target="_blank" class="text-[10px] bg-slate-800 hover:bg-rose-600 border border-slate-700 text-rose-400 hover:text-white font-bold px-2.5 py-1 rounded-lg transition-all flex items-center gap-1">
                        Tab Baru <i class="fas fa-external-link-alt text-[8px]"></i>
                    </a>
                    <button onclick="closeMaterialViewer()" class="text-slate-400 hover:text-white px-2 py-1">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            {{-- Content of Viewer --}}
            <div id="viewer-content-area" class="flex-1 flex flex-col bg-slate-950 overflow-hidden">
                {{-- iframe or text container --}}
                <iframe id="viewer-iframe" class="w-full flex-1 border-none hidden" allow="autoplay; clipboard-write; clipboard-read"></iframe>
                <div id="viewer-text-content" class="flex-1 p-6 text-slate-300 text-xs space-y-4 overflow-y-auto hidden prose prose-invert max-w-none"></div>
            </div>
        </div>

        {{-- Resize Handler for Sidebar --}}
        <div id="resize-handler" class="w-[8px] bg-slate-800/80 hover:bg-rose-500 cursor-col-resize select-none z-25 hidden relative transition-colors duration-150 group">
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-5 h-12 bg-slate-900 border border-slate-700 group-hover:border-rose-500 rounded-md flex items-center justify-center cursor-col-resize shadow-lg z-30">
                <i class="fas fa-grip-lines-vertical text-[10px] text-slate-500 group-hover:text-rose-400"></i>
            </div>
        </div>
        
        {{-- Resize Overlay (prevents iframe from swallowing mouse events during resize) --}}
        <div id="resize-overlay" class="fixed inset-0 z-30 hidden cursor-col-resize"></div>
        
        {{-- Attendees & Materials Sidebar --}}
        <aside class="w-80 border-l border-slate-800 bg-slate-900/95 flex-col h-full z-10 flex hidden">
            {{-- Tabs Header --}}
            <div class="border-b border-slate-800 flex bg-slate-950/40">
                <button id="tab-btn-peserta" onclick="switchTab('peserta')" class="flex-1 py-3 text-[10px] tracking-wider uppercase transition-all flex items-center justify-center gap-1.5 border-b-2 border-indigo-500 text-indigo-400 font-bold">
                    <i class="fas fa-users text-xs"></i> Peserta (<span id="attendee-count">0</span>)
                </button>
                <button id="tab-btn-materi" onclick="switchTab('materi')" class="flex-1 py-3 text-[10px] tracking-wider uppercase transition-all flex items-center justify-center gap-1.5 text-slate-400">
                    <i class="fas fa-book text-xs"></i> Materi
                </button>
                <button onclick="toggleSidebar()" class="px-4 text-slate-400 hover:text-white border-l border-slate-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            {{-- Tab Content: Peserta --}}
            <div id="tab-content-peserta" class="flex-1 overflow-y-auto p-4 space-y-3">
                <div id="attendees-list" class="space-y-3">
                    <!-- Fallback/Loading -->
                    <div class="text-center text-xs text-slate-500 py-8">
                        <i class="fas fa-spinner animate-spin mb-2 text-base"></i>
                        <p>Memuat daftar peserta...</p>
                    </div>
                </div>
            </div>

            {{-- Tab Content: Materi --}}
            <div id="tab-content-materi" class="flex-1 overflow-y-auto p-4 space-y-4 hidden">
                @php
                    $modules = $course->modules()->ordered()->active()->get();
                @endphp
                @if($modules->isEmpty())
                    <div class="text-center text-xs text-slate-500 py-8">
                        <i class="fas fa-info-circle text-2xl mb-2 block"></i>
                        Belum ada materi pembelajaran.
                    </div>
                @else
                    @foreach($modules as $module)
                        <div class="space-y-2">
                            <div class="bg-slate-800/60 p-2.5 rounded-xl border border-slate-800/80">
                                <h3 class="text-xs font-bold text-slate-200 flex items-center gap-1.5">
                                    <span class="w-2 h-2 bg-indigo-500 rounded-full animate-pulse"></span>
                                    {{ $module->title }}
                                </h3>
                                @if($module->description)
                                    <p class="text-[10px] text-slate-400 mt-1 line-clamp-2">{{ $module->description }}</p>
                                @endif
                            </div>
                            
                            {{-- Module Items --}}
                            <div class="pl-2 space-y-1.5">
                                {{-- Materials --}}
                                @foreach($module->materials()->active()->ordered()->get() as $material)
                                    <div class="flex items-center justify-between bg-slate-900/60 p-2 rounded-xl border border-slate-850 hover:border-indigo-500/30 transition-all duration-200 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($material->material_type == 'pdf')
                                                <i class="far fa-file-pdf text-rose-500 text-sm flex-shrink-0"></i>
                                            @elseif($material->material_type == 'video')
                                                <i class="far fa-play-circle text-amber-500 text-sm flex-shrink-0"></i>
                                            @elseif($material->material_type == 'link')
                                                <i class="fas fa-link text-indigo-400 text-sm flex-shrink-0"></i>
                                            @else
                                                <i class="far fa-file-alt text-indigo-500 text-sm flex-shrink-0"></i>
                                            @endif
                                            <span class="truncate text-[11px] text-slate-300 font-medium">{{ $material->title }}</span>
                                        </div>
                                        @php
                                            $url = '';
                                            if ($material->material_type == 'video' && $material->isYouTubeVideo()) {
                                                $url = $material->getVideoEmbedUrl();
                                            } elseif ($material->file_path) {
                                                $url = asset('storage/' . $material->file_path);
                                            } else {
                                                $url = $material->file_url;
                                            }
                                            $safeTitle = addslashes($material->title);
                                            $safeContent = $material->content ? addslashes($material->content) : '';
                                        @endphp
                                        <button onclick="showMaterialInline('{{ $safeTitle }}', '{{ $material->material_type }}', '{{ $url }}', '{{ $safeContent }}')" class="text-[10px] bg-slate-800 hover:bg-rose-600/80 border border-slate-700 text-rose-400 hover:text-white font-bold px-2 py-1 rounded-lg transition-all flex items-center gap-1 flex-shrink-0">
                                            Buka <i class="fas fa-eye text-[8px]"></i>
                                        </button>
                                    </div>
                                @endforeach

                                {{-- Assignments --}}
                                @foreach($module->assignments()->get() as $assignment)
                                    <div class="flex items-center justify-between bg-slate-900/60 p-2 rounded-xl border border-slate-850 hover:border-indigo-500/30 transition-all duration-200 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <i class="far fa-clipboard text-amber-500 text-sm flex-shrink-0"></i>
                                            <span class="truncate text-[11px] text-slate-300 font-medium">{{ $assignment->title }}</span>
                                        </div>
                                        <button onclick="showMaterialInline('{{ addslashes($assignment->title) }}', 'assignment', '{{ route('guru.lms.assignments.show', $assignment->id) }}?embed=1', '')" class="text-[10px] bg-slate-800 hover:bg-rose-600/80 border border-slate-700 text-rose-400 hover:text-white font-bold px-2 py-1 rounded-lg transition-all flex items-center gap-1 flex-shrink-0">
                                            Buka <i class="fas fa-eye text-[8px]"></i>
                                        </button>
                                    </div>
                                @endforeach

                                {{-- Quizzes --}}
                                @foreach($module->quizzes()->get() as $quiz)
                                    <div class="flex items-center justify-between bg-slate-900/60 p-2 rounded-xl border border-slate-850 hover:border-indigo-500/30 transition-all duration-200 text-xs">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <i class="far fa-question-circle text-emerald-500 text-sm flex-shrink-0"></i>
                                            <span class="truncate text-[11px] text-slate-300 font-medium">{{ $quiz->title }}</span>
                                        </div>
                                        <button onclick="showMaterialInline('{{ addslashes($quiz->title) }}', 'quiz', '{{ route('guru.lms.quizzes.show', $quiz->id) }}?embed=1', '')" class="text-[10px] bg-slate-800 hover:bg-rose-600/80 border border-slate-700 text-rose-400 hover:text-white font-bold px-2 py-1 rounded-lg transition-all flex items-center gap-1 flex-shrink-0">
                                            Buka <i class="fas fa-eye text-[8px]"></i>
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </aside>
    </div>

    {{-- Jitsi Meet External API SDK --}}
    <script src="https://{{ config('services.jitsi.domain', 'meet.jit.si') }}/external_api.js"></script>
    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('aside');
            const handler = document.getElementById('resize-handler');
            if (sidebar.classList.contains('hidden')) {
                sidebar.classList.remove('hidden');
                sidebar.classList.add('flex');
                if (handler) handler.classList.remove('hidden');
            } else {
                sidebar.classList.add('hidden');
                sidebar.classList.remove('flex');
                if (handler) handler.classList.add('hidden');
            }
        }

        function switchTab(tabName) {
            const btnPeserta = document.getElementById('tab-btn-peserta');
            const btnMateri = document.getElementById('tab-btn-materi');
            const contentPeserta = document.getElementById('tab-content-peserta');
            const contentMateri = document.getElementById('tab-content-materi');
            
            if (tabName === 'peserta') {
                btnPeserta.classList.add('border-b-2', 'border-indigo-500', 'text-indigo-400', 'font-bold');
                btnPeserta.classList.remove('text-slate-400');
                btnMateri.classList.remove('border-b-2', 'border-indigo-500', 'text-indigo-400', 'font-bold');
                btnMateri.classList.add('text-slate-400');
                
                contentPeserta.classList.remove('hidden');
                contentMateri.classList.add('hidden');
            } else {
                btnMateri.classList.add('border-b-2', 'border-indigo-500', 'text-indigo-400', 'font-bold');
                btnMateri.classList.remove('text-slate-400');
                btnPeserta.classList.remove('border-b-2', 'border-indigo-500', 'text-indigo-400', 'font-bold');
                btnPeserta.classList.add('text-slate-400');
                
                contentMateri.classList.remove('hidden');
                contentPeserta.classList.add('hidden');
            }
        }

        function toggleJitsiPanel() {
            const confPanel = document.getElementById('conference-panel');
            const viewerPanel = document.getElementById('material-viewer-panel');
            const viewerHandler = document.getElementById('viewer-resize-handler');
            const toggleBtn = document.getElementById('viewer-toggle-video-btn');
            
            if (confPanel.classList.contains('hidden')) {
                // Show Video
                confPanel.classList.remove('hidden');
                viewerPanel.style.width = viewerPanel.dataset.savedWidth || '550px';
                if (viewerHandler && !viewerPanel.classList.contains('hidden')) {
                    viewerHandler.classList.remove('hidden');
                }
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="fas fa-video-slash"></i> Sembunyikan Video';
                    toggleBtn.classList.remove('bg-rose-600', 'text-white');
                    toggleBtn.classList.add('bg-slate-800', 'text-rose-400');
                }
            } else {
                // Hide Video
                viewerPanel.dataset.savedWidth = viewerPanel.style.width || viewerPanel.offsetWidth + 'px';
                confPanel.classList.add('hidden');
                viewerPanel.style.width = '100%';
                if (viewerHandler) viewerHandler.classList.add('hidden');
                if (toggleBtn) {
                    toggleBtn.innerHTML = '<i class="fas fa-video"></i> Tampilkan Video';
                    toggleBtn.classList.remove('bg-slate-800', 'text-rose-400');
                    toggleBtn.classList.add('bg-rose-600', 'text-white');
                }
            }
        }

        function showMaterialInline(title, type, url, content) {
            const viewerPanel = document.getElementById('material-viewer-panel');
            const viewerHandler = document.getElementById('viewer-resize-handler');
            const iframe = document.getElementById('viewer-iframe');
            const textContent = document.getElementById('viewer-text-content');
            const viewerTitle = document.getElementById('viewer-title');
            const viewerIcon = document.getElementById('viewer-icon');
            const externalBtn = document.getElementById('viewer-external-btn');
            
            viewerTitle.textContent = title;
            
            if (type === 'pdf') {
                viewerIcon.className = 'far fa-file-pdf text-rose-500';
            } else if (type === 'video') {
                viewerIcon.className = 'far fa-play-circle text-amber-500';
            } else if (type === 'link') {
                viewerIcon.className = 'fas fa-link text-indigo-400';
            } else if (type === 'assignment') {
                viewerIcon.className = 'far fa-clipboard text-amber-500';
            } else if (type === 'quiz') {
                viewerIcon.className = 'far fa-question-circle text-emerald-500';
            } else {
                viewerIcon.className = 'far fa-file-alt text-indigo-500';
            }
            
            iframe.classList.add('hidden');
            textContent.classList.add('hidden');
            
            if (type === 'text') {
                textContent.innerHTML = content;
                textContent.classList.remove('hidden');
                if (externalBtn) externalBtn.classList.add('hidden');
            } else {
                iframe.src = url;
                iframe.classList.remove('hidden');
                if (externalBtn) {
                    externalBtn.href = url;
                    externalBtn.classList.remove('hidden');
                }
            }
            
            viewerPanel.classList.remove('hidden');
            viewerPanel.classList.add('flex');
            
            if (viewerHandler) {
                const confPanel = document.getElementById('conference-panel');
                if (confPanel && !confPanel.classList.contains('hidden')) {
                    viewerHandler.classList.remove('hidden');
                } else {
                    viewerHandler.classList.add('hidden');
                }
            }
        }

        function closeMaterialViewer() {
            const viewerPanel = document.getElementById('material-viewer-panel');
            const viewerHandler = document.getElementById('viewer-resize-handler');
            const iframe = document.getElementById('viewer-iframe');
            
            if (viewerPanel) {
                viewerPanel.classList.add('hidden');
                viewerPanel.classList.remove('flex');
            }
            if (viewerHandler) {
                viewerHandler.classList.add('hidden');
            }
            if (iframe) {
                iframe.src = 'about:blank';
            }
        }

        document.addEventListener("DOMContentLoaded", function () {
            const domain = "{{ config('services.jitsi.domain', 'meet.jit.si') }}";
            const options = {
                roomName: "{{ $roomName }}",
                width: "100%",
                height: "100%",
                parentNode: document.querySelector('#meet'),
                userInfo: {
                    displayName: "{{ $displayName }}"
                },
                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: false,
                    enableWelcomePage: false,
                    prejoinPageEnabled: false, // Bypass prejoin for faster joining
                    disableDeepLinking: true // Avoid app redirect prompts on mobile browsers
                },
                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'embedmeeting', 'fullscreen',
                        'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                        'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                        'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                        'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone',
                        'security'
                    ],
                    SETTINGS_SECTIONS: [ 'devices', 'language', 'profile', 'calendar' ]
                }
            };
            
            const api = new JitsiMeetExternalAPI(domain, options);
            
            // Live size diagnostics logger
            setInterval(function() {
                const meet = document.querySelector('#meet');
                const iframe = meet ? meet.querySelector('iframe') : null;
                console.log('--- DIAGNOSTICS ---');
                console.log('Meet Container Size:', meet ? `${meet.offsetWidth}px x ${meet.offsetHeight}px` : 'NOT FOUND');
                console.log('IFrame Size:', iframe ? `${iframe.offsetWidth}px x ${iframe.offsetHeight}px` : 'NOT FOUND');
                if (meet) {
                    console.log('Meet Style Display:', window.getComputedStyle(meet).display);
                    console.log('Meet Parent Height:', meet.parentElement ? `${meet.parentElement.offsetWidth}px x ${meet.parentElement.offsetHeight}px` : 'NO PARENT');
                }
            }, 3000);
            
            // Force allow attributes on iframe for WebRTC browser permissions
            const iframe = api.getIFrame();
            if (iframe) {
                iframe.setAttribute('allow', 'camera *; microphone *; display-capture *; autoplay *; clipboard-write *');
            }
            
            // Listen to hangup event to auto-submit the Laravel form ending the meeting
            api.addEventListener('videoConferenceLeft', function() {
                // Submit form to stop the meeting
                const form = document.querySelector('form');
                if (form) form.submit();
            });

            // Polling attendees list
            function fetchAttendees() {
                fetch("{{ route('guru.lms.meeting.attendees', $course->id) }}")
                    .then(response => response.json())
                    .then(data => {
                        const countEl = document.getElementById('attendee-count');
                        if (countEl) countEl.textContent = data.total;

                        const listEl = document.getElementById('attendees-list');
                        if (!listEl) return;

                        if (data.total === 0) {
                            listEl.innerHTML = `
                                <div class="text-center text-xs text-slate-500 py-8">
                                    <i class="far fa-user-circle text-2xl mb-2 block"></i>
                                    Belum ada siswa bergabung
                                </div>
                            `;
                            return;
                        }

                        let html = '';
                        data.attendees.forEach(student => {
                            html += `
                                <div class="flex items-center gap-3 bg-slate-800/40 p-2.5 rounded-xl border border-slate-800/85 hover:border-indigo-500/30 transition-all duration-200">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-[11px] font-bold text-white shadow-md flex-shrink-0">
                                        ${student.initials}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-xs font-bold text-slate-200 truncate leading-snug">${student.name}</p>
                                        <p class="text-[9px] text-indigo-400 font-semibold mt-0.5 flex items-center gap-1">
                                            <i class="far fa-clock"></i> Gabung ${student.joined_at}
                                        </p>
                                    </div>
                                </div>
                            `;
                        });
                        listEl.innerHTML = html;
                    })
                    .catch(err => console.error("Error fetching attendees:", err));
            }

            // Polling every 15 seconds
            setInterval(fetchAttendees, 15000);
            fetchAttendees(); // Run immediately

            // Auto-open sidebar on desktop when someone is online
            // Keep it toggleable

            // Drag-to-resize Sidebar and Material Viewer Logic
            const sidebar = document.querySelector('aside');
            const sidebarHandler = document.getElementById('resize-handler');
            const viewer = document.getElementById('material-viewer-panel');
            const viewerHandler = document.getElementById('viewer-resize-handler');
            const overlay = document.getElementById('resize-overlay');
            
            let isResizingSidebar = false;
            let isResizingViewer = false;
            
            const minSidebarWidth = 260;
            const maxSidebarWidth = window.innerWidth * 0.45; // Max 45% screen width
            
            const minViewerWidth = 320;
            const maxViewerWidth = window.innerWidth * 0.65; // Max 65% screen width

            // Sidebar handler events
            if (sidebarHandler) {
                sidebarHandler.addEventListener('mousedown', function(e) {
                    isResizingSidebar = true;
                    if (overlay) overlay.classList.remove('hidden');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                });
            }

            // Material Viewer handler events
            if (viewerHandler) {
                viewerHandler.addEventListener('mousedown', function(e) {
                    isResizingViewer = true;
                    if (overlay) overlay.classList.remove('hidden');
                    document.body.style.cursor = 'col-resize';
                    document.body.style.userSelect = 'none';
                });
            }

            // Unified mousemove
            document.addEventListener('mousemove', function(e) {
                if (isResizingSidebar) {
                    const newWidth = window.innerWidth - e.clientX;
                    if (newWidth >= minSidebarWidth && newWidth <= maxSidebarWidth) {
                        sidebar.style.width = newWidth + 'px';
                    }
                } else if (isResizingViewer) {
                    // Calculate relative width based on Jitsi left and Sidebar right
                    const sidebarWidth = (!sidebar.classList.contains('hidden')) ? sidebar.offsetWidth : 0;
                    const newWidth = window.innerWidth - e.clientX - sidebarWidth;
                    if (newWidth >= minViewerWidth && newWidth <= maxViewerWidth) {
                        viewer.style.width = newWidth + 'px';
                    }
                }
            });

            // Unified mouseup
            document.addEventListener('mouseup', function() {
                if (isResizingSidebar || isResizingViewer) {
                    isResizingSidebar = false;
                    isResizingViewer = false;
                    if (overlay) overlay.classList.add('hidden');
                    document.body.style.cursor = 'default';
                    document.body.style.userSelect = 'auto';
                }
            });
        });
    </script>
</body>
</html>

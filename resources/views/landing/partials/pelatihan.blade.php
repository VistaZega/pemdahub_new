{{-- PELATIHAN & DOKUMENTASI — Bento Grid Style --}}
<section id="pelatihan" class="section" style="background: var(--bg-card); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); position: relative; overflow: hidden;">
    {{-- Decorative backgrounds --}}
    <div style="position: absolute; top: -100px; left: -100px; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(79,46,209,0.03) 0%, transparent 70%); pointer-events: none;"></div>
    <div style="position: absolute; bottom: -100px; right: -100px; width: 400px; height: 400px; border-radius: 50%; background: radial-gradient(circle, rgba(245,158,11,0.03) 0%, transparent 70%); pointer-events: none;"></div>

    <div class="fw">
        <div style="text-align: center; margin-bottom: 56px;" data-aos="fade-up">
            <div class="section-label" style="justify-content: center;">
                <div class="section-label-dot" style="background: var(--gold);"></div>
                <span class="section-label-text" style="color: var(--indigo-mid);">Pusat Panduan & Pelatihan</span>
            </div>
            <h2 class="h1" style="margin-bottom: 12px;">Modul Pelatihan <span style="background: linear-gradient(135deg, var(--indigo), var(--indigo-light)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">Offline</span></h2>
            <p class="body-lg" style="max-width: 680px; margin: 0 auto;">Unduh dokumen panduan resmi sistem PembdaHub dalam format PDF untuk pembelajaran mandiri tanpa koneksi internet.</p>
        </div>

        {{-- Filter Buttons --}}
        <div style="display: flex; justify-content: center; gap: 8px; flex-wrap: wrap; margin-bottom: 40px;" data-aos="fade-up" data-aos-delay="50">
            <button class="filter-btn active" onclick="filterTraining('all', this)">Semua</button>
            <button class="filter-btn" onclick="filterTraining('panduan_umum', this)">Umum</button>
            <button class="filter-btn" onclick="filterTraining('fitur_admin', this)">Admin</button>
            <button class="filter-btn" onclick="filterTraining('fitur_guru', this)">Guru</button>
            <button class="filter-btn" onclick="filterTraining('fitur_siswa', this)">Siswa</button>
            <button class="filter-btn" onclick="filterTraining('fitur_orangtua', this)">Orang Tua</button>
            <button class="filter-btn" onclick="filterTraining('fitur_keuangan', this)">Keuangan</button>
            <button class="filter-btn" onclick="filterTraining('fitur_yayasan', this)">Yayasan</button>
        </div>

        {{-- Bento Grid for Modules --}}
        <div class="bento bento-3" id="training-grid" data-aos="fade-up" data-aos-delay="100">
            @forelse($trainingModules as $module)
                @php
                    // Map categories to modern gradient themes and icons
                    $theme = match($module->category) {
                        'panduan_umum'   => ['icon' => 'fa-compass', 'grad' => 'linear-gradient(135deg, #4f2ed1, #3b82f6)', 'bg' => '#f4f3ff', 'color' => '#4f2ed1'],
                        'fitur_admin'    => ['icon' => 'fa-user-shield', 'grad' => 'linear-gradient(135deg, #1e1b4b, #475569)', 'bg' => '#f1f5f9', 'color' => '#1e1b4b'],
                        'fitur_guru'     => ['icon' => 'fa-chalkboard-user', 'grad' => 'linear-gradient(135deg, #8b5cf6, #d946ef)', 'bg' => '#faf5ff', 'color' => '#8b5cf6'],
                        'fitur_siswa'    => ['icon' => 'fa-graduation-cap', 'grad' => 'linear-gradient(135deg, #10b981, #06b6d4)', 'bg' => '#ecfdf5', 'color' => '#10b981'],
                        'fitur_orangtua' => ['icon' => 'fa-people-roof', 'grad' => 'linear-gradient(135deg, #f43f5e, #f97316)', 'bg' => '#fff1f2', 'color' => '#f43f5e'],
                        'fitur_keuangan' => ['icon' => 'fa-wallet', 'grad' => 'linear-gradient(135deg, #f59e0b, #fbbf24)', 'bg' => '#fffbeb', 'color' => '#d97706'],
                        'fitur_yayasan'  => ['icon' => 'fa-building-columns', 'grad' => 'linear-gradient(135deg, #dc2626, #7c3aed)', 'bg' => '#fef2f2', 'color' => '#dc2626'],
                        default          => ['icon' => 'fa-book-open', 'grad' => 'linear-gradient(135deg, #6366f1, #a855f7)', 'bg' => '#eeebff', 'color' => '#6366f1'],
                    };
                @endphp
                <div class="bcard training-card hover-glow" data-category="{{ $module->category }}" style="display: flex; flex-direction: column; justify-content: space-between; min-height: 320px; transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94); transform-origin: center;">
                    {{-- Card Header & Thumbnail --}}
                    <div>
                        <div style="position: relative; width: 100%; height: 160px; border-radius: 12px; overflow: hidden; margin-bottom: 20px; background: {{ $theme['bg'] }};">
                            @if($module->thumbnail_url)
                                <img src="{{ $module->thumbnail_url }}" alt="{{ $module->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <div style="width: 100%; height: 100%; background: {{ $theme['grad'] }}; opacity: 0.1;"></div>
                                <div style="position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa-solid {{ $theme['icon'] }}" style="font-size: 4rem; color: {{ $theme['color'] }}; opacity: 0.3;"></i>
                                </div>
                            @endif
                            
                            {{-- Difficulty Badge Overlay --}}
                            @php
                                $diffBg = match($module->difficulty) {
                                    'Pemula' => 'linear-gradient(135deg, #10b981, #059669)',
                                    'Menengah' => 'linear-gradient(135deg, #3b82f6, #2563eb)',
                                    'Mahir' => 'linear-gradient(135deg, #ef4444, #dc2626)',
                                    default => 'linear-gradient(135deg, #10b981, #059669)',
                                };
                            @endphp
                            <div style="position: absolute; top: 12px; right: 12px; background: {{ $diffBg }}; color: white; padding: 4px 10px; border-radius: 8px; font-size: 11px; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                {{ $module->difficulty ?? 'Pemula' }}
                            </div>
                            
                            {{-- Icon Category Overlay --}}
                            <div style="position: absolute; bottom: 12px; left: 12px; background: rgba(255,255,255,0.9); backdrop-filter: blur(4px); color: {{ $theme['color'] }}; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
                                <i class="fa-solid {{ $theme['icon'] }}"></i>
                            </div>
                        </div>

                        {{-- Category & Reading Time --}}
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span class="feature-pill" style="background: {{ $theme['bg'] }}; color: {{ $theme['color'] }}; font-weight: 700; font-size: 11px;">
                                {{ $module->category_label }}
                            </span>
                            <span style="font-size: 11px; color: var(--text-muted); font-weight: 600; display: flex; align-items: center; gap: 4px;">
                                <i class="fa-regular fa-clock"></i> {{ $module->reading_time ?? 15 }} Menit
                            </span>
                        </div>

                        {{-- Card Body --}}
                        <h4 class="h3" style="margin-bottom: 10px; font-size: 16px; line-height: 1.4; color: var(--text-primary); display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">{{ $module->title }}</h4>
                        <p class="body" style="font-size: 13px; line-height: 1.6; color: var(--text-secondary); margin-bottom: 16px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                            {{ $module->description }}
                        </p>
                    </div>

                    {{-- Card Footer / Action --}}
                    <div style="border-top: 1px solid var(--border); padding-top: 16px; margin-top: auto; display: flex; align-items: center; justify-content: space-between;">
                        <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">
                            Target: <strong style="color: var(--text-secondary);">{{ Str::limit($module->target_roles_label, 20) }}</strong>
                        </span>
                        <a href="{{ route('training.download', $module) }}" class="btn-download" style="background: {{ $theme['grad'] }}; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 12px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); transition: all 0.2s ease;">
                            <i class="fa-solid fa-file-pdf"></i> Unduh PDF
                        </a>
                    </div>
                </div>
            @empty
                <div class="bcard" style="grid-column: span 3; text-align: center; padding: 48px;">
                    <i class="fa-regular fa-folder-open" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px;"></i>
                    <h4 class="h3">Belum ada modul pelatihan</h4>
                    <p class="body">Materi pelatihan akan segera diunggah oleh Administrator.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

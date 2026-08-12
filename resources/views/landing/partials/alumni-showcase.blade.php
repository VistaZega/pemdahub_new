@if(isset($recentAlumnis) && $recentAlumnis->count() > 0)
<style>
.alumni-section { padding: 5rem 1rem; background: linear-gradient(135deg, #f8fafc, #ffffff, #eef2ff); position: relative; overflow: hidden; }
.alumni-bg-blob1 { position: absolute; top: -10rem; right: -10rem; width: 30rem; height: 30rem; border-radius: 50%; background: #e0e7ff; filter: blur(3rem); opacity: 0.5; z-index: 0; pointer-events: none;}
.alumni-bg-blob2 { position: absolute; top: 10rem; left: -5rem; width: 20rem; height: 20rem; border-radius: 50%; background: #ede9fe; filter: blur(3rem); opacity: 0.5; z-index: 0; pointer-events: none;}
.alumni-container { max-width: 105rem; margin: 0 auto; position: relative; z-index: 10; padding: 0 2rem; }
.alumni-header-text { text-align: center; margin-bottom: 4rem; }
.alumni-subtitle { color: #4f46e5; font-weight: 600; letter-spacing: 0.05em; text-transform: uppercase; font-size: 0.875rem; display: block; margin-bottom: 0.5rem; }
.alumni-title { font-size: 2.5rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem 0; line-height: 1.2; }
.alumni-title span { background: linear-gradient(to right, #4f46e5, #7c3aed); -webkit-background-clip: text; color: transparent; }
.alumni-desc { color: #475569; max-width: 42rem; margin: 0 auto; font-size: 1.125rem; line-height: 1.7; }
.alumni-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2.5rem; }
.alumni-card { background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(12px); border: 1px solid rgba(226, 232, 240, 0.8); border-radius: 2rem; padding: 2rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.03); transition: all 0.3s ease; display: flex; flex-direction: column; position: relative; overflow: hidden; z-index: 1;}
.alumni-card:hover { transform: translateY(-8px); box-shadow: 0 20px 25px -5px rgba(79, 70, 229, 0.08), 0 8px 10px -6px rgba(79, 70, 229, 0.05); }
.alumni-quote-icon { position: absolute; top: -1.5rem; right: -1rem; font-size: 8rem; color: #eef2ff; transition: all 0.5s ease; z-index: 0; opacity: 0.7;}
.alumni-card:hover .alumni-quote-icon { color: #e0e7ff; transform: rotate(12deg) scale(1.1); }
.alumni-card-content { position: relative; z-index: 10; display: flex; flex-direction: column; height: 100%;}
.alumni-profile { display: flex; align-items: center; gap: 1.25rem; margin-bottom: 1.5rem; }
.alumni-avatar-wrapper { position: relative; flex-shrink: 0; width: 80px; height: 80px; min-width: 80px; max-width: 80px; border-radius: 50%; padding: 4px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; transition: border-color 0.3s ease;}
.alumni-card:hover .alumni-avatar-wrapper { border-color: #c7d2fe; background: #eef2ff;}
.alumni-avatar { width: 100%; height: 100%; border-radius: 50%; object-fit: cover; object-position: top; }
.alumni-badge { position: absolute; bottom: 0; right: 0; width: 24px; height: 24px; background: #fbbf24; border-radius: 50%; border: 3px solid #fff; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.15);}
.alumni-badge i { font-size: 10px; color: #fff; }
.alumni-name { font-weight: 800; font-size: 1.125rem; color: #0f172a; margin: 0; line-height: 1.3; transition: color 0.3s ease;}
.alumni-card:hover .alumni-name { color: #4f46e5; }
.alumni-school { display: inline-block; margin-top: 0.35rem; padding: 0.25rem 0.75rem; background: #f8fafc; color: #6366f1; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; border: 1px solid #e2e8f0;}
.alumni-message { color: #4338ca; font-style: italic; font-weight: 500; font-size: 1.25rem; flex-grow: 1; margin-bottom: 1.5rem; line-height: 1.6; letter-spacing: -0.01em;}
.alumni-footer { margin-top: auto; border-top: 1px solid #f1f5f9; padding-top: 1.25rem; font-size: 0.75rem; color: #64748b; line-height: 1.6;}
.alumni-footer strong { color: #334155; font-weight: 600;}
.alumni-btn-container { text-align: center; margin-top: 4rem; }
.alumni-btn { display: inline-flex; align-items: center; justify-content: center; gap: 0.75rem; background: linear-gradient(to right, #4f46e5, #7c3aed); color: #fff; font-weight: 700; padding: 1rem 2.5rem; border-radius: 9999px; box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3); transition: all 0.3s ease; text-decoration: none; font-size: 1.05rem;}
.alumni-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 25px -5px rgba(79, 70, 229, 0.4); }
.alumni-btn i { transition: transform 0.3s ease; }
.alumni-btn:hover i { transform: translateX(4px) translateY(-4px); }
@media (max-width: 768px) { .alumni-title { font-size: 2rem; } .alumni-grid { grid-template-columns: 1fr; } }
</style>

<section class="alumni-section" data-aos="fade-up">
    <!-- Dekorasi Latar Belakang -->
    <div class="alumni-bg-blob1"></div>
    <div class="alumni-bg-blob2"></div>

    <div class="alumni-container">
        
        <div class="alumni-header-text" data-aos="fade-up">
            <span class="alumni-subtitle">Suara Alumni</span>
            <h2 class="alumni-title">Rembuk Alumni <span>PEMBDA</span></h2>
            <p class="alumni-desc">
                Lintas generasi, merajut kembali kisah dan kenangan indah. Inilah cerita dan dukungan dari mereka yang pernah mengenyam pendidikan di almamater tercinta.
            </p>
        </div>

        <div class="alumni-grid">
            @foreach($recentAlumnis as $alumni)
            <div class="alumni-card" data-aos="fade-up" data-aos-delay="{{ $loop->iteration * 100 }}">
                
                <i class="fa-solid fa-quote-right alumni-quote-icon"></i>
                
                <div class="alumni-card-content">
                    <div class="alumni-profile">
                        <div class="alumni-avatar-wrapper">
                            <img src="{{ $alumni->photo_url }}" class="alumni-avatar" alt="{{ $alumni->full_name }}">
                            <div class="alumni-badge">
                                <i class="fa-solid fa-check"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="alumni-name">{{ $alumni->full_name }}</h4>
                            <div class="alumni-school">
                                Lulusan {{ $alumni->school->name ?? 'PEMBDA' }} '{{ $alumni->graduation_year }}
                            </div>
                        </div>
                    </div>
                    
                    @php
                        $alias = $alumni->alias_name ? "dikenal <strong>{$alumni->alias_name}</strong>, " : "";
                        $anak = $alumni->children_count ? "dengan {$alumni->children_count} anak, " : "";
                        $status = $alumni->marital_status ? "berstatus {$alumni->marital_status}, " : "";
                        
                        $kerja = "";
                        if($alumni->occupation && $alumni->company_name) {
                            $kerja = "bekerja sebagai {$alumni->occupation} di {$alumni->company_name}";
                        } elseif($alumni->occupation) {
                            $kerja = "bekerja sebagai {$alumni->occupation}";
                        } elseif($alumni->company_name) {
                            $kerja = "bekerja di {$alumni->company_name}";
                        }
                        
                        $narrative = trim("{$alias} {$status} {$anak} {$kerja}", ", ");
                    @endphp
                    
                    <div class="alumni-message">
                        "{{ $alumni->message }}"
                    </div>
                    
                    @if($narrative)
                    <div class="alumni-footer">
                        <strong>{{ $alumni->full_name }}</strong> {!! $narrative !!}.
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        
        <div class="alumni-btn-container" data-aos="fade-up">
            <a href="{{ route('ika.register') }}" class="alumni-btn">
                <i class="fa-solid fa-paper-plane"></i> Ikut Rembuk Alumni Sekarang
            </a>
        </div>
        
    </div>
</section>
@endif

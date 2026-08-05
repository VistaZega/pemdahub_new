@if(isset($item))
@php
    $catLower = strtolower($item->category_label ?? '');
    if (str_contains($catLower, 'prestasi') || str_contains($catLower, 'penghargaan') || str_contains($catLower, 'juara')) {
        $pillBg = 'var(--emerald-bg)';
        $pillColor = 'var(--emerald)';
    } elseif (str_contains($catLower, 'kegiatan') || str_contains($catLower, 'acara') || str_contains($catLower, 'lomba')) {
        $pillBg = 'var(--blue-bg)';
        $pillColor = 'var(--blue)';
    } elseif (str_contains($catLower, 'kerjasama') || str_contains($catLower, 'mitra') || str_contains($catLower, 'mou')) {
        $pillBg = 'var(--violet-bg)';
        $pillColor = 'var(--violet)';
    } else {
        $pillBg = 'var(--amber-bg)';
        $pillColor = 'var(--amber)';
    }
@endphp
<div class="bcard shimmer-card" style="padding:0; overflow:hidden; border: 1px solid rgba(244,63,94,0.15);">
    <div style="height: 140px; position:relative; background:linear-gradient(135deg, {{ $item->gradient_from ?? '#3b82f6' }}, {{ $item->gradient_to ?? '#1d4ed8' }}); display:flex; align-items:center; justify-content:center;">
        @if(!empty($item->image))
            <img src="{{ $item->image_url }}" alt="{{ $item->title }}" style="width:100%; height:100%; object-fit:cover;">
            <div style="position:absolute; inset:0; background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.4));"></div>
        @else
            <i class="{{ $item->icon ?? 'fa-solid fa-newspaper' }}" style="font-size:48px; color:rgba(255,255,255,0.15);"></i>
        @endif
        <span class="feature-pill" style="position:absolute; top:12px; left:12px; background:{{ $pillBg }}; color:{{ $pillColor }}; font-weight: 700; box-shadow: 0 4px 10px rgba(0,0,0,0.1);">
            <i class="fa-solid fa-newspaper"></i> Info: {{ $item->category_label ?? 'Terbaru' }}
        </span>
    </div>
    <div style="padding:20px;">
        <div style="font-size:12px; color:var(--text-muted); margin-bottom:8px; font-weight: 600;">
            <i class="fa-regular fa-calendar" style="margin-right:4px;"></i> {{ $item->formatted_date ?? date('d M Y') }}
        </div>
        <h4 class="h3" style="margin-bottom:8px; font-size:15px; line-height:1.4; color: var(--text-primary);">{{ $item->title }}</h4>
        <p class="body" style="font-size:12px; line-height: 1.6; margin-bottom: 0;">{{ Str::limit($item->excerpt ?? $item->content ?? '', 90) }}</p>
    </div>
</div>
@endif

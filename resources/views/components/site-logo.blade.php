@props(['size' => 28, 'showText' => true, 'textStyle' => '', 'textClass' => ''])

@php
    $settings  = app(\App\Services\SettingsService::class);
    $logoUrl   = $settings->get('site_logo_url', '');
    $siteName  = $settings->get('site_name', 'NextTrade');
    $radius    = round($size * 0.29);
    $iconSize  = round($size * 0.54);
    $fontSize  = $size >= 36 ? 18 : ($size >= 28 ? 13 : 11);
@endphp

@if($logoUrl)
    <img src="{{ $logoUrl }}"
         alt="{{ $siteName }}"
         width="{{ $size }}"
         height="{{ $size }}"
         style="width:{{ $size }}px;height:{{ $size }}px;object-fit:contain;border-radius:{{ $radius }}px;flex-shrink:0;"
         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
    {{-- fallback icon shown if image fails --}}
    <div style="display:none;width:{{ $size }}px;height:{{ $size }}px;border-radius:{{ $radius }}px;background:#06b6d4;align-items:center;justify-content:center;flex-shrink:0;">
        <svg style="width:{{ $iconSize }}px;height:{{ $iconSize }}px;" fill="none" stroke="white" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
    </div>
@else
    <div style="width:{{ $size }}px;height:{{ $size }}px;border-radius:{{ $radius }}px;background:#06b6d4;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
        <svg style="width:{{ $iconSize }}px;height:{{ $iconSize }}px;" fill="none" stroke="white" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
    </div>
@endif

@if($showText)
    <span class="{{ $textClass }}" style="font-size:{{ $fontSize }}px;font-weight:700;color:white;white-space:nowrap;{{ $textStyle }}">
        {{ $siteName }}
    </span>
@endif

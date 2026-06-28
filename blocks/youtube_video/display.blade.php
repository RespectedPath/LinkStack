<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

@php
    $sp = json_decode($link->type_params ?? '{}', true);
    if (!is_array($sp)) { $sp = []; }
    $videoId = (string) ($sp['video_id'] ?? '');
    $privacy = (bool) ($sp['privacy_mode'] ?? true);
    $host = $privacy ? 'www.youtube-nocookie.com' : 'www.youtube.com';
@endphp

@if($videoId !== '' && preg_match('/^[A-Za-z0-9_-]{11}$/', $videoId))
    <div class="button-entrance youtube-block-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="youtube-video-{{ $link->id }}">
        @if(!empty($link->title))
            <h3 class="yt-heading">{{ $link->title }}</h3>
        @endif
        <div class="yt-frame-wrap">
            <iframe class="yt-frame"
                src="https://{{ $host }}/embed/{{ $videoId }}"
                title="{{ $link->title ?: 'YouTube video' }}"
                loading="lazy"
                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                referrerpolicy="strict-origin-when-cross-origin"
                allowfullscreen></iframe>
        </div>
    </div>
@endif

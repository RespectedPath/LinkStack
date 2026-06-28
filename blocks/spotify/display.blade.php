<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

@php
    $sp = json_decode($link->type_params ?? '{}', true);
    if (!is_array($sp)) { $sp = []; }
    $type = (string) ($sp['content_type'] ?? '');
    $id   = (string) ($sp['content_id'] ?? '');

    // Default player heights by content type. Spotify's official
    // sizes — tracks and episodes use the compact 152px (just art +
    // play button); everything with a list (album / playlist / show /
    // artist top tracks) gets the taller 352px so the list is visible.
    $heightByType = [
        'track'    => 152,
        'episode'  => 152,
        'album'    => 352,
        'playlist' => 352,
        'show'     => 352,
        'artist'   => 352,
    ];
    $height = $heightByType[$type] ?? 352;

    $allowedTypes = ['track','album','playlist','artist','show','episode'];
@endphp

@if(in_array($type, $allowedTypes, true) && preg_match('/^[A-Za-z0-9]{22}$/', $id))
    <div class="button-entrance spotify-block-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="spotify-{{ $link->id }}">
        @if(!empty($link->title))
            <h3 class="sp-heading">{{ $link->title }}</h3>
        @endif
        <iframe class="sp-frame"
            src="https://open.spotify.com/embed/{{ $type }}/{{ $id }}?utm_source=generator"
            style="height: {{ $height }}px;"
            title="{{ $link->title ?: 'Spotify ' . $type }}"
            frameborder="0"
            loading="lazy"
            allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"
            referrerpolicy="strict-origin-when-cross-origin"
            allowfullscreen></iframe>
    </div>
@endif

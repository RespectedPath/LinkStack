<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

@php
    $sp = json_decode($link->type_params ?? '{}', true);
    if (!is_array($sp)) { $sp = []; }
    $channel = (string) ($sp['channel_name'] ?? '');

    // Twitch's player REQUIRES a `parent` param matching the host the
    // iframe is loaded from — otherwise it refuses to play. Read from
    // the current request so the same code works on localhost, a
    // staging domain, and production without any config.
    $parent = request()->getHost() ?: 'localhost';
@endphp

@if($channel !== '' && preg_match('/^[a-z0-9_]{4,25}$/', $channel))
    <div class="button-entrance twitch-block-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="twitch-channel-{{ $link->id }}">
        @if(!empty($link->title))
            <h3 class="tw-heading">{{ $link->title }}</h3>
        @endif
        <div class="tw-frame-wrap">
            <iframe class="tw-frame"
                src="https://player.twitch.tv/?channel={{ urlencode($channel) }}&parent={{ urlencode($parent) }}&autoplay=false&muted=false"
                title="{{ $link->title ?: 'Twitch player for ' . $channel }}"
                loading="lazy"
                allowfullscreen
                referrerpolicy="strict-origin-when-cross-origin"></iframe>
        </div>
    </div>
@endif

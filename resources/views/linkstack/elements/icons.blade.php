<?php use App\Models\UserData; ?>

        @php
            // Social icons are drawn from the render's $links (the
            // button-94 "icon" rows), NOT a fresh DB query — so
            // draft/publish is honored: the public page shows the
            // PUBLISHED social row, not the live draft. Same ordering as
            // before: `order`, then `id` as a tiebreaker.
            $icons = collect($links ?? [])
                ->filter(fn ($l) => (($l->button_id ?? null) == 94) || (($l->name ?? null) === 'icon'))
                ->sortBy(fn ($l) => sprintf('%011d%011d', (int) ($l->order ?? 0), (int) ($l->id ?? 0)))
                ->values();
        @endphp
        @if(count($icons) > 0)
        <div class="row fadein social-icon-div">
        @foreach($icons as $icon)
        <a class="social-hover social-link" href="{{ mm_safe_href($icon->link) }}" title="{{ucfirst($icon->title)}}" aria-label="{{ucfirst($icon->title)}}" @if((UserData::getData($userinfo->id, 'links-new-tab') != false))target="_blank"@endif><i id="{{ $icon->id }}" class="button-click dynamic-contrast social-icon fa-brands fa-{{$icon->title}}"></i></a>
        @endforeach
        </div>
        @endif
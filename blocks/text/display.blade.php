{{-- Text block — wrapped in .mm-text-block so we can control the
     vertical rhythm. CKEditor-saved content tends to be wrapped in
     <p> tags, which inherit the browser's default 1em top/bottom
     margin and cause asymmetric whitespace at the start and end of
     the block. .mm-text-block zeros those margins so the text sits
     flush in its slot. Alignment comes from a per-instance setting
     stored in type_params; default is center.
     UI-PASS-PLAN.md Pass 1, item 8. --}}
@php
    // type_params merge in UserController sets $link->alignment on
    // the link object at render time; default to center for blocks
    // saved before this control existed.
    $align = $link->alignment ?? 'center';
    if (!in_array($align, ['left', 'center', 'right'], true)) {
        $align = 'center';
    }
@endphp
<div class="fadein mm-text-block" style="text-align: {{ $align }};" id="text-block-{{ $link->id }}">
    {!! block_appearance_style($link, ['id' => 'text-block-' . $link->id, 'text' => ['', ' p', ' h1', ' h2', ' h3', ' a']]) !!}
    @if(env('ALLOW_USER_HTML') === true)
        {!! $link->title !!}
    @else
        {{ $link->title }}
    @endif
</div>
<style>
    .mm-text-block {
        padding: 6px 0;
        line-height: 1.5;
    }
    /* Zero the default <p> margins (CKEditor wraps content in <p>)
       so the text starts and ends flush in its visual slot. The
       gap to neighbouring blocks is owned by the wrapper / page
       layout, not the inner paragraphs. */
    .mm-text-block > p,
    .mm-text-block > p:first-child,
    .mm-text-block > p:last-child {
        margin-block-start: 0;
        margin-block-end: 0;
    }
    /* Restore spacing BETWEEN paragraphs in a multi-paragraph text
       block — just not at the outer edges. */
    .mm-text-block > p + p {
        margin-top: 0.6em;
    }
</style>

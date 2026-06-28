<?php
    $savedType  = $content_type ?? '';
    $savedId    = $content_id ?? '';
    $savedInput = ($savedType && $savedId)
        ? 'https://open.spotify.com/' . $savedType . '/' . $savedId
        : ($link ?? '');
?>

{{-- LinkStack expects a hidden `button` select on every block form for legacy reasons. --}}
<select style="display:none" name="button" class="form-control">
    <option class="button button-default spotify" value="default spotify">Spotify</option>
</select>

<label for='title' class='form-label'>Section heading <span class='text-muted'>(optional)</span></label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Listen to my new EP" />
<span class='small text-muted'>Shown above the player. Leave blank for just the embed.</span><br>

<label for='spotify_url' class='form-label'>Spotify share link</label>
<input type='text' name='spotify_url' id='spotify_url' value='{{ $savedInput }}' class='form-control' maxlength="500" required placeholder="https://open.spotify.com/album/…" />
<span class='small text-muted'>
    Open a track / album / playlist / artist / podcast / episode in Spotify, click
    <strong>Share &rarr; Copy link</strong>, and paste it here. All six content types embed automatically.
</span>

@include('studio.partials.block-collapsed-toggle')

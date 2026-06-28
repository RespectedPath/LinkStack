<?php
    // When editing an existing block, LinkTypeViewController::getParamForm
    // merges type_params keys into the view scope — so $video_id and
    // $privacy_mode are populated from the saved JSON.
    $savedVideoId = $video_id ?? '';
    $savedUrl     = $savedVideoId ? ('https://youtube.com/watch?v=' . $savedVideoId) : ($link ?? '');
    $savedPrivacy = isset($privacy_mode) ? (bool) $privacy_mode : true; // default ON for new blocks
?>

{{-- LinkStack expects a hidden `button` select on every block form for legacy reasons. --}}
<select style="display:none" name="button" class="form-control">
    <option class="button button-default youtube_video" value="default youtube_video">YouTube video</option>
</select>

<label for='title' class='form-label'>Section heading <span class='text-muted'>(optional)</span></label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Watch my latest demo" />
<span class='small text-muted'>Shown above the video. Leave blank for just the player.</span><br>

<label for='video_url' class='form-label'>YouTube URL</label>
<input type='text' name='video_url' id='video_url' value='{{ $savedUrl }}' class='form-control' maxlength="500" required placeholder="https://youtu.be/dQw4w9WgXcQ" />
<span class='small text-muted'>
    Paste any YouTube URL &mdash;
    <code>youtube.com/watch?v=…</code>,
    <code>youtu.be/…</code>,
    <code>youtube.com/shorts/…</code>,
    or a bare 11-character video ID. All work.
</span><br>

<div class="form-check form-switch mt-3">
    {{-- Hidden default so the field always submits, even unchecked. --}}
    <input type="hidden" name="privacy_mode" value="0">
    <input class="form-check-input" type="checkbox" id="privacy_mode" name="privacy_mode" value="1" @if($savedPrivacy) checked @endif>
    <label class="form-check-label" for="privacy_mode">
        Privacy mode <span class="text-muted small">(recommended)</span>
    </label>
    <div class="text-muted small mt-1">
        Loads the video from <code>youtube-nocookie.com</code> so YouTube doesn't drop tracking cookies on visitors until they hit play.
    </div>
</div>

@include('studio.partials.block-collapsed-toggle')

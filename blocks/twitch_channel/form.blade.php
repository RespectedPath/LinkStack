<?php
    // LinkTypeViewController::getParamForm merges type_params into the
    // view scope on edit. $channel_name is what we stored.
    $savedChannel = $channel_name ?? '';
    $savedInput   = $savedChannel ?: ($link ?? '');
?>

{{-- LinkStack expects a hidden `button` select on every block form for legacy reasons. --}}
<select style="display:none" name="button" class="form-control">
    <option class="button button-default twitch_channel" value="default twitch_channel">Twitch channel</option>
</select>

<label for='title' class='form-label'>Section heading <span class='text-muted'>(optional)</span></label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Watch me live" />
<span class='small text-muted'>Shown above the player. Leave blank for just the embed.</span><br>

<label for='channel' class='form-label'>Twitch channel</label>
<input type='text' name='channel' id='channel' value='{{ $savedInput }}' class='form-control' maxlength="255" required placeholder="jameskoch" />
<span class='small text-muted'>
    Paste your Twitch URL (<code>twitch.tv/yourname</code>) or just the channel name.
    The player shows your live stream when you're broadcasting, or your most recent video when you're offline.
</span>

@include('studio.partials.block-collapsed-toggle')

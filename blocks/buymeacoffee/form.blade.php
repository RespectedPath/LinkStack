<?php
    // LinkTypeViewController::getParamForm merges type_params into the
    // view scope on edit, so $bmc_username / $button_label arrive
    // populated when editing an existing block.
    $savedUsername = $bmc_username ?? '';
    $savedLabel    = $button_label ?? '';
    $savedInput    = $savedUsername ?: ($link ?? '');
?>

{{-- LinkStack expects a hidden `button` select on every block form for legacy reasons. --}}
<select style="display:none" name="button" class="form-control">
    <option class="button button-default buymeacoffee" value="default buymeacoffee">Buy Me a Coffee</option>
</select>

<label for='title' class='form-label'>Section heading <span class='text-muted'>(optional)</span></label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Enjoy what I do?" />
<span class='small text-muted'>Shown above the button. Leave blank for just the button.</span><br>

<label for='bmc_username' class='form-label'>Buy Me a Coffee username</label>
<input type='text' name='bmc_username' id='bmc_username' value='{{ $savedInput }}' class='form-control' maxlength="255" required placeholder="jameskoch" />
<span class='small text-muted'>
    Paste your BMC URL (<code>buymeacoffee.com/yourname</code>, <code>bmc.link/yourname</code>, <code>coff.ee/yourname</code>) or just the username.
</span><br>

<label for='button_label' class='form-label'>Button label <span class='text-muted'>(optional)</span></label>
<input type='text' name='button_label' value='{{ $savedLabel }}' class='form-control' maxlength="60" placeholder="Buy me a coffee" />
<span class='small text-muted'>Defaults to "Buy me a coffee" if blank.</span>

@include('studio.partials.block-collapsed-toggle')

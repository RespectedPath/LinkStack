<select style="display:none" name="button" class="form-control"><option class="button button-default newsletter_signup" value="default newsletter_signup">Newsletter signup</option></select>

<label for='title' class='form-label'>Section heading</label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Join my newsletter" required />
<span class='small text-muted'>Text shown above the signup form on your public page</span><br>

<label for='link' class='form-label'>Button label</label>
<input type='text' name='link' value='{{ $link ?? '' }}' class='form-control' maxlength="50" placeholder="Subscribe" required />
<span class='small text-muted'>Label for the signup button</span><br>

<label for='api_key' class='form-label'>Mailchimp API key</label>
<input type='password' name='api_key' value='{{ $api_key ?? '' }}' class='form-control' maxlength="255" required autocomplete="off" />
<span class='small text-muted'>Mailchimp &rarr; Account &rarr; Extras &rarr; API keys. Must end in a data-center suffix like <code>-us1</code>.</span><br>

<label for='list_id' class='form-label'>Audience List ID</label>
<input type='text' name='list_id' value='{{ $list_id ?? '' }}' class='form-control' maxlength="50" required autocomplete="off" pattern="[a-zA-Z0-9]+" />
<span class='small text-muted'>Mailchimp &rarr; Audience &rarr; Settings &rarr; "Audience name and defaults" &rarr; Audience ID.</span>

@include('studio.partials.block-collapsed-toggle')

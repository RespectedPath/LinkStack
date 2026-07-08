<select style="display:none" name="button" class="form-control"><option class="button button-default contact_form" value="default contact_form">Contact form</option></select>

<label for='title' class='form-label'>Section heading</label>
<input type='text' name='title' value='{{ $title ?? '' }}' class='form-control' maxlength="100" placeholder="Contact me" required />
<span class='small text-muted'>Text shown above the form on your public page</span><br>

<label for='link' class='form-label'>Destination e-mail address</label>
<input type='email' name='link' value='{{ $link ?? '' }}' class='form-control' maxlength="255" required />
<span class='small text-muted'>Submissions are delivered to this address</span><br>

<label for='subject' class='form-label'>Custom e-mail subject <span class='text-muted'>(optional)</span></label>
<input type='text' name='subject' value='{{ $subject ?? '' }}' class='form-control' maxlength="150" placeholder="New message from your Mail Minted contact form" />
<span class='small text-muted'>Leave blank to use the default subject line</span>

@include('studio.partials.block-collapsed-toggle')

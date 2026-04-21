<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

<div class="button-entrance contact-form-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="contact-form-{{ $link->id }}">
    <h3 class="cf-heading">{{ $link->title }}</h3>

    @if(session('contact_form_success') === (int) $link->id)
        <div class="cf-banner cf-success" role="status">
            Thanks! Your message has been sent.
        </div>
    @endif

    @if(session('contact_form_error') === (int) $link->id)
        <div class="cf-banner cf-error" role="alert">
            Something went wrong. Please try again in a moment.
        </div>
    @endif

    <form class="cf-form" method="POST" action="{{ route('contactFormSubmit', ['id' => $link->id]) }}#contact-form-{{ $link->id }}" novalidate>
        @csrf

        {{-- Honeypot: inline-hidden so bots still see it but humans never do, even if styles.css fails to load --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="" aria-hidden="true" style="position:absolute !important;left:-10000px !important;top:-10000px !important;width:1px !important;height:1px !important;opacity:0 !important;pointer-events:none !important;">

        <label for="cf-name-{{ $link->id }}" class="cf-label">Full name</label>
        <input type="text" id="cf-name-{{ $link->id }}" name="name" class="cf-input @error('name') cf-input-error @enderror" value="{{ old('name') }}" maxlength="100" required autocomplete="name">
        @error('name') <span class="cf-field-error">{{ $message }}</span> @enderror

        <label for="cf-email-{{ $link->id }}" class="cf-label">E-mail address</label>
        <input type="email" id="cf-email-{{ $link->id }}" name="email" class="cf-input @error('email') cf-input-error @enderror" value="{{ old('email') }}" maxlength="255" required autocomplete="email">
        @error('email') <span class="cf-field-error">{{ $message }}</span> @enderror

        <label for="cf-message-{{ $link->id }}" class="cf-label">Message</label>
        <textarea id="cf-message-{{ $link->id }}" name="message" class="cf-input cf-textarea @error('message') cf-input-error @enderror" maxlength="5000" required rows="5">{{ old('message') }}</textarea>
        @error('message') <span class="cf-field-error">{{ $message }}</span> @enderror

        <button type="submit" class="button button-default cf-submit">Send message</button>
    </form>
</div>

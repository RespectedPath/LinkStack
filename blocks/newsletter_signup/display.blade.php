<link rel="stylesheet" href="{{ block_asset('styles.css') }}">

{{--
    SECURITY: This template deliberately never reads $link->type_params.
    The Mailchimp API key and List ID live there and must stay
    server-side. Only $link->id, $link->title, and $link->link (the CTA
    button label) are rendered. Do not add anything that decodes
    type_params here — keep the server/client boundary intact.
--}}
<div class="button-entrance newsletter-wrapper" style="--delay: {{ $initial ?? 1 }}s" id="newsletter-signup-{{ $link->id }}">
    {!! block_appearance_style($link, ['id' => 'newsletter-signup-' . $link->id, 'button' => ['.ns-submit'], 'heading' => ['.ns-heading'], 'summary_id' => 'block-' . $link->id]) !!}
    @include('blocks::partials.block-heading', ['link' => $link, 'headingClass' => 'ns-heading'])

    @if(session('newsletter_success') === (int) $link->id)
        <div class="ns-banner ns-success" role="status">
            You're all set! Thanks for subscribing.
        </div>
    @endif

    @if(session('newsletter_already') === (int) $link->id)
        <div class="ns-banner ns-info" role="status">
            You're already subscribed — thanks for sticking with us.
        </div>
    @endif

    @if(session('newsletter_pending') === (int) $link->id)
        <div class="ns-banner ns-info" role="status">
            Almost there — check your inbox for a confirmation e-mail.
        </div>
    @endif

    @if(session('newsletter_previously_unsubscribed') === (int) $link->id)
        <div class="ns-banner ns-warn" role="alert">
            Looks like you previously unsubscribed. Please contact the page owner directly if you'd like to rejoin.
        </div>
    @endif

    @if(session('newsletter_error') === (int) $link->id)
        <div class="ns-banner ns-error" role="alert">
            Something went wrong. Please try again in a moment.
        </div>
    @endif

    <form class="ns-form" method="POST" action="{{ route('newsletterSubscribe', ['id' => $link->id]) }}#newsletter-signup-{{ $link->id }}" novalidate>
        @csrf

        {{-- Honeypot: inline-hidden so bots still fill it but humans never see it even if styles.css fails --}}
        <input type="text" name="website" tabindex="-1" autocomplete="off" value="" aria-hidden="true" style="position:absolute !important;left:-10000px !important;top:-10000px !important;width:1px !important;height:1px !important;opacity:0 !important;pointer-events:none !important;">

        <div class="ns-name-row">
            <div class="ns-field">
                <label for="ns-first-{{ $link->id }}" class="ns-label">First name</label>
                <input type="text" id="ns-first-{{ $link->id }}" name="first_name" class="ns-input @error('first_name') ns-input-error @enderror" value="{{ old('first_name') }}" maxlength="50" required autocomplete="given-name">
                @error('first_name') <span class="ns-field-error">{{ $message }}</span> @enderror
            </div>
            <div class="ns-field">
                <label for="ns-last-{{ $link->id }}" class="ns-label">Last name</label>
                <input type="text" id="ns-last-{{ $link->id }}" name="last_name" class="ns-input @error('last_name') ns-input-error @enderror" value="{{ old('last_name') }}" maxlength="50" required autocomplete="family-name">
                @error('last_name') <span class="ns-field-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <label for="ns-email-{{ $link->id }}" class="ns-label">E-mail address</label>
        <input type="email" id="ns-email-{{ $link->id }}" name="email" class="ns-input @error('email') ns-input-error @enderror" value="{{ old('email') }}" maxlength="255" required autocomplete="email">
        @error('email') <span class="ns-field-error">{{ $message }}</span> @enderror

        <button type="submit" class="button button-default ns-submit">{{ $link->link ?: 'Subscribe' }}</button>
    </form>
</div>

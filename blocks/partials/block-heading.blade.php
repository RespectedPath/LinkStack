{{--
    Shared heading for rich blocks (contact_form, newsletter_signup,
    stripe_payment, …). Renders the block title with the optional picker
    icon (custom_icon), so the icon behaves IDENTICALLY on every block —
    change it here once and every block updates.

    Params:
      $link         the Link model
      $headingClass the block's own heading class (cf-heading / ns-heading /
                    sp-heading) so per-block CSS + block_appearance_style()
                    keep targeting it. The icon sits inside the <h3>, so it
                    inherits the heading's colour and size automatically.
--}}
@php $__blockIcon = trim((string) ($link->custom_icon ?? '')); @endphp
<h3 class="{{ $headingClass }}">@if($__blockIcon)<i class="{{ $__blockIcon }}" style="margin-right:0.45em;" aria-hidden="true"></i>@endif{{ $link->title }}</h3>

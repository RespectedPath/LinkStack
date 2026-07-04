<div class="container">
	<div class="footer fadein" style="margin:5% 0px 35px 0px;">
	@if(env('DISPLAY_FOOTER') === true)
		@if(env('DISPLAY_FOOTER_HOME') === true)<a class="footer-hover spacing" href="@if(str_replace('"', "", EnvEditor::getKey('HOME_FOOTER_LINK')) === "" ){{ url('') }}@else{{ str_replace('"', "", EnvEditor::getKey('HOME_FOOTER_LINK')) }}@endif">{{footer('Home')}}</a>@endif
		@if(env('DISPLAY_FOOTER_TERMS') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Terms')) }}">{{footer('Terms')}}</a>@endif
		@if(env('DISPLAY_FOOTER_PRIVACY') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Privacy')) }}">{{footer('Privacy')}}</a>@endif
		@if(env('DISPLAY_FOOTER_CONTACT') === true)<a class="footer-hover spacing" href="{{ url('') }}/pages/{{ strtolower(footer('Contact')) }}">{{footer('Contact')}}</a>@endif
	@endif
	</div>

	@if(env('DISPLAY_CREDIT') === true)
	{{-- Removed class spacing --}}
	<a style="text-decoration: none;" class="" href="https://linkstack.org" target="_blank" title="{{__('messages.Learn more about LinkStack')}}">
		<div style="vertical-align: middle;display: inline-block;padding-bottom:50px;" class="credit-hover hvr-grow fadein">
			<img style="width:200px" class="" src="{{ asset('assets/linkstack/images/powered-by-linkstack.svg') }}" alt="LinkStack">
		</div>
	</a>
	@endif

	{{-- AGPL v3 source-availability disclosure. LinkStack (and this
	     fork) is licensed under AGPL v3; when the service is run for
	     users over a network, the license requires the modified source
	     be reachable. A quiet plaintext link satisfies that without
	     the "Powered by" branding badge — the whitelabel product
	     (Mail Minted) stays clean while remaining license-compliant.
	     Change the URL if the fork ever moves. --}}
	<div class="fadein" style="font-size: 0.7rem; opacity: 0.5; padding-bottom: 24px;">
		<a href="https://github.com/RespectedPath/LinkStack" target="_blank" rel="noopener noreferrer"
		   style="color: inherit; text-decoration: none;">Source</a>
	</div>
	</div>
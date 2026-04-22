@extends('linkstack.layout')

@section('content')
    @push('linkstack-head')
        @include('linkstack.modules.meta')
        @include('linkstack.modules.assets')
        {{-- Google Analytics: platform-wide tracker + per-user tracker, each only renders if set --}}
        @include('linkstack.modules.google-analytics', ['trackingId' => env('GOOGLE_ANALYTICS_TRACKING_ID')])
        @include('linkstack.modules.google-analytics', ['trackingId' => $userinfo->google_analytics_id ?? null])
    @endpush

    @push('linkstack-head-end')
        @foreach($information as $info)
            @include('linkstack.modules.theme')
        @endforeach
        {{-- User Appearance customization overrides — renders AFTER
             theme CSS so its !important rules cascade and win. Skipped
             entirely when the user hasn't customized anything. --}}
        @include('linkstack.modules.appearance-override', ['userinfo' => $userinfo])
    @endpush

    @push('linkstack-body-start')
        @include('linkstack.modules.admin-bar')
        @include('linkstack.modules.share-button')
        @include('linkstack.modules.report-icon')
    @endpush

    @push('linkstack-content')
        @foreach($information as $info)
            @include('linkstack.elements.avatar')
            @include('linkstack.elements.heading')
            @include('linkstack.elements.bio')
        @endforeach
        @include('linkstack.elements.icons')
        @include('linkstack.elements.buttons')
        @yield('content')
        @include('linkstack.modules.footer')
    @endpush
@endsection
@extends('layouts.sidebar')

@section('content')

<div class="container-fluid content-inner mt-n5 py-0">
  <div class="row">
    <div class="col-12">
      <div class="card rounded">
        <div class="card-body">

          @if(session()->has('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger">
              <strong>Couldn't save:</strong>
              <ul class="mb-0 mt-1">
                @foreach($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <section class='text-gray-400'>
            <h3 class="mb-4 card-header"><i class="fa-solid fa-icons"></i> {{__('messages.Page Icons')}}</h3>
            <p class="text-muted">
              Small brand icons rendered as a row near the top of your public page.
              For brands not listed below, use <a href="{{ url('/studio/add-link') }}">Add link &rarr; Predefined Site</a>.
            </p>
            <div class="card-body p-0 p-md-3">

              <form action="{{ route('editIcons') }}" enctype="multipart/form-data" method="post">
                @csrf
                <div class="form-group col-lg-8">

                  @php
                    // Helpers: read existing url / find link row id / count clicks
                    // for each brand by querying the `links` table for rows owned
                    // by the current user with button_id=94 (the "icon" Button
                    // type) and a matching brand `title`. These functions used to
                    // live inside the Links page; moved here verbatim so /studio/links
                    // can be cleaned up to just show the main link cards.
                    if (!function_exists('iconLink')) {
                        function iconLink($icon) {
                            $iconLink = DB::table('links')
                                ->where('user_id', Auth::id())
                                ->where('title', $icon)
                                ->where('button_id', 94)
                                ->value('link');
                            return is_null($iconLink) ? false : $iconLink;
                        }
                    }

                    if (!function_exists('searchIcon')) {
                        function searchIcon($icon) {
                            $iconId = DB::table('links')
                                ->where('user_id', Auth::id())
                                ->where('title', $icon)
                                ->where('button_id', 94)
                                ->value('id');
                            return is_null($iconId) ? false : $iconId;
                        }
                    }

                    if (!function_exists('iconclicks')) {
                        function iconclicks($icon) {
                            $iconClicks = searchIcon($icon);
                            $iconClicks = DB::table('links')->where('id', $iconClicks)->value('click_number');
                            return is_null($iconClicks) ? 0 : $iconClicks;
                        }
                    }

                    if (!function_exists('icon')) {
                        /**
                         * Render one Social Icons input row.
                         *
                         * $prefix (optional): visible URL prefix label, e.g.
                         *   'facebook.com/'. When provided, the input field
                         *   strips the matching base URL from any saved
                         *   value so the user sees just their handle, and
                         *   the placeholder hints at handle-only entry.
                         *   Server-side normalisation in editIcons does the
                         *   reverse (prepends the base on save).
                         *
                         * Omit $prefix for brands with non-standard URL
                         * shapes (Mastodon, Bluesky, WhatsApp, Discord) —
                         * the input stays as a full-URL paste.
                         */
                        function icon($name, $label, $prefix = null, $placeholder = '') {
                            $saved = iconLink($name);
                            $display = $saved ?: '';
                            if ($prefix && $display !== '') {
                                $pattern = '#^https?://' . preg_quote($prefix, '#') . '#i';
                                if (preg_match($pattern, $display)) {
                                    $display = preg_replace($pattern, '', $display);
                                }
                            }
                            $deleteBtn = searchIcon($name) != NULL
                                ? '<a href="'.route("deleteLink", searchIcon($name)).'" class="btn btn-danger" title="Remove"><i class="bi bi-trash-fill"></i></a>'
                                : '';
                            $prefixSpan = $prefix
                                ? '<span class="input-group-text text-muted small">'.e($prefix).'</span>'
                                : '';
                            $inputType = $prefix ? 'text' : 'url';
                            $placeholderAttr = $placeholder ? ' placeholder="'.e($placeholder).'"' : '';
                            echo '<div class="mb-3">
                                    <label class="form-label">'.$label.'</label>
                                    <span class="form-text" style="font-size: 90%; font-style: italic;">'.__('messages.Clicks').': '.iconclicks($name).'</span>
                                    <div class="input-group">
                                      <span class="input-group-text"><i class="fab fa-'.$name.'"></i></span>
                                      '.$prefixSpan.'
                                      <input type="'.$inputType.'" class="form-control" name="'.$name.'" value="'.e($display).'"'.$placeholderAttr.' />
                                      '.$deleteBtn.'
                                    </div>
                                  </div>';
                        }
                    }
                  @endphp
                  <style>input{border-top-right-radius: 0.25rem!important; border-bottom-right-radius: 0.25rem!important;}</style>

                  {{-- Handle-only brands (server prepends URL on save) --}}
                  {!! icon('instagram', 'Instagram', 'instagram.com/',     'yourhandle') !!}
                  {!! icon('x-twitter', 'X',         'x.com/',             'yourhandle') !!}
                  {!! icon('facebook',  'Facebook',  'facebook.com/',      'your.profile') !!}
                  {!! icon('github',    'GitHub',    'github.com/',        'yourhandle') !!}
                  {!! icon('linkedin',  'LinkedIn',  'linkedin.com/in/',   'yourhandle') !!}
                  {!! icon('tiktok',    'TikTok',    'tiktok.com/@',       'yourhandle') !!}
                  {!! icon('youtube',   'YouTube',   'youtube.com/@',      'yourchannel') !!}
                  {!! icon('threads',   'Threads',   'threads.net/@',      'yourhandle') !!}
                  {!! icon('twitch',    'Twitch',    'twitch.tv/',         'yourhandle') !!}
                  {!! icon('pinterest', 'Pinterest', 'pinterest.com/',     'yourhandle') !!}
                  {!! icon('snapchat',  'Snapchat',  'snapchat.com/add/',  'yourhandle') !!}
                  {!! icon('reddit',    'Reddit',    'reddit.com/user/',   'yourhandle') !!}
                  {!! icon('telegram',  'Telegram',  't.me/',              'yourhandle') !!}
                  {!! icon('behance',   'Behance',   'behance.net/',       'yourhandle') !!}
                  {!! icon('dribbble',  'Dribble',   'dribbble.com/',      'yourhandle') !!}

                  {{-- Full-URL brands (non-standard formats — paste the URL) --}}
                  {!! icon('mastodon',  'Mastodon',  null, 'https://mastodon.social/@you') !!}
                  {!! icon('bluesky',   'Bluesky',   null, 'https://bsky.app/profile/you.bsky.social') !!}
                  {!! icon('whatsapp',  'WhatsApp',  null, 'https://wa.me/15551234567') !!}
                  {!! icon('discord',   'Discord',   null, 'https://discord.gg/invitecode') !!}

                  <button type="submit" class="mt-3 ml-3 btn btn-primary">
                    <i class="bi bi-save"></i> {{__('messages.Save links')}}
                  </button>
                </div>
              </form>

            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</div>

@endsection

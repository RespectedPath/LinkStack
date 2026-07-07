@extends('layouts.sidebar')

@section('content')

<div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row">   
        
     <div class="col-lg-12">
        <div class="card   rounded">
            <div class="card-body">
               <div class="row">
                   <div class="col-sm-12">  

                    @foreach($pages as $page)

                    <section class='text-gray-400'>
                    <h3 class="mb-4 card-header"><i class="bi bi-brush">{{__('messages.Select a theme')}}</i></h3>
                    <div>
                    
                        @if($errors->any())
                        <div class="alert alert-danger d-flex align-items-center" role="alert">
                            <svg class="bi flex-shrink-0 me-2" width="24" height="24">
                                <use xlink:href="#exclamation-triangle-fill"></use>
                            </svg>
                            <div>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}
                                @endforeach
                            </div>
                        </div>
                        @endif

                        <button type="button" class="btn btn-primary mb-5" data-bs-toggle="modal" data-bs-target="#exampleModal">
                        {{__('messages.Select theme')}}
                        </button>

                    <section class="text-gray-400"></section>
                    <div>

                        <div style="max-width:1000px" class="col-md-12">
                            {{-- Live preview: use the shared studio partial so the
                                 theme page matches the phone-frame preview used on the
                                 unified editor (UI-IMPROVEMENTS item 12). Falls back to
                                 the static theme thumbnail only when the user has no
                                 public handle yet (nothing to render live). --}}
                            @if(env('USE_THEME_PREVIEW_IFRAME') === false || $page->littlelink_name == '')
                            <div class="card rounded shadow-lg bg-light">
                              <div class="flex-wrap card-header d-flex justify-content-between align-items-center bg-light">
                                <div class="header-title"><h4 class="card-title">{{__('messages.Preview')}}</h4></div>
                              </div>
                              <div class="card-body">
                                <center><img style="width:95%;max-width:700px;" src="@if(file_exists(base_path() . '/themes/' . $page->theme . '/preview.png')){{url('/themes/' . $page->theme . '/preview.png')}}@elseif($page->theme === 'default' or empty($page->theme)){{url('/assets/linkstack/images/themes/default.png')}}@else{{url('/assets/linkstack/images/themes/no-preview.png')}}@endif"></center>
                              </div>
                            </div>
                            @else
                            @include('studio.partials.live-preview', ['littleLinkName' => $page->littlelink_name])
                            @endif
                          </div>
  
                   </div>
               </div>
            </div>
         </div>
        </div>
      </div>
    </div>

    {{-- Custom background uploader removed (Mail Minted): it duplicated
         the Appearance → Background tab (/studio/appearance), which owns
         solid / gradient / image backgrounds with browser-side resize and
         stores them in theme_customization. Keeping two upload paths for
         the same thing was the "double preview box" on this page. The
         themeBackground route + rem-background route still exist server-
         side; they're just no longer surfaced here. See PRE-DEPLOY-AUDIT.md
         and UI-IMPROVEMENTS item 10. --}}

     @if(auth()->user()->role == 'admin')
    <div class="col-lg-12">
        <div class="card   rounded">
           <div class="card-body">
              <div class="row">
                  <div class="col-sm-12">  
                    <h3 class="mb-4 card-header">{{__('messages.Manage themes')}}</h3>
                    @if(env('ENABLE_THEME_UPDATER') == 'true')
                    
                    <div id="ajax-container">
                    
                        <br><br><br>
                        <div class="accordion">
                            <div class="accordion-item">
                              <h2 class="accordion-header" id="details-header">
                                <button class="accordion-button collapsed disabled" type="button" aria-expanded="false" aria-controls="details-collapse">
                                    <div style="max-height:20px;max-width:20px;" class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">{{__('messages.Loading...')}}</span>
                                    </div>
                                </button>
                              </h2>
                              <div id="details-collapse" class="accordion-collapse collapse" aria-labelledby="details-header">
                                <div class="accordion-body"></div>
                              </div>
                            </div>
                          </div>
                    
                    </div>
                    <div id="my-lazy-element"></div>
                    @endif
                    
                    <br><br><br>
                    <form action="{{ route('editTheme') }}" enctype="multipart/form-data" method="post">
                        @csrf
                        {{-- <h3>{{__('messages.Upload themes')}}</h3> --}}
                        <div style="display: none;" class="form-group col-lg-8">
                            <select class="form-control" name="theme">
                                <option>{{ $page->theme }}</option>
                            </select>
                            <br>
                        </div>
                        <div class="mb-3">
                            <label>{{__('messages.Upload themes')}}</label>
                            <input type="file" accept=".zip" name="zip" class="form-control form-control-lg">
                        </div><br><br>
                        <div class="d-flex flex-column flex-md-row align-items-md-center">
                            <button type="submit" class="btn btn-primary me-md-3 mb-3 mb-md-0">{{__('messages.Upload themes')}}</button>
                            <button class="btn btn-danger me-md-3 mb-3 mb-md-0 delete-themes" title="Delete themes"><a href="{{ url('/admin/theme') }}" class="text-white">{{__('messages.Delete themes')}}</a></button>
                            <button class="btn btn-info download-themes" title="Download more themes"><a href="https://linkstack.org/themes/" target="_blank" class="text-white">{{__('messages.Download themes')}}</a></button>
                          </div>
                    </form>
                    </details>
                    </div>
                  </div>
              </div>
           </div>
        </div>
     </div>   
     @endif 

@endforeach

<script nonce="{{ csp_nonce() }}" src="{{ asset('assets/external-dependencies/jquery-1.12.4.min.js') }}"></script>
</section>
<script nonce="{{ csp_nonce() }}">
$(window).on('load', function() {
    var placeholder = $('#ajax-container');
    var lazyElement = $('#my-lazy-element');
    
    $.ajax({
        url: '../theme-updater',
        success: function(response) {
            placeholder.replaceWith(lazyElement);
            
            lazyElement.html(response);
        }
    });
});
</script>
<script nonce="{{ csp_nonce() }}" type="text/javascript">$("iframe").load(function() { $("iframe").contents().find("a").each(function(index) { $(this).on("click", function(event) { event.preventDefault(); event.stopPropagation(); }); }); });</script>

@push('sidebar-scripts')
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">{{__('messages.Select a theme')}}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <form action="{{ route('editTheme') }}" enctype="multipart/form-data" method="post">
                @csrf
                <select id="theme-select" style="display:none;" name="theme" data-base-url="{{ url('') }}/@<?= Auth::user()->littlelink_name ?>"><option value="default" selected></option></select>
                <?php
                    // Collect themes grouped by category (parsed from each readme.md).
                    $catOrder = ['Trade','Beauty','Wellness','Food','Creative','Professional','Lifestyle'];
                    $grouped = [];
                    if ($handle = opendir(base_path('themes'))) {
                        while (false !== ($entry = readdir($handle))) {
                            if ($entry === '.' || $entry === '..') continue;
                            $readme = base_path('themes') . '/' . $entry . '/readme.md';
                            if (!file_exists($readme)) continue;
                            $text = file_get_contents($readme);
                            $themeName = null; $themeCat = 'Other';
                            if (preg_match('/Theme Name:\s*(.*)/', $text, $m))  $themeName = trim($m[1]);
                            if (preg_match('/Theme Category:\s*(.*)/', $text, $mc)) $themeCat = trim($mc[1]);
                            if (!$themeName) continue;
                            $grouped[$themeCat][] = ['slug' => $entry, 'name' => $themeName];
                        }
                        closedir($handle);
                    }
                    $cats = array_keys($grouped);
                    usort($cats, function($a, $b) use ($catOrder) {
                        $ia = array_search($a, $catOrder); $ib = array_search($b, $catOrder);
                        $ia = $ia === false ? 999 : $ia; $ib = $ib === false ? 999 : $ib;
                        return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
                    });
                    foreach ($grouped as &$arr) usort($arr, fn($x, $y) => strcmp($x['name'], $y['name']));
                    unset($arr);
                ?>

                <div class="mb-4">
                    <input type="text" id="theme-search" class="form-control form-control-lg" placeholder="Search by profession or category…" autocomplete="off">
                    <p id="theme-noresults" class="text-muted mt-2" style="display:none;">No themes match your search.</p>
                </div>

                <div class="theme-cat-section" data-cat="basics">
                    <h4 class="mb-3 mt-2">Basics</h4>
                    <div class="row">
                        <div class="col-lg-3 theme-card-wrap" data-name="default theme" data-cat="basics">
                            <div class="card shadow-lg @if($page->theme == "" or $page->theme == "default") bg-primary @else bg-soft-primary @endif">
                               <div class="card-body pb-0">
                                <a style="cursor:pointer;" data-set-theme="default">
                                  <div class="d-flex justify-content-between"><div>
                                     <img draggable="false" class="bd-placeholder-img bd-placeholder-img-lg img-fluid" src="{{url('assets/linkstack/images/themes/default.png')}}">
                                  </div></div>
                                  <div class="text-center">
                                     <h2 class="m-3 @if($page->theme == "" or $page->theme == "default") text-white @else text-gray @endif">Default Theme</h2>
                                  </div>
                                </a>
                               </div>
                            </div>
                        </div>
                    </div>
                </div>

                @foreach($cats as $cat)
                <div class="theme-cat-section" data-cat="{{ strtolower($cat) }}">
                    <h4 class="mb-3 mt-4">{{ $cat }}</h4>
                    <div class="row">
                        @foreach($grouped[$cat] as $t)
                        <div class="col-lg-3 theme-card-wrap" data-name="{{ strtolower($t['name']) }}" data-cat="{{ strtolower($cat) }}">
                            <div class="card shadow-lg @if($page->theme == $t['slug']) bg-primary @else bg-soft-primary @endif">
                               <div class="card-body pb-0">
                                <a style="cursor:pointer;" data-set-theme="{{ $t['slug'] }}">
                                  <div class="d-flex justify-content-between"><div>
                                     <img draggable="false" class="bd-placeholder-img bd-placeholder-img-lg img-fluid" src="{{url('themes/'.$t['slug'].'/preview.png')}}">
                                  </div></div>
                                  <div class="text-center">
                                     <h2 class="m-3 @if($page->theme == $t['slug']) text-white @else text-gray @endif">{{ $t['name'] }}</h2>
                                  </div>
                                </a>
                               </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{__('messages.Close')}}</button>
            </div>
        </div>
    </div>
    </div>
<script nonce="{{ csp_nonce() }}">
      // Delegated (replaces inline onclick under CSP): theme cards carry
      // data-set-theme="<slug>".
      document.addEventListener('click', function (e) {
        var el = e.target.closest ? e.target.closest('[data-set-theme]') : null;
        if (el) setTheme(el.getAttribute('data-set-theme'));
      });
      function setTheme(themeName) {
        const selectElement = document.getElementById('theme-select');
        selectElement.querySelector('option').value = themeName;
        selectElement.form.submit();
      }
      (function () {
        const input = document.getElementById('theme-search');
        if (!input) return;
        const noRes = document.getElementById('theme-noresults');
        input.addEventListener('input', function () {
          const q = this.value.trim().toLowerCase();
          let anyVisible = false;
          document.querySelectorAll('.theme-cat-section').forEach(function (sec) {
            let secVisible = false;
            sec.querySelectorAll('.theme-card-wrap').forEach(function (card) {
              const hay = card.getAttribute('data-name') + ' ' + card.getAttribute('data-cat');
              const show = q === '' || hay.indexOf(q) !== -1;
              card.style.display = show ? '' : 'none';
              if (show) secVisible = true;
            });
            sec.style.display = secVisible ? '' : 'none';
            if (secVisible) anyVisible = true;
          });
          if (noRes) noRes.style.display = anyVisible ? 'none' : '';
        });
      })();
  </script>
@endpush

@endsection

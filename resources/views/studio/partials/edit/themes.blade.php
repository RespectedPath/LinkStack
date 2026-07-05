{{--
    Themes tab — customer-facing theme picker, ported from the standalone
    /studio/theme page into the unified editor (UI-IMPROVEMENTS item 12a).
    Shows the current theme + a "Browse themes" button that opens a
    fullscreen modal grid (search + category sections). Picking a card
    submits to the existing editTheme route, which now redirects back to
    /studio/edit#themes.

    IDs are prefixed `mm-theme-*` to avoid colliding with anything else on
    the unified editor page. The admin-only "Manage themes" (theme-zip
    uploader) intentionally stays on /studio/theme — not ported here.

    Uses $user (User::find in showEditor) for the current theme + handle.
--}}
@php
    $currentTheme = $user->theme ?? '';
    $isDefault = ($currentTheme === '' || $currentTheme === 'default');
    // Resolve a friendly name for the current theme from its readme.
    $currentName = 'Default Theme';
    if (!$isDefault) {
        $readmePath = base_path('themes/' . $currentTheme . '/readme.md');
        if (is_file($readmePath) && preg_match('/Theme Name:\s*(.*)/', file_get_contents($readmePath), $m)) {
            $currentName = trim($m[1]);
        } else {
            $currentName = $currentTheme;
        }
    }
    $currentPreview = $isDefault
        ? url('assets/linkstack/images/themes/default.png')
        : (is_file(base_path('themes/' . $currentTheme . '/preview.png'))
            ? url('themes/' . $currentTheme . '/preview.png')
            : url('assets/linkstack/images/themes/no-preview.png'));
@endphp

<section class="text-gray-400">
  <h3 class="mb-4 card-header"><i class="bi bi-brush"></i> Themes</h3>
  <p class="text-muted small">
    A theme sets your page's overall look — colors, fonts, and background.
    You can fine-tune anything afterward on the Appearance tab.
  </p>

  <div class="d-flex align-items-center gap-3 mb-3" style="flex-wrap: wrap;">
    <img src="{{ $currentPreview }}" alt="Current theme preview"
         style="width: 90px; height: auto; border-radius: 8px; border: 1px solid rgba(128,128,128,0.25);">
    <div>
      <div class="small text-muted">Current theme</div>
      <div style="font-weight: 600; font-size: 1.05rem;">{{ $currentName }}</div>
    </div>
  </div>

  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#mm-theme-modal">
    <i class="bi bi-grid-3x3-gap"></i> Browse themes
  </button>
</section>

{{-- ===== Theme picker modal ===== --}}
<div class="modal fade" id="mm-theme-modal" tabindex="-1" aria-labelledby="mm-theme-modal-label" aria-hidden="true">
  <div class="modal-dialog modal-fullscreen">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="mm-theme-modal-label">{{__('messages.Select a theme')}}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <form action="{{ route('editTheme') }}" enctype="multipart/form-data" method="post">
          @csrf
          <select id="mm-theme-select" style="display:none;" name="theme"><option value="default" selected></option></select>
          @php
              // Collect themes grouped by category (parsed from each readme.md).
              $catOrder = ['Trade','Beauty','Wellness','Food','Creative','Professional','Lifestyle'];
              $grouped = [];
              if ($dh = opendir(base_path('themes'))) {
                  while (false !== ($entry = readdir($dh))) {
                      if ($entry === '.' || $entry === '..') continue;
                      $readme = base_path('themes') . '/' . $entry . '/readme.md';
                      if (!file_exists($readme)) continue;
                      $text = file_get_contents($readme);
                      $themeName = null; $themeCat = 'Other';
                      if (preg_match('/Theme Name:\s*(.*)/', $text, $mn))  $themeName = trim($mn[1]);
                      if (preg_match('/Theme Category:\s*(.*)/', $text, $mc)) $themeCat = trim($mc[1]);
                      if (!$themeName) continue;
                      $grouped[$themeCat][] = ['slug' => $entry, 'name' => $themeName];
                  }
                  closedir($dh);
              }
              $cats = array_keys($grouped);
              usort($cats, function($a, $b) use ($catOrder) {
                  $ia = array_search($a, $catOrder); $ib = array_search($b, $catOrder);
                  $ia = $ia === false ? 999 : $ia; $ib = $ib === false ? 999 : $ib;
                  return $ia === $ib ? strcmp($a, $b) : $ia <=> $ib;
              });
              foreach ($grouped as &$arr) usort($arr, fn($x, $y) => strcmp($x['name'], $y['name']));
              unset($arr);
          @endphp

          <div class="mb-4">
            <input type="text" id="mm-theme-search" class="form-control form-control-lg" placeholder="Search by profession or category…" autocomplete="off">
            <p id="mm-theme-noresults" class="text-muted mt-2" style="display:none;">No themes match your search.</p>
          </div>

          <div class="theme-cat-section" data-cat="basics">
            <h4 class="mb-3 mt-2">Basics</h4>
            <div class="row">
              <div class="col-lg-3 theme-card-wrap" data-name="default theme" data-cat="basics">
                <div class="card shadow-lg @if($isDefault) bg-primary @else bg-soft-primary @endif">
                  <div class="card-body pb-0">
                    <a style="cursor:pointer;" onclick="mmSetTheme('default')">
                      <div class="d-flex justify-content-between"><div>
                        <img draggable="false" class="bd-placeholder-img bd-placeholder-img-lg img-fluid" src="{{url('assets/linkstack/images/themes/default.png')}}">
                      </div></div>
                      <div class="text-center">
                        <h2 class="m-3 @if($isDefault) text-white @else text-gray @endif">Default Theme</h2>
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
                <div class="card shadow-lg @if($currentTheme == $t['slug']) bg-primary @else bg-soft-primary @endif">
                  <div class="card-body pb-0">
                    <a style="cursor:pointer;" onclick="mmSetTheme('{{ $t['slug'] }}')">
                      <div class="d-flex justify-content-between"><div>
                        <img draggable="false" class="bd-placeholder-img bd-placeholder-img-lg img-fluid" src="{{url('themes/'.$t['slug'].'/preview.png')}}">
                      </div></div>
                      <div class="text-center">
                        <h2 class="m-3 @if($currentTheme == $t['slug']) text-white @else text-gray @endif">{{ $t['name'] }}</h2>
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

@push('sidebar-scripts')
<script>
  function mmSetTheme(themeName) {
    var sel = document.getElementById('mm-theme-select');
    sel.querySelector('option').value = themeName;
    sel.form.submit();
  }
  (function () {
    var input = document.getElementById('mm-theme-search');
    if (!input) return;
    var noRes = document.getElementById('mm-theme-noresults');
    input.addEventListener('input', function () {
      var q = this.value.trim().toLowerCase();
      var anyVisible = false;
      document.querySelectorAll('#mm-theme-modal .theme-cat-section').forEach(function (sec) {
        var secVisible = false;
        sec.querySelectorAll('.theme-card-wrap').forEach(function (card) {
          var hay = card.getAttribute('data-name') + ' ' + card.getAttribute('data-cat');
          var show = q === '' || hay.indexOf(q) !== -1;
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

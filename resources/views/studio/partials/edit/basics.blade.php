<?php use App\Models\UserData; ?>
{{--
    Basics tab — ported verbatim from /studio/page.blade.php.
    Display name, page description (CKEditor), and the page-behavior
    toggles (verified checkmark [admin/vip], share button, open links
    in new tab). Posts to editPage / editProfilePicture exactly as the
    standalone page did — no controller change.

    The profile photo intentionally stays on the Appearance tab (its
    upload + shape controls live together there); this tab links across
    to it for anyone looking.
--}}

<style>
    .ck-editor__editable[role="textbox"] {
        /* editing area */
        min-height: 200px;
    }

    .ck-content .image {
        /* block images */
        max-width: 80%;
        margin: 20px auto;
    }
</style>
<style>
    @supports (-webkit-appearance: none) or (-moz-appearance: none) {
      input[type=checkbox],
      input[type=radio] {
      --active: var(--bs-primary);
      --active-inner: #fff;
      --focus: 2px var(--bs-primary);
      --border: #BBC1E1;
      --border-hover: var(--bs-primary);
      --background: #fff;
      --disabled: #F6F8FF;
      --disabled-inner: #E1E6F9;
      -webkit-appearance: none;
      -moz-appearance: none;
      height: 21px;
      outline: none;
      display: inline-block;
      vertical-align: top;
      position: relative;
      margin: 0;
      cursor: pointer;
      border: 1px solid var(--bc, var(--border));
      background: var(--b, var(--background));
      transition: background 0.3s, border-color 0.3s, box-shadow 0.2s;
      }
    input[type=checkbox]:after,
    input[type=radio]:after {
        content: "";
        display: block;
        left: 0;
        top: 0;
        position: absolute;
        transition: transform var(--d-t, 0.3s) var(--d-t-e, ease), opacity var(--d-o, 0.2s);
      }
      input[type=checkbox]:checked,
    input[type=radio]:checked {
        --b: var(--active);
        --bc: var(--active);
        --d-o: .3s;
        --d-t: .6s;
        --d-t-e: cubic-bezier(.2, .85, .32, 1.2);
      }
      input[type=checkbox]:disabled,
    input[type=radio]:disabled {
        --b: var(--disabled);
        cursor: not-allowed;
        opacity: 0.9;
      }
      input[type=checkbox]:disabled:checked,
    input[type=radio]:disabled:checked {
        --b: var(--disabled-inner);
        --bc: var(--border);
      }
      input[type=checkbox]:disabled + label,
    input[type=radio]:disabled + label {
        cursor: not-allowed;
      }
      input[type=checkbox]:hover:not(:checked):not(:disabled),
    input[type=radio]:hover:not(:checked):not(:disabled) {
        --bc: var(--border-hover);
      }
      input[type=checkbox]:focus,
    input[type=radio]:focus {
        box-shadow: 0 0 0 var(--focus);
      }
      input[type=checkbox]:not(.switch),
    input[type=radio]:not(.switch) {
        width: 21px;
      }
      input[type=checkbox]:not(.switch):after,
    input[type=radio]:not(.switch):after {
        opacity: var(--o, 0);
      }
      input[type=checkbox]:not(.switch):checked,
    input[type=radio]:not(.switch):checked {
        --o: 1;
      }
      input[type=checkbox] + label,
    input[type=radio] + label {
        font-size: 14px;
        line-height: 21px;
        display: inline-block;
        vertical-align: top;
        cursor: pointer;
        margin-left: 4px;
      }

      input[type=checkbox]:not(.switch) {
        border-radius: 7px;
      }
      input[type=checkbox]:not(.switch):after {
        width: 5px;
        height: 9px;
        border: 2px solid var(--active-inner);
        border-top: 0;
        border-left: 0;
        left: 7px;
        top: 4px;
        transform: rotate(var(--r, 20deg));
      }
      input[type=checkbox]:not(.switch):checked {
        --r: 43deg;
      }
      input[type=checkbox].switch {
        width: 38px;
        border-radius: 11px;
      }
      input[type=checkbox].switch:after {
        left: 2px;
        top: 2px;
        border-radius: 50%;
        width: 15px;
        height: 15px;
        background: var(--ab, var(--border));
        transform: translateX(var(--x, 0));
      }
      input[type=checkbox].switch:checked {
        --ab: var(--active-inner);
        --x: 17px;
      }
      input[type=checkbox].switch:disabled:not(:checked):after {
        opacity: 0.6;
      }

      input[type=radio] {
        border-radius: 50%;
      }
      input[type=radio]:after {
        width: 19px;
        height: 19px;
        border-radius: 50%;
        background: var(--active-inner);
        opacity: 0;
        transform: scale(var(--s, 0.7));
      }
      input[type=radio]:checked {
        --s: .5;
      }
    }
    .txt-label{
        color: white;
        padding-left: 5px;
        font-size: 200%;
        position: relative;
    }
    .toggle-btn{
        padding-left: 20px;
    }
    .ch2{
        padding-top: 60px;
    }
</style>

<section class='text-gray-400'>
<h3 class="mb-4 card-header"><i class="bi bi-file-earmark-break"> {{__('messages.My Profile')}}</i></h3>

<div>
<div></div>
@foreach($pages as $page)
<form action="{{ route('editPage') }}" enctype="multipart/form-data" method="post">
    @csrf
    @if($page->littlelink_name != '')
    <div class="form-group col-lg-8 mb-3">
      <small class="text-muted">Profile photo lives on the <a href="#" data-mm-tab="appearance">Appearance tab</a>.</small>
    </div>
    @endif

    <div class="form-group col-lg-8">
        {{-- Page URL (handle) is intentionally not user-editable here:
             it's tied to Mail Minted provisioning + the customer's
             shared URLs. Admin panel can still change it via
             /admin/edit-user/{id} for legitimate support cases. --}}
        <label style="margin-top:15px">{{__('messages.Display name')}}</label>
        <div class="input-group">
            <input type="text" class="form-control" name="name" value="{{ $page->name }}" required>
        </div>
    </div>

    <div class="form-group col-lg-8">
        <label>{{__('messages.Page Description')}}</label>
        <textarea class="form-control @if(env('ALLOW_USER_HTML') === true) ckeditor @endif" name="pageDescription" rows="3" maxlength="250">{{ $page->littlelink_description ?? '' }}</textarea>
        <small id="pageDescription-counter" class="text-muted d-block mt-1">0 / 250 characters</small>
    </div>
    {{-- Constrain the rich-text editor to a sensible size for
         a 1-3 sentence bio. CKEditor ignores rows="3" and
         renders ~150px tall by default; pin it.

         CKEditor 5 also ships its own theme and ignores OS
         dark mode — by default it renders dark-grey text on
         white, which becomes unreadable when the surrounding
         dashboard is in dark mode. The prefers-color-scheme
         block below adapts the editor surface, toolbar, and
         button colours to match. WCAG contrast > 7:1 for
         text in both modes. --}}
    <style>
        .ck-editor__editable_inline {
            min-height: 80px !important;
            max-height: 200px;
            overflow-y: auto;
            background: #ffffff !important;
            color: #212529 !important;
        }
        @media (prefers-color-scheme: dark) {
            .ck.ck-editor__main > .ck-editor__editable,
            .ck-editor__editable_inline {
                background: #1f2329 !important;
                color: #e9ecef !important;
            }
            .ck.ck-toolbar {
                background: #14171a !important;
                border-color: #2a2e33 !important;
            }
            .ck.ck-toolbar .ck-button,
            .ck.ck-toolbar .ck-button .ck-icon {
                color: #e9ecef !important;
            }
            .ck.ck-toolbar .ck-button:hover,
            .ck.ck-toolbar .ck-button.ck-on {
                background: #2a2e33 !important;
            }
            .ck.ck-editor__main {
                border-color: #2a2e33 !important;
            }
        }
    </style>

    @if(auth()->user()->role == 'admin' || auth()->user()->role == 'vip')
        <div class="form-group col-lg-8">
        <h5 style="margin-top:50px"> {{__('messages.Show checkmark')}}</h5>
        <p class="text-muted">{{__('messages.disableverified')}}</p>
          <div class="mb-3 form-check form-switch">
            <input name="checkmark" class="switch toggle-btn" type="checkbox" id="checkmark" <?php if(UserData::getData(Auth::user()->id, 'checkmark') == true){echo 'checked';} ?> />
            <label class="form-check-label" for="checkmark">{{__('messages.Enable')}}</label>
          </div>
        <input type="hidden" name="_token" value="{{csrf_token()}}">
    @endif
    @endforeach

    <div class="form-group col-lg-8">
      <h5 style="margin-top:50px">{{__('messages.Show share button')}}</h5>
      <p class="text-muted">{{__('messages.disablesharebutton')}}</p>
        <div class="mb-3 form-check form-switch">
          <input name="sharebtn" class="switch toggle-btn" type="checkbox" id="sharebtn" <?php if(UserData::getData(Auth::user()->id, 'disable-sharebtn') != "true"){echo 'checked';} ?> />
          <label class="form-check-label" for="sharebtn">{{__('messages.Enable')}}</label>
        </div>

        <div class="form-group col-lg-8">
          <h5 style="margin-top:50px">{{__('messages.Open links in new tab')}}</h5>
          <p class="text-muted">{{__('messages.openlinksnewtab')}}</p>
            <div class="mb-3 form-check form-switch">
              <input name="tablinks" class="switch toggle-btn" type="checkbox" id="tablinks" <?php if(UserData::getData(Auth::user()->id, 'links-new-tab') != false){echo 'checked';} ?> />
              <label class="form-check-label" for="tablinks">{{__('messages.Enable')}}</label>
            </div>

    <button id="submit-btn" type="submit" class="mt-3 ml-3 btn btn-primary">{{__('messages.Save')}}</button>
</form>

@if(env('ALLOW_USER_HTML') === true)
<script nonce="{{ csp_nonce() }}" src="{{ asset('assets/external-dependencies/ckeditor.js') }}"></script>
<script nonce="{{ csp_nonce() }}">
  ClassicEditor
      .create(document.querySelector('.ckeditor'), {
          // Minimal toolbar — only the formatting that
          // survives editPage's strip_tags allowlist
          // (<a><strong><i>). Anything else used to be
          // offered in the toolbar, formatted in preview,
          // then silently stripped on save.
          toolbar: {
              items: [
                  'bold', 'italic', '|',
                  'link', '|',
                  'undo', 'redo',
              ],
              shouldNotGroupWhenFull: true
          },
          fontFamily: {
              options: [
                  'default',
                  'Arial, Helvetica, sans-serif',
                  'Courier New, Courier, monospace',
                  'Georgia, serif',
                  'Lucida Sans Unicode, Lucida Grande, sans-serif',
                  'Tahoma, Geneva, sans-serif',
                  'Times New Roman, Times, serif',
                  'Trebuchet MS, Helvetica, sans-serif',
                  'Verdana, Geneva, sans-serif'
              ],
              supportAllValues: true
          },
          fontSize: {
              options: [10, 12, 14, 'default', 18, 20, 22],
              supportAllValues: true
          },
          link: {
              addTargetToExternalLinks: true, // Add this option to open external links in a new tab
              defaultProtocol: 'http://',
              decorators: {
                  addTargetToExternalLinks: {
                      mode: 'manual',
                      label: 'Open in new tab',
                      attributes: {
                          target: '_blank',
                          rel: 'noopener noreferrer'
                      }
                  }
              }
          }
      })
      .then(editor => {
          // Live character counter for the description.
          // Counts the plain-text content (HTML tags
          // excluded) and warns visually once 90% used.
          var counter = document.getElementById('pageDescription-counter');
          var limit = 250;
          var update = function () {
              var text = (editor.getData() || '').replace(/<[^>]*>/g, '');
              var len = text.length;
              if (counter) {
                  counter.textContent = len + ' / ' + limit + ' characters';
                  counter.className = 'd-block mt-1 ' + (len > limit * 0.9 ? 'text-danger' : 'text-muted');
              }
          };
          editor.model.document.on('change:data', update);
          update();
      })
      .catch(error => {
          console.error(error);
      });
</script>
@endif
</div>
</section>

@extends('layouts.sidebar')

@section('content')

<div class="conatiner-fluid content-inner mt-n5 py-0">
  <div class="row">   


      <div class="col-lg-12">
          <div class="card   rounded">
             <div class="card-body">
                <div class="row">
                    <div class="col-sm-12">  
  
                      <section class="text-gray-400">
                        <h2 class="mb-4 card-header"><i class="bi bi-person"> {{__('messages.Edit User')}}</i></h2>
                          <div class="card-body p-0 p-md-3">
                  
                        @foreach($user as $user)
                        <form action="{{ route('editUser', $user->id) }}" enctype="multipart/form-data" method="post" id="mm-admin-user-form">
                          @csrf
                              <div class="form-group col-lg-8">
                              <label>{{__('messages.Name')}}</label>
                              <input type="text" class="form-control" name="name" value="{{ $user->name }}">
                            </div>
                            <div class="form-group col-lg-8">
                              <label>{{__('messages.Email')}}</label>
                              <input type="email" class="form-control" name="email" value="{{ $user->email }}">
                            </div>
                            <div class="form-group col-lg-8">
                              <label>{{__('messages.Password')}}</label>
                              <input type="password" class="form-control" name="password" placeholder="Leave empty for no change">
                            </div>
                            
                            <div class="form-group col-lg-8">
                              <label>{{__('messages.Logo')}}</label>
                              <div class="mb-3">
                                <input type="file" class="form-control form-control-lg" name="image">
                            </div>
                            </div>
                            
                            <div class="form-group col-lg-8">
                              @if(file_exists(base_path(findAvatar($user->id))))
                              <img src="{{ url(findAvatar($user->id)) }}" class="bd-placeholder-img img-thumbnail" width="100" height="100" draggable="false">
                              @else
                              <img src="{{ asset('assets/linkstack/images/logo.svg') }}" class="bd-placeholder-img img-thumbnail" width="100" height="100" draggable="false">
                              @endif
                              @if(file_exists(base_path(findAvatar($user->id))))<br><a title="Remove icon" class="hvr-grow p-1 text-danger" style="padding-left:5px;" href="?delete"><i class="bi bi-trash-fill"></i> {{__('messages.Delete')}}</a>@endif
                              @if(($_SERVER['QUERY_STRING'] ?? '') === 'delete' and File::exists(base_path(findAvatar($user->id))))@php File::delete(base_path(findAvatar($user->id))); header("Location: ".url()->current()); die(); @endphp @endif
                          </div><br>
                            
                            <div class="form-group col-lg-8">
                              <label>{{__('messages.Custom background')}}</label>
                              <div class="mb-3">
                                <input type="file" class="form-control form-control-lg" name="background">
                            </div>
                            </div>
                            <div class="form-group col-lg-8">
                                @if(!file_exists(base_path('assets/img/background-img/'.findBackground($user->id))))<p><i>{{__('messages.No image selected')}}</i></p>@endif
                                <img style="width:95%;max-width:400px;argin-left:1rem!important;border-radius:5px;" src="@if(file_exists(base_path('assets/img/background-img/'.findBackground($user->id)))){{url('assets/img/background-img/'.findBackground($user->id))}}@else{{url('/assets/linkstack/images/themes/no-preview.png')}}@endif">
                                @if(file_exists(base_path('assets/img/background-img/'.findBackground($user->id))))<br><a title="Remove icon" class="hvr-grow p-1 text-danger" style="padding-left:5px;" href="?deleteB"><i class="bi bi-trash-fill"></i> {{__('messages.Delete')}}</a>@endif
                                @if(($_SERVER['QUERY_STRING'] ?? '') === 'deleteB' and File::exists(base_path('assets/img/background-img/'.findBackground($user->id))))@php File::delete(base_path('assets/img/background-img/'.findBackground($user->id))); header("Location: ".url()->current()); die(); @endphp @endif
                                <br>
                            </div><br>

                            <label>{{__('messages.Select theme')}}</label>
                              <div class="form-group col-lg-8">
                                  <select id="theme-select" style="margin-bottom: 40px;" class="form-control" name="theme" data-base-url="{{ url('') }}/@<?= Auth::user()->littlelink_name ?>">
                                      <?php
                                          if ($handle = opendir('themes')) {
                                              while (false !== ($entry = readdir($handle))) {
                                                  if ($entry != "." && $entry != "..") {
                                                      if(file_exists(base_path('themes') . '/' . $entry . '/readme.md')){
                                                          $text = file_get_contents(base_path('themes') . '/' . $entry . '/readme.md');
                                                          $pattern = '/Theme Name:.*/';
                                                          preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
                                                          if(sizeof($matches) > 0) {
                                                              $themeName = substr($matches[0][0],12);
                                                          }
                                                      }
                                                      if($user->theme != $entry and isset($themeName)){
                                                          echo '<option value="'.$entry.'" data-image="'.url('themes/'.$entry.'/screenshot.png').'">'.$themeName.'</option>';
                                                      }
                                                  }
                                              }
                                          }
                              
                                          if($user->theme != "default" and $user->theme != ""){
                                              if(file_exists(base_path('themes') . '/' . $user->theme . '/readme.md')){
                                                  $text = file_get_contents(base_path('themes') . '/' . $user->theme . '/readme.md');
                                                  $pattern = '/Theme Name:.*/';
                                                  preg_match($pattern, $text, $matches, PREG_OFFSET_CAPTURE);
                                                  $themeName = substr($matches[0][0],12);
                                              }
                                              echo '<option value="'.$user->theme.'" data-image="'.url('themes/'.$user->theme.'/screenshot.png').'" selected>'.$themeName.'</option>';
                                          }
                              
                                          echo '<option value="default" data-image="'.url('themes/default/screenshot.png').'"';
                                          if($user->theme == "default" or $user->theme == ""){
                                              echo ' selected';
                                          }
                                          echo '>Default</option>';
                                      ?>
                                  </select>
                              </div>
                            
                            <div class="form-group col-lg-8">
                              <label>{{__('messages.Page URL')}}</label>
                              <div class="input-group">
                            <div class="input-group-prepend">
                            <div class="input-group-text">{{ url('') }}/@</div>
                            </div>
                            <input type="text" class="form-control" name="littlelink_name" value="{{ $user->littlelink_name }}">
                          </div>
                        </div>
                            
                            <div class="form-group col-lg-8">
                              <label> {{__('messages.Page description')}}</label>
                              <textarea class="form-control" name="littlelink_description" rows="3">{{ $user->littlelink_description }}</textarea>
                            </div>
                            <div class="form-group col-lg-8">
                              <label for="exampleFormControlSelect1">{{__('messages.Role')}}</label>
                              <select class="form-control" name="role">
                                <option <?= ($user->role === strtolower('user')) ? 'selected' : '' ?>>user</option>
                                <option <?= ($user->role === strtolower('vip')) ? 'selected' : '' ?>>vip</option>
                                <option <?= ($user->role === strtolower('admin')) ? 'selected' : '' ?>>admin</option>
                              </select>
                            </div>
                            @endforeach
                            <div id="mm-admin-upload-status" class="small text-danger ml-3"></div>
                            <button type="submit" class="mt-3 ml-3 btn btn-primary">{{__('messages.Save')}}</button>
                          </form>

                          {{-- Same resize promise the customer uploaders keep
                               (shared mm-image-resize.js): picked photos are
                               shrunk in-browser before the multipart post, so
                               big originals can't bounce off the server's 2MB
                               cap; unreadable files (cloud stubs, HEIC) are
                               blocked with an actionable message instead of
                               posting raw. --}}
                          <script nonce="{{ csp_nonce() }}" src="{{ asset('assets/js/mm-image-resize.js') }}?v={{ filemtime(public_path('assets/js/mm-image-resize.js')) }}"></script>
                          <script nonce="{{ csp_nonce() }}">
                          (function () {
                              var form = document.getElementById('mm-admin-user-form');
                              if (!form || !window.mmResizeImage) return;
                              form.addEventListener('submit', function (e) {
                                  var logo = form.querySelector('input[name="image"]');
                                  var bg   = form.querySelector('input[name="background"]');
                                  var jobs = [];
                                  if (logo && logo.files && logo.files[0]) jobs.push({ input: logo, opts: { maxDim: 1024, name: 'avatar.jpg' } });
                                  if (bg   && bg.files   && bg.files[0])   jobs.push({ input: bg,   opts: { maxDim: 1920, name: 'background.jpg' } });
                                  if (!jobs.length) return; // no files picked — plain native submit
                                  e.preventDefault();
                                  var status = document.getElementById('mm-admin-upload-status');
                                  if (status) status.textContent = 'Preparing image…';
                                  Promise.all(jobs.map(function (j) {
                                      return window.mmResizeImage(j.input.files[0], j.opts).then(function (file) {
                                          var dt = new DataTransfer();
                                          dt.items.add(file);
                                          j.input.files = dt.files;
                                      });
                                  })).then(function () {
                                      if (status) status.textContent = '';
                                      // Native submit (skips this handler) — same
                                      // pattern as the studio photo cropper.
                                      HTMLFormElement.prototype.submit.call(form);
                                  }).catch(function (err) {
                                      if (status) status.textContent = (err && err.message) || 'Couldn\'t read that image.';
                                  });
                              });
                          })();
                          </script>
                  
                            </div>
                  </section>
  
                    </div>
                </div>
             </div>
          </div>
       </div>


    </div>
  </div>

@endsection

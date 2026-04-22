@extends('layouts.sidebar')

@section('content')

<div class="conatiner-fluid content-inner mt-n5 py-0">
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

          <section class="text-gray-400">
            <h3 class="mb-4 card-header"><i class="bi bi-palette-fill"></i> Appearance</h3>
            <p class="text-muted">Customize how your public page looks. Changes apply on top of your selected theme.</p>

            <div class="appearance-layout">

              {{-- ===== Settings panel ===== --}}
              <form id="appearance-form" class="appearance-form" action="{{ route('saveAppearance') }}" method="post">
                @csrf

                {{-- Colors --}}
                <fieldset class="appearance-section">
                  <legend>Colors</legend>

                  @include('studio.partials.appearance-color', ['id' => 'c-primary',     'name' => 'colors[primary]',     'label' => 'Primary',       'help' => 'Button fill and accent elements',            'value' => $saved['colors']['primary']])
                  @include('studio.partials.appearance-color', ['id' => 'c-background', 'name' => 'colors[background]',  'label' => 'Background',    'help' => 'Page background when no image is set',       'value' => $saved['colors']['background']])
                  @include('studio.partials.appearance-color', ['id' => 'c-text',       'name' => 'colors[text]',        'label' => 'Text',          'help' => 'Primary text on the page',                   'value' => $saved['colors']['text'],        'presets' => ['#111111', '#ffffff']])
                  @include('studio.partials.appearance-color', ['id' => 'c-btn-text',   'name' => 'colors[button_text]', 'label' => 'Button text',   'help' => 'Text on link buttons',                        'value' => $saved['colors']['button_text']])
                </fieldset>

                {{-- Background --}}
                <fieldset class="appearance-section">
                  <legend>Background</legend>

                  <div class="mb-3">
                    <label class="form-label d-block">Background type</label>
                    @foreach([['solid','Solid color'],['gradient','Gradient'],['image','Image URL']] as [$val, $lab])
                      <div class="form-check form-check-inline">
                        <input class="form-check-input appearance-bg-type" type="radio" name="background[type]" id="bgt-{{ $val }}" value="{{ $val }}" @if($saved['background']['type'] === $val) checked @endif>
                        <label class="form-check-label" for="bgt-{{ $val }}">{{ $lab }}</label>
                      </div>
                    @endforeach
                  </div>

                  <div class="appearance-bg-panel" data-bg-panel="solid" @if($saved['background']['type'] !== 'solid') style="display:none" @endif>
                    @include('studio.partials.appearance-color', ['id' => 'bg-solid', 'name' => 'background[solid]', 'label' => 'Solid color', 'help' => null, 'value' => $saved['background']['solid']])
                  </div>

                  <div class="appearance-bg-panel" data-bg-panel="gradient" @if($saved['background']['type'] !== 'gradient') style="display:none" @endif>
                    <div class="row g-2">
                      <div class="col-md-6">
                        @include('studio.partials.appearance-color', ['id' => 'bg-grad-start', 'name' => 'background[gradient_start]', 'label' => 'Gradient start', 'help' => null, 'value' => $saved['background']['gradient_start']])
                      </div>
                      <div class="col-md-6">
                        @include('studio.partials.appearance-color', ['id' => 'bg-grad-end',   'name' => 'background[gradient_end]',   'label' => 'Gradient end',   'help' => null, 'value' => $saved['background']['gradient_end']])
                      </div>
                    </div>
                    <div class="mt-2">
                      <label class="form-label">Direction</label>
                      <select class="form-select" name="background[gradient_direction]">
                        @foreach([['to bottom','Top to bottom'],['to right','Left to right'],['to bottom right','Diagonal']] as [$val, $lab])
                          <option value="{{ $val }}" @if($saved['background']['gradient_direction'] === $val) selected @endif>{{ $lab }}</option>
                        @endforeach
                      </select>
                    </div>
                  </div>

                  <div class="appearance-bg-panel" data-bg-panel="image" @if($saved['background']['type'] !== 'image') style="display:none" @endif>
                    <label for="bg-image-url" class="form-label">Image URL</label>
                    <input type="url" id="bg-image-url" name="background[image_url]" class="form-control" value="{{ $saved['background']['image_url'] }}" maxlength="2048" placeholder="https://example.com/bg.jpg">
                    <small class="text-muted">Paste a direct link to a hosted image. No file upload.</small>
                  </div>
                </fieldset>

                {{-- Typography --}}
                <fieldset class="appearance-section">
                  <legend>Typography</legend>
                  <label for="font-select" class="form-label">Font</label>
                  <select id="font-select" class="form-select" name="typography[font]">
                    <option value="" @if(empty($saved['typography']['font'])) selected @endif>Theme default</option>
                    @foreach($fonts as $f)
                      <option value="{{ $f }}" style="font-family: '{{ $f }}', sans-serif" @if($saved['typography']['font'] === $f) selected @endif>{{ $f }}</option>
                    @endforeach
                  </select>
                  <small class="text-muted">Only the selected font is loaded from Google Fonts.</small>
                </fieldset>

                {{-- Buttons --}}
                <fieldset class="appearance-section">
                  <legend>Buttons</legend>

                  <label class="form-label d-block">Shape</label>
                  <div class="appearance-swatch-group" role="radiogroup">
                    @foreach(['pill','rounded','square'] as $shape)
                      <label class="appearance-swatch" data-swatch="shape-{{ $shape }}">
                        <input type="radio" name="buttons[shape]" value="{{ $shape }}" @if($saved['buttons']['shape'] === $shape) checked @endif>
                        <span class="appearance-swatch-preview appearance-swatch-btn appearance-swatch-btn-{{ $shape }}">Button</span>
                        <span class="appearance-swatch-name">{{ ucfirst($shape) }}</span>
                      </label>
                    @endforeach
                  </div>

                  <label class="form-label d-block mt-3">Style</label>
                  <div class="appearance-swatch-group" role="radiogroup">
                    @foreach(['filled','outline','soft'] as $style)
                      <label class="appearance-swatch" data-swatch="style-{{ $style }}">
                        <input type="radio" name="buttons[style]" value="{{ $style }}" @if($saved['buttons']['style'] === $style) checked @endif>
                        <span class="appearance-swatch-preview appearance-swatch-btn-style-{{ $style }}">Button</span>
                        <span class="appearance-swatch-name">{{ ucfirst($style) }}</span>
                      </label>
                    @endforeach
                  </div>
                </fieldset>

                {{-- Avatar --}}
                <fieldset class="appearance-section">
                  <legend>Avatar</legend>
                  <div class="appearance-swatch-group" role="radiogroup">
                    @foreach([['circle','Circle'],['rounded_square','Rounded square']] as [$val, $lab])
                      <label class="appearance-swatch" data-swatch="avatar-{{ $val }}">
                        <input type="radio" name="avatar[shape]" value="{{ $val }}" @if($saved['avatar']['shape'] === $val) checked @endif>
                        <span class="appearance-swatch-preview appearance-swatch-avatar appearance-swatch-avatar-{{ $val }}"></span>
                        <span class="appearance-swatch-name">{{ $lab }}</span>
                      </label>
                    @endforeach
                  </div>
                </fieldset>

                <div class="appearance-actions">
                  <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Save appearance
                  </button>
                  <button type="button" class="btn btn-outline-secondary" id="appearance-reset-btn">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset to defaults
                  </button>
                </div>
              </form>

              <form id="appearance-reset-form" action="{{ route('resetAppearance') }}" method="post" style="display:none">
                @csrf
              </form>

              {{-- ===== Live preview ===== --}}
              <aside class="appearance-preview">
                <div class="appearance-preview-header">
                  <h6 class="mb-0"><i class="bi bi-phone"></i> Live preview</h6>
                  <a href="{{ url('/@' . ($user->littlelink_name ?? '')) }}" target="_blank" class="small">Open in new tab &rarr;</a>
                </div>
                <div class="appearance-preview-frame">
                  <iframe id="appearance-preview-iframe"
                          src="{{ url('/@' . ($user->littlelink_name ?? '')) . '?preview=1' }}"
                          title="Live preview of your public page"
                          loading="lazy"></iframe>
                </div>
              </aside>

            </div>
          </section>

        </div>
      </div>
    </div>
  </div>
</div>

<link rel="stylesheet" href="{{ asset('assets/css/appearance.css') }}">
<script src="{{ asset('assets/js/appearance.js') }}"></script>

@endsection

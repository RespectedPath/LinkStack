{{--
    Reusable color picker: native <input type="color"> synced with a
    hex text field. The two are kept in lockstep by small JS in
    appearance.js. Optional $presets array renders quick-click tiles.

    Required: $id, $name, $label, $value
    Optional: $help, $presets, $form (HTML form= id to associate when
              this input sits outside the form's DOM tree — used by
              the Appearance editor's tabbed layout), $editedKeys
              (dot-key(s) for the "edited" chip — see edited-badge)
--}}
<div class="appearance-color-field mb-3">
    <label for="{{ $id }}-hex" class="form-label d-flex justify-content-between align-items-center mb-1">
        <span>{{ $label }}@isset($editedKeys) @include('studio.partials.edited-badge', ['keys' => $editedKeys])@endisset</span>
        @if(!empty($presets ?? null))
            <span class="appearance-color-presets">
                @foreach($presets as $p)
                    <button type="button" class="appearance-color-preset" data-target="{{ $id }}" data-preset="{{ $p }}" style="background: {{ $p }}" title="Set to {{ $p }}"></button>
                @endforeach
            </span>
        @endif
    </label>
    <div class="input-group">
        <input type="color" class="form-control form-control-color appearance-color-swatch" id="{{ $id }}" data-pair="{{ $id }}-hex" value="{{ $value }}" @if(!empty($form ?? null)) form="{{ $form }}" @endif>
        <input type="text" class="form-control appearance-color-hex" id="{{ $id }}-hex" name="{{ $name }}" value="{{ $value }}" pattern="^#[0-9a-fA-F]{6}$" maxlength="7" data-pair="{{ $id }}" placeholder="#000000" @if(!empty($form ?? null)) form="{{ $form }}" @endif>
    </div>
    @if(!empty($help ?? null))
        <small class="text-muted">{{ $help }}</small>
    @endif
</div>

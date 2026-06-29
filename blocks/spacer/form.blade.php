{{-- Spacer height in actual pixels. Previously this was a unit-less
     range slider whose value was secretly multiplied by 5 at render
     time — saved "5" meant 25 px on the page. Storing actual pixels
     now so the number the operator sees IS the gap visitors see.
     UI-PASS-PLAN.md Pass 1, item 7. --}}
<label for='height' class='form-label'>Spacer height</label>
<div class='input-group' style='max-width: 220px;'>
    <input type='number'
           name='height'
           id='height'
           value="{{ $params->height ?? 32 }}"
           class='form-control'
           min='8'
           max='200'
           step='4'>
    <span class='input-group-text'>px</span>
</div>
<small class='form-text text-muted'>Vertical gap between the blocks above and below. 8 to 200 pixels.</small>

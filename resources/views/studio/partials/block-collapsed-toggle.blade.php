{{--
    Per-block-instance "Start collapsed" toggle.

    Include from any block's form.blade.php to opt the block into the
    accordion treatment in resources/views/linkstack/elements/buttons.blade.php.

    Storage: the value rides into links.type_params JSON via the standard
    "non-column linkData key" path in UserController::saveLink. On edit,
    LinkTypeViewController::getParamForm merges type_params back onto the
    form's view data, so $collapsed is pre-filled from prior state.
--}}
<div class="form-check form-switch mt-3">
    {{-- Hidden default so the field is always submitted, even when the box is unchecked. --}}
    <input type="hidden" name="collapsed" value="0">
    <input class="form-check-input" type="checkbox" id="block-collapsed" name="collapsed" value="1" @if(!empty($collapsed ?? null)) checked @endif>
    <label class="form-check-label" for="block-collapsed">
        Start collapsed
    </label>
    <small class="text-muted d-block">Visitors see only the section heading and click it to expand.</small>
</div>

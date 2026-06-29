@extends('layouts.sidebar')

@section('content')

@push('sidebar-stylesheets')
<script src="{{ asset('assets/external-dependencies/fontawesome.js') }}" crossorigin="anonymous"></script>
@endpush

<div class="conatiner-fluid content-inner mt-n5 py-0">
    <div class="row">
     <div class="col-lg-12">
        <div class="card rounded">
            <div class="card-body">
               <div class="row">
                   <div class="col-sm-12">

                    @push('sidebar-stylesheets')
                    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
                    @endpush

                    @if($LinkID === 0)
                        {{-- ====================================================
                             ADD MODE: page is a grid of block-type tiles.
                             Each tile opens a modal containing that block's
                             form. A new block (separate row in the `links`
                             table) is created each time the user saves —
                             which is how multiple Mailchimp / contact form /
                             Stripe payment blocks coexist on one page.
                             ==================================================== --}}
                        <section class='text-gray-400'>
                            <h3 class="card-header"><i class="bi bi-journal-plus"></i> {{__('messages.Add')}} {{__('messages.Block')}}</h3>
                            <div class='card-body'>
                                <p class="text-muted mb-3">Choose what kind of block to add to your page. You can add as many of each type as you want &mdash; each one is independent.</p>

                                <div id="blockTypeGrid" class="p-0">
                                    @php
                                        // Group the LinkTypes by their category, then render
                                        // groups in the canonical CATEGORY_ORDER. Each group
                                        // is a section with a small muted header and the
                                        // tile grid underneath. Within a group the existing
                                        // sort order from LinkType::get() is preserved.
                                        $grouped = collect($LinkTypes)->groupBy('category');
                                    @endphp

                                    @foreach (\App\Models\LinkType::CATEGORY_ORDER as $catKey)
                                        @php
                                            $tiles = $grouped->get($catKey, collect());
                                        @endphp
                                        @if($tiles->isNotEmpty())
                                            <h6 class="text-muted text-uppercase mt-3 mb-2 small" style="letter-spacing:0.04em;">
                                                {{ \App\Models\LinkType::CATEGORY_LABELS[$catKey] ?? ucfirst($catKey) }}
                                            </h6>
                                            <div class="d-flex flex-row flex-wrap mb-2">
                                                @foreach ($tiles as $lt)
                                                    @php
                                                        if (block_text_translation_check($lt['title'])) {
                                                            $title = bt($lt['title']);
                                                        } else {
                                                            $title = __('messages.block.title.' . $lt['typename']);
                                                        }
                                                        $description = bt($lt['description']) ?? __('messages.block.description.' . $lt['typename']);
                                                    @endphp
                                                    <a href="#" data-typeid="{{ $lt['typename'] }}" data-typename="{{ $title }}" class="hvr-grow m-2 w-100 d-block doSelectLinkType">
                                                        <div class="rounded mb-3 shadow-lg">
                                                            <div class="row g-0">
                                                                <div class="col-auto bg-light d-flex align-items-center justify-content-center p-3">
                                                                    <i class="{{ $lt['icon'] }} text-primary h1 mb-0"></i>
                                                                </div>
                                                                <div class="col">
                                                                    <div class="card-body">
                                                                        <h5 class="card-title text-dark mb-0">{{ $title }}</h5>
                                                                        <p class="card-text text-muted">{{ $description }}</p>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </section>

                        {{-- Modal: hosts the chosen block's form. The whole
                             <form> wraps body+footer so the Save button
                             actually submits. data-bs-dismiss uses BS5 syntax;
                             page already loads BS5 via the sidebar layout. --}}
                        <div class="modal fade" id="addBlockModal" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('addLink') }}" method="post" id="my-form">
                                        @method('POST')
                                        @csrf
                                        <input type='hidden' name='linkid' value="0" />
                                        <input type='hidden' name='typename' value='' />

                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                {{__('messages.Add')}}: <span id="selectedBlockName" class="text-primary">&mdash;</span>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div id="link_params"></div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal" data-dismiss="modal">{{__('messages.Cancel')}}</button>
                                            <button type="button" class="btn btn-soft-primary" onclick="submitFormWithParam('add_more')">{{__('messages.Save and Add More')}}</button>
                                            <button type="submit" class="btn btn-primary">{{__('messages.Save')}}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                    @else
                        {{-- ====================================================
                             EDIT MODE: render the chosen link's form on the
                             page directly (no modal). Type is fixed for an
                             existing row; the only thing the user does here
                             is update the fields they care about and save.
                             ==================================================== --}}
                        <section class='text-gray-400'>
                            <h3 class="card-header"><i class="bi bi-journal-plus"></i> {{__('messages.Edit')}} {{__('messages.Block')}}</h3>
                            <div class='card-body'>
                                <form action="{{ route('addLink') }}" method="post" id="my-form">
                                    @method('POST')
                                    @csrf
                                    <input type='hidden' name='linkid' value="{{ $LinkID }}" />
                                    <input type='hidden' name='typename' value='{{ $typename }}' />

                                    <div id='link_params' class='col-lg-8'></div>

                                    <div class="d-flex align-items-center pt-4">
                                        <a class="btn btn-danger me-3" href="{{ url('studio/links') }}">{{__('messages.Cancel')}}</a>
                                        <button type="submit" class="btn btn-primary me-3">{{__('messages.Save')}}</button>
                                        <button type="button" class="btn btn-soft-primary me-3" onclick="submitFormWithParam('add_more')">{{__('messages.Save and Add More')}}</button>
                                    </div>
                                </form>
                            </div>
                        </section>
                    @endif

                   </div>
               </div>
            </div>
         </div>
        </div>
      </div>
    </div>

<script>
function submitFormWithParam(paramValue) {
    var form = document.getElementById("my-form");
    var paramField = document.createElement("input");
    paramField.setAttribute("type", "hidden");
    paramField.setAttribute("name", "param");
    paramField.setAttribute("value", paramValue);
    form.appendChild(paramField);
    form.submit();
}
</script>

@endsection

@push("sidebar-scripts")
<script>
$(function() {
    var linkId      = $("input[name='linkid']").val();
    var initialType = $("input[name='typename']").val();

    // Edit mode: load that block's fields immediately into the on-page form.
    if (linkId && linkId !== "0" && initialType) {
        LoadLinkTypeParams(initialType, linkId);
        return;
    }

    // Add mode: tile-click loads that block's fields into the modal,
    // then opens it. LinkStack ships Bootstrap 4.3.1 (assets/js/bootstrap.min.js),
    // so the modal API is jQuery's `.modal('show')` — not BS5's
    // bootstrap.Modal.getOrCreateInstance.
    $('.doSelectLinkType').on('click', function(e) {
        e.preventDefault();
        var typeId   = $(this).data('typeid');
        var typeName = $(this).data('typename');
        $("input[name='typename']").val(typeId);
        $("#selectedBlockName").text(typeName);
        LoadLinkTypeParams(typeId, "0");
        $('#addBlockModal').modal('show');
    });

    function LoadLinkTypeParams(typeId, currentLinkId) {
        var baseURL = <?php echo "\"" . url('') . "\""; ?>;
        $("#link_params").html('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>')
                         .load(baseURL + '/studio/linkparamform_part/' + typeId + '/' + currentLinkId);
        setTimeout(function() { document.dispatchEvent(new Event('contentLoaded')); }, 300);
    }
});
</script>
@endpush

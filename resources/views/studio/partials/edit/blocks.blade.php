<?php use App\Models\Button;

// Check if the LinkCount cookie is set
if (isset($_COOKIE['LinkCount'])) {
  // Set the expiration time of the cookie to one hour in the past
  setcookie('LinkCount', '', time() - 3600);
}

if(!function_exists('strp')){function strp($urlStrp){return str_replace(array('http://', 'https://'), '', $urlStrp);}}
?>
{{--
    Blocks tab — ported from /studio/links.blade.php.
    The blocks list (drag-to-reorder via #links-table-body, wired by the
    globally-loaded assets/js/main-dashboard.js) plus delete/edit/add.

    STEP 2 (this commit): Add / Edit still navigate to the existing
    /studio/add-link and /studio/edit-link/{id} pages as a working
    fallback. STEP 3 replaces those with an inline editor panel rendered
    right here (no redirect). The #mm-block-editor slot below is the
    placeholder that step 3 will populate.

    Dropped vs the standalone page: the outer .appearance-layout grid
    and the live-preview include — the unified shell owns both.
--}}

<style>
.sortable-handle {
    margin-right: 25px;
    width: 25px;
    height: auto;
    transform: rotate(90deg);
    cursor: grab;
    cursor: -webkit-grabbing;
    fill: currentColor;
}
.mm-blocks-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 1rem;
}
.mm-blocks-header > .btn { flex-shrink: 0; }
.delete{position:relative; color:transparent; background-color:tomato; border-radius:5px; left:5px; padding:5px 12px; cursor: pointer;}
.delete:hover{color:transparent;background-color:#f13d1d;}
</style>

<section class='text-gray-400'>
    <h3 class="card-header mm-blocks-header">
        <span><i class="bi bi-link-45deg"></i> My Blocks</span>
        <a class="btn btn-primary" href="{{ url('/studio/add-link') }}">Add new Block</a>
    </h3>

    <div>
        <div style="overflow-y: none;">

            <div id="links-table-body" data-page="{{request('page', 1)}}" data-per-page="{{$pagePage ? $pagePage : 0}}">
                @if($links->total() == 0)
                      <div class="col-6 text-center">
                        <p class="mt-5">{{__('messages.No Link Added')}}</p>
                      </div>
                @else
                @foreach($links as $link)
                @php $button = Button::find($link->button_id); if(isset($button->name)){$buttonName = $button->name;}else{$buttonName = 0;} @endphp
                @php if($buttonName == "default email"){$buttonName = "email";} if($buttonName == "default email_alt"){$buttonName = "email_alt";} @endphp
                @if($button && $button->name !== 'icon')
                <div class='row h-100 pb-0 mb-2 border rounded hvr-glow w-100' data-id="{{$link->id}}">
                    <div class="d-flex ">

                        <div class='col-auto p-2 my-auto mr-2' title="{{ $link->link }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="sortable-handle" viewBox="0 0 16 16">
                                <path d="M1 4a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V4zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V4zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V4zM1 9a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1V9zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V9zm5 0a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-2a1 1 0 0 1-1-1V9z"/>
                              </svg>
                        </div>

                    <div class='col h-100'>

                        <div class='row h-100'>
                            <div class='col-12 p-2' style="max-width:300px;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;" title="{{ $link->title }}">
                                <span class='h6'>
                                    @if($button->name == "custom_website")
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><img style="margin-bottom:3px;margin-left:4px;margin-right:4px;max-width:15px;max-height:15px;" alt="button-icon" class="icon hvr-icon" src="@if(file_exists(base_path("assets/favicon/icons/").localIcon($link->id))){{url('assets/favicon/icons/'.localIcon($link->id))}}@else{{getFavIcon($link->id)}}@endif" onerror="this.onerror=null; this.src='{{asset('assets/linkstack/icons/website.svg')}}';"></span>
                                    @elseif($button->name == "space")
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><i style="margin-left:2.83px;margin-right:-1px;color:#fff;" class='bi bi-distribute-vertical'>&nbsp;</i></span>
                                    @elseif($button->name == "heading")
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><i style="margin-left:2.83px;margin-right:-1px;color:#fff;" class='bi bi-card-heading'>&nbsp;</i></span>
                                    @elseif($button->name == "text")
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><i style="margin-left:2.83px;margin-right:-1px;color:#fff;" class='bi bi-fonts'>&nbsp;</i></span>
                                    @elseif($link->custom_icon && $link->type && $link->type !== 'predefined')
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><i style="width:20px;margin:1px;color:#fff;" class='fa {{$link->custom_icon}}'>&nbsp;</i></span>
                                    @else
                                    <span class="bg-soft-secondary" style="border: 1px solid #d0d4d7 !important;border-radius:5px;width:25px!important;height:25px!important;"><img style="max-width:15px !important;" alt="button-icon" height="15" class="m-1 " src="{{ asset('\/assets/linkstack/icons\/') . $buttonName }}.svg "></span>
                                    @endif

                                    @if($button->name == "space")
                                        Spacer ({{ (int) $link->title }} px)
                                    @else
                                        {{strip_tags($link->title,'')}}
                                    @endif
                                    </span>

                                @if(!empty($link->link) and $button->name != "vcard")
                                <br>
                                <a title='{{$link->link}}' href="{{ $link->link}}" target="_blank" class="d-none d-md-block ml-4 text-muted small">{{Str::limit($link->link, 75 )}}</a>
                                <a title='{{$link->link}}' href="{{ $link->link}}" target="_blank" class="d-md-none ml-4 text-muted small">{{Str::limit($link->link, 25 )}}</a>
                                @elseif(!empty($link->link) and $button->name == "vcard")
                                <br><a href="{{ url('vcard/'.$link->id) }}" target="_blank" class="ml-4 small">{{__('messages.Download')}}</a>
                                @endif

                            </div>

                            <div class='col' class="text-right">
                                {{Str::limit($link->params['text'] ?? null, 150)  }}

                                @if($link->typename == 'video')
                                    @php
                                        $embed = OEmbed::get($link->link);
                                        if ($embed && $embed->hasThumbnail()) {
                                            echo "<img style='max-height: 150px;' src='".$embed->thumbnailUrl()."' />";
                                        }
                                    @endphp
                                @endif
                            </div>

                            <div class='col-12 py-1 px-3 m-0 mt-2'>

                                @if(!empty($link->link))
                                <span><i class="bi bi-bar-chart-line"></i> {{ $link->click_number }} {{__('messages.Clicks')}}</span>
                                @endif

                                <a style="float: right;" href="{{ route('deleteLink', $link->id ) }}" onclick="return confirm('{{ __('messages.confirm_delete', ['title' => addslashes($link->title)]) }}')" class="btn btn-sm me-1 btn-icon btn-danger" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Delete" data-bs-placement="top" data-original-title="{{__('messages.Delete')}}">
                                    <span class="btn-inner">
                                       <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="currentColor">
                                          <path d="M19.3248 9.46826C19.3248 9.46826 18.7818 16.2033 18.4668 19.0403C18.3168 20.3953 17.4798 21.1893 16.1088 21.2143C13.4998 21.2613 10.8878 21.2643 8.27979 21.2093C6.96079 21.1823 6.13779 20.3783 5.99079 19.0473C5.67379 16.1853 5.13379 9.46826 5.13379 9.46826" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M20.708 6.23975H3.75" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          <path d="M17.4406 6.23973C16.6556 6.23973 15.9796 5.68473 15.8256 4.91573L15.5826 3.69973C15.4326 3.13873 14.9246 2.75073 14.3456 2.75073H10.1126C9.53358 2.75073 9.02558 3.13873 8.87558 3.69973L8.63258 4.91573C8.47858 5.68473 7.80258 6.23973 7.01758 6.23973" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                       </svg>
                                    </span>
                                 </a>

                                    <a style="float: right;" href="{{ route('editLink', $link->id ) }}" class="btn btn-sm me-1 btn-icon btn-warning" data-bs-toggle="tooltip" data-bs-placement="top" data-original-title="{{__('messages.Edit')}}" aria-label="Edit" data-bs-original-title="{{__('messages.Edit')}}">
                                       <span class="btn-inner">
                                          <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                             <path d="M11.4925 2.78906H7.75349C4.67849 2.78906 2.75049 4.96606 2.75049 8.04806V16.3621C2.75049 19.4441 4.66949 21.6211 7.75349 21.6211H16.5775C19.6625 21.6211 21.5815 19.4441 21.5815 16.3621V12.3341" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                             <path fill-rule="evenodd" clip-rule="evenodd" d="M8.82812 10.921L16.3011 3.44799C17.2321 2.51799 18.7411 2.51799 19.6721 3.44799L20.8891 4.66499C21.8201 5.59599 21.8201 7.10599 20.8891 8.03599L13.3801 15.545C12.9731 15.952 12.4211 16.181 11.8451 16.181H8.09912L8.19312 12.401C8.20712 11.845 8.43412 11.315 8.82812 10.921Z" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                             <path d="M15.1655 4.60254L19.7315 9.16854" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path>
                                          </svg>
                                       </span>
                                    </a>

                                @if(file_exists(base_path("assets/favicon/icons/").localIcon($link->id)))<a style="float: right;" href="{{ route('clearIcon', $link->id ) }}"  data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Add" data-bs-placement="top" data-original-title="Clear icon cache" class="float-right hvr-grow p-1 text-primary"><i style="-webkit-text-stroke:1px;padding-right:5px;" class="bi bi-arrow-repeat"></i></a>@endif

                            </div>
                        </div>
                    </div>
                </div>
                </div>
                @endif
                @endforeach
                @endif
            </div>

            <script type="text/javascript">
                const linksTableOrders = "{{ implode(' | ', $links->pluck('id')->toArray()) }}"
            </script>
        </div>

        <ul class="pagination justify-content-center">
            {!! $links ?? ''->links() !!}
        </ul>

        @if(count($links) > 3)<a class="btn btn-primary" href="{{ url('/studio/add-link') }}">Add new Block</a>@endif
    </div>

    {{-- STEP 3 inline-editor slot — intentionally empty for now. --}}
    <div id="mm-block-editor"></div>
</section>

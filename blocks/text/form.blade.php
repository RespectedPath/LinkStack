<label for='text' class='form-label'>{{__('messages.Text to display')}}</label>
<textarea class="form-control @if(env('ALLOW_USER_HTML') === true) ckeditor @endif" name="text" rows="6">{{ $title ?? '' }}</textarea>

{{-- Per-instance text alignment. Stored in type_params (non-column
     keys auto-route to JSON via UserController::saveLink). Default
     "center" matches the surrounding bio-page layout. --}}
<label for='alignment' class='form-label mt-3'>Alignment</label>
<select name='alignment' id='alignment' class='form-control' style='max-width: 220px;'>
    @php $savedAlign = $alignment ?? 'center'; @endphp
    <option value='left'   @if($savedAlign === 'left')   selected @endif>Left</option>
    <option value='center' @if($savedAlign === 'center') selected @endif>Center</option>
    <option value='right'  @if($savedAlign === 'right')  selected @endif>Right</option>
</select>
@if(env('ALLOW_USER_HTML') === true)
<script nonce="{{ csp_nonce() }}" src="{{ asset('assets/external-dependencies/ckeditor.js') }}"></script>
<script nonce="{{ csp_nonce() }}">
    ClassicEditor
        .create(document.querySelector('.ckeditor'), {
            toolbar: {
                items: [
                    'exportPDF', 'exportWord', '|',
                    'findAndReplace', 'selectAll', '|',
                    'heading', '|',
                    'bold', 'italic', 'strikethrough', 'underline', 'code', 'subscript', 'superscript', 'removeFormat', '|',
                    'bulletedList', 'numberedList', 'todoList', '|',
                    'outdent', 'indent', '|',
                    'undo', 'redo',
                    'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', 'highlight', '|',
                    'alignment', '|',
                    'link', 'blockQuote', '|',
                    'specialCharacters', 'horizontalLine', '|',
                    'textPartLanguage', '|',
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
        .catch(error => {
            console.error(error);
        });
</script>

@endif

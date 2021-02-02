@extends('layouts.app')
@section('styles')
    <link href="{{ asset('css/fixed-header.css') }}" rel="stylesheet">
@endsection



@section('content')
    <div class="box padding20">
        <h2 class="table">E-mails template</h2>
        <a href="{{ route('admin.emails')}}" class="label label-default">Voltar</a>
        <hr class="clear hr"/>
        @if(Request::is('*/editar'))
            {{ Form::model( $obj, ['method' => 'PATCH', 'route' => ['admin.emails.atualizar', $obj->id]] ) }}
        @else
            {{ Form::open(['url' => 'admin/emails/salvar']) }}
        @endif

        <div class="row">
            <div class="form-group col-md-5" >
                {{ Form::label('Projeto') }}
                {{ Form::select('fk_faculdade_id', $aFaculdades, (isset($obj->fk_faculdade_id) ? $obj->fk_faculdade_id : null), ['class' => 'form-control']) }}
            </div>
            <div class="form-group col-md-3">
                {{ Form::label('Assunto') }}
                {{ Form::input('text', 'assunto', null, ['class' => 'form-control', '', 'placeholder' => 'Assunto', 'maxlength' => 60]) }}
            </div>
            <div class="form-group col-md-4">
                {{ Form::label('E-mail de envio') }}
                {{ Form::input('email', 'emailfrom', null, ['class' => 'form-control', '', 'placeholder' => 'E-mail de envio']) }}
            </div>
        </div>
        <div class="row">
            <div class="col-md-8">
                <label for="mytextarea">Mensagem:</label>
                {!! Form::textarea('mytextarea', null, ['id' => 'mytextarea', 'rows' => 4, 'cols' => 54, 'style' => 'resize:none']) !!}
            </div>
            <div class="col-md-4">
                <div class="row">
                    <div class="col-md-12">
                        {{ Form::label('Tipo de e-mail:') }}
                        {{ Form::select('fk_tipo_email', $aTipoEmails, (isset($obj->fk_tipo_email) ? $obj->fk_tipo_email : null), ['class' => 'form-control']) }}
                    </div>
                    <div class="col-md-12">&nbsp;</div>
                    <div class="col-md-12">
                        <label for="">Listas de variáveis disponíveis</label>
                        <p class="small lead">Favor adicionar as variáveis entre <?php echo "{{  }}" ?></p>
                        <ul class="list-unstyled" id="variaveisEmail">
                            @if(!empty($variaveis))
                                @foreach($variaveis as $var)
                                    <li> <?php echo $var->titulo ?></li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="form-group col-md-12">
                {{ Form::submit('Salvar', ['class' => 'btn btn-primary']) }}
            </div>
        </div>
        {{ Form::close() }}
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/textcolor/plugin.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/emoticons/plugin.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/code/plugin.min.js" referrerpolicy="origin"></script>
    <script src="" referrerpolicy="origin"></script>
    <script src="" referrerpolicy="origin"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name="_token"]').attr('id', 'token');

            $('select[name="fk_tipo_email"]').change(function () {
                $('#mytextarea').val('html');
                if ($(this).val()) {

                    if ($('select[name="fk_faculdade_id"]').val() == '') {
                        alert('Selecione um Projeto primeiro');return;
                    }

                    $.ajax({
                        url: "{{ url('/admin/emails') }}/" + $('select[name="fk_faculdade_id"]').val() +'/'+ $(this).val() +'/variaveis',
                        ataType: "json"
                    }).done(function(data) {
                        $('#variaveisEmail').html('');
                        $.each(data.variaveis, function (index, element) {
                            const li = document.createElement('li');

                            $('#variaveisEmail').append(li);
                            li.innerHTML = element.titulo;
                        })

                    });
                }
            });
            var editor_config = {
                path_absolute : "{{ url('/') }}",
                document_base_url : "{{ url('/') }}",
                selector: "textarea",
                /* theme of the editor */
                theme: "modern",
                skin: "lightgray",

                /* width and height of the editor */
                width: "100%",
                height: 300,

                /* display statusbar */
                statubar: true,

                /* plugin */
                plugins: [
                    "advlist autolink link image lists charmap print preview hr anchor pagebreak",
                    "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
                    "save table contextmenu directionality emoticons template paste textcolor colorpicker textpattern"
                ],

                /* toolbar */
                toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | fontselect fontsizeselect link image | print preview media fullpage | forecolor backcolor emoticons",
                fontsize_formats: "8pt 10pt 12pt 14pt 18pt 24pt 36pt",
                theme_advanced_font_sizes : "8pt,10pt,12pt,14pt,16pt,18pt,20pt,22pt,24pt,26pt,28pt,30pt,32pt,34pt,36pt",
                /* style */
                style_formats: [
                    {title: "Headers", items: [
                            {title: "Header 1", format: "h1"},
                            {title: "Header 2", format: "h2"},
                            {title: "Header 3", format: "h3"},
                            {title: "Header 4", format: "h4"},
                            {title: "Header 5", format: "h5"},
                            {title: "Header 6", format: "h6"}
                        ]},
                    {title: "Inline", items: [
                            {title: "Bold", icon: "bold", format: "bold"},
                            {title: "Italic", icon: "italic", format: "italic"},
                            {title: "Underline", icon: "underline", format: "underline"},
                            {title: "Strikethrough", icon: "strikethrough", format: "strikethrough"},
                            {title: "Superscript", icon: "superscript", format: "superscript"},
                            {title: "Subscript", icon: "subscript", format: "subscript"},
                            {title: "Code", icon: "code", format: "code"}
                        ]},
                    {title: "Blocks", items: [
                            {title: "Paragraph", format: "p"},
                            {title: "Blockquote", format: "blockquote"},
                            {title: "Div", format: "div"},
                            {title: "Pre", format: "pre"}
                        ]},
                    {title: "Alignment", items: [
                            {title: "Left", icon: "alignleft", format: "alignleft"},
                            {title: "Center", icon: "aligncenter", format: "aligncenter"},
                            {title: "Right", icon: "alignright", format: "alignright"},
                            {title: "Justify", icon: "alignjustify", format: "alignjustify"}
                        ]}
                ],
                relative_urls: false,
                remove_script_host: false,
                convert_urls: false,
                file_browser_callback : function(field_name, url, type, win) {
                    var x = window.innerWidth || document.documentElement.clientWidth || document.getElementsByTagName('body')[0].clientWidth;
                    var y = window.innerHeight|| document.documentElement.clientHeight|| document.getElementsByTagName('body')[0].clientHeight;

                    var cmsURL = editor_config.path_absolute + '/laravel-filemanager?field_name=' + field_name+'&lang=en';
                    if (type == 'image') {
                        cmsURL = cmsURL + "&type=Images";
                    } else {
                        cmsURL = cmsURL + "&type=Files";
                    }

                    tinyMCE.activeEditor.windowManager.open({
                        file : cmsURL,
                        title : 'Filemanager',
                        width : x * 0.8,
                        height : y * 0.8,
                        resizable : "yes",
                        close_previous : "no"
                    });
                }
            };

            tinymce.init(editor_config);
        });
    </script>
@endpush

@extends('layouts.app')
@section('styles')
    <link href="{{ asset('css/fixed-header.css') }}" rel="stylesheet">
@endsection

@section('content')
    <div class="box padding20">
        <div class="col-md-9"><h2 class="table">E-mails template</h2></div>
        <div class="col-md-3" style="margin-top: 20px;">
            <a href="{{ route('admin.emails.incluir') }}"
               class="btn btn-success right margin-bottom-10">Adicionar</a>
        </div>
        <hr class="clear hr"/>
        <div class="row"></div>

        @if(count($emails) > 0)
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">Lista de registros encontrados</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-striped dataTable">
                        <thead>
                        <tr>
                            <th>Projeto</th>
                            <th>Tipo</th>
                            <th>Assunto</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($emails as $email)
                            <tr>
                                <td>{{ $email->faculdade->fantasia }}</td>
                                <td>{{ $email->tipo->titulo }}</td>
                                <td>{{ $email->assunto }}</td>
                                <td style="text-align: center">
                                    <a href="/admin/emails/{{ $email->id }}/editar"
                                       class="btn btn-default btn-sm" title="Editar"><i
                                            class="fa fa-fw fa-edit"></i></a>
                                    {{ Form::open(['method' => 'DELETE', 'route' => ['admin.emails.deletar', $email->id], 'style' => 'display:inline;']) }}
                                    <button type="submit" class="btn btn-danger btn-sm" title="Excluir" onclick="return confirm('Deseja realmente excluir?')">
                                        <i class="fa fa-fw fa-trash"></i>
                                    </button>
                                    {{ Form::close() }}

                                    {{ Form::open(['method' => 'POST', 'route' => ['admin.emails.clonar', $email->id], 'style' => 'display:inline;']) }}
                                        {{ Form::select('fk_faculdade_id', $aFaculdades, null, ['class' => 'form-control']) }}
                                        <button type="submit" class="btn btn-default btn-sm">Clonar</button>
                                    {{ Form::close() }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <hr class="clear hr"/>
            <div class="row">
                <div class="alert alert-info">Nenhum registro no banco!</div>
            </div>
        @endif

    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/tinymce.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/textcolor/plugin.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/emoticons/plugin.min.js" referrerpolicy="origin"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/4.9.5/plugins/code/plugin.min.js" referrerpolicy="origin"></script>
    <script src="" referrerpolicy="origin"></script>
    <script src="" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            /* replace textarea having class .tinymce with tinymce editor */
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
                "save table contextmenu directionality emoticons template paste textcolor"
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
            ]
        });
    </script>
@endpush

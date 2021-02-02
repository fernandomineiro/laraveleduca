<div class="padding20">
    <div class="row">
        <div class="form-group col-md-10">
            {{Form::label('Tags do Aluno') }} <small>*</small>
            {{Form::select('selectCursosFaculdade', $objTags, [],
                [
                    'multiple' => 'multiple', 'id' => 'selectedTags', 'name' => 'selectedTags',
                    'class' => 'form-control myselect', 'allowClear' => true,
                    'data-placeholder' => 'Tags do Aluno'
                ]
            )}}
        </div>
        <div class="col-md-2">
            <button class="btn btn-primary" style="margin-top: 23px;" onclick="inserirTags(this)">Adicionar Tags</button>
        </div>
    </div>

    <br>
    <h2>Lista de Tags</h2>
    <hr>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>ID</th>
            <th>Tag</th>
            <th style="width: 10%;text-align: center;">Ações</th>
        </tr>
        </thead>
        <tbody>
        @foreach($objTagsAluno as $tag)
            <tr>
                <td>{{ $tag->tagsAluno->id }}</td>
                <td>{{ $tag->tagsAluno->tag }}</td>
                <td style="width: 10%;text-align: center;">
                    <button class="btn btn-danger" onclick="deletarTag({{ $tag->id }}, this)">Deletar</button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

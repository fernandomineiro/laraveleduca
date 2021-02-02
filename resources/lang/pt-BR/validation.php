<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted'             => ':attribute deve ser aceito.',
    'active_url'           => ':attribute não é uma URL válida.',
    'after'                => ':attribute deve ser uma data depois de :date.',
    'after_or_equal'       => ':attribute deve ser uma data posterior ou igual a :date.',
    'alpha'                => ':attribute deve conter somente letras.',
    'alpha_dash'           => ':attribute deve conter letras, números e traços.',
    'alpha_num'            => ':attribute deve conter somente letras e números.',
    'array'                => ':attribute deve ser um array.',
    'before'               => ':attribute deve ser uma data antes de :date.',
    'before_or_equal'      => ':attribute deve ser uma data anterior ou igual a :date.',
    'between'              => [
        'numeric' => ':attribute deve estar entre :min e :max.',
        'file'    => ':attribute deve estar entre :min e :max kilobytes.',
        'string'  => ':attribute deve estar entre :min e :max caracteres.',
        'array'   => ':attribute deve ter entre :min e :max itens.',
    ],
    'boolean'              => ':attribute deve ser verdadeiro ou falso.',
    'confirmed'            => 'A confirmação de :attribute não confere.',
    'date'                 => ':attribute não é uma data válida.',
    'date_format'          => ':attribute não confere com o formato :format.',
    'different'            => ':attribute e :other devem ser diferentes.',
    'digits'               => ':attribute deve ter :digits dígitos.',
    'digits_between'       => ':attribute deve ter entre :min e :max dígitos.',
    'dimensions'           => ':attribute tem dimensões de imagem inválidas.',
    'distinct'             => ':attribute tem um valor duplicado.',
    'email'                => ':attribute deve ser um endereço de e-mail válido.',
    'exists'               => ':attribute selecionado é inválido.',
    'file'                 => ':attribute deve ser um arquivo.',
    'filled'               => ':attribute é um campo obrigatório.',
    'image'                => ':attribute deve ser uma imagem.',
    'in'                   => ':attribute é inválido.',
    'in_array'             => ':attribute não existe em :other.',
    'integer'              => ':attribute deve ser um inteiro.',
    'ip'                   => ':attribute deve ser um endereço IP válido.',
    'json'                 => ':attribute deve ser um JSON válido.',
    'max'                  => [
        'numeric' => ':attribute não deve ser maior que :max.',
        'file'    => ':attribute não deve ter mais que :max kilobytes.',
        'string'  => ':attribute não deve ter mais que :max caracteres.',
        'array'   => ':attribute não deve ter mais que :max itens.',
    ],
    'mimes'                => ':attribute deve ser um arquivo do tipo: :values.',
    'mimetypes'            => ':attribute deve ser um arquivo do tipo: :values.',
    'min'                  => [
        'numeric' => ':attribute deve ser no mínimo :min.',
        'file'    => ':attribute deve ter no mínimo :min kilobytes.',
        'string'  => ':attribute deve ter no mínimo :min caracteres.',
        'array'   => ':attribute deve ter no mínimo :min itens.',
    ],
    'not_in'               => 'O :attribute selecionado é inválido.',
    'numeric'              => ':attribute deve ser um número.',
    'present'              => 'O campo :attribute deve ser presente.',
    'regex'                => 'O formato de :attribute é inválido.',
    'required'             => 'O campo :attribute é obrigatório.',
    'required_if'          => 'O campo :attribute é obrigatório quando :other é :value.',
    'required_unless'      => 'O :attribute é necessário a menos que :other esteja em :values.',
    'required_with'        => 'O campo :attribute é obrigatório quando :values está presente.',
    'required_with_all'    => 'O campo :attribute é obrigatório quando :values estão presentes.',
    'required_without'     => 'O campo :attribute é obrigatório quando :values não está presente.',
    'required_without_all' => 'O campo :attribute é obrigatório quando nenhum destes estão presentes: :values.',
    'same'                 => ':attribute e :other devem ser iguais.',
    'size'                 => [
        'numeric' => ':attribute deve ser :size.',
        'file'    => ':attribute deve ter :size kilobytes.',
        'string'  => ':attribute deve ter :size caracteres.',
        'array'   => ':attribute deve conter :size itens.',
    ],
    'string'               => ':attribute deve ser uma string',
    'timezone'             => ':attribute deve ser uma timezone válida.',
    'unique'               => ':attribute já está em uso.',
    'uploaded'             => ':attribute falhou ao ser enviado.',
    'url'                  => 'O formato de :attribute é inválido.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap attribute place-holders
    | with something more reader friendly such as E-Mail Address instead
    | of "email". This simply helps us make messages a little cleaner.
    |
    */

    'attributes' => [
        'titulo'                                => 'Título',
        'local'                                 => 'Local',
        'Data'                                  => 'Data',
        'descricao'                             => 'Descrição',
        'data_inicio'                           => 'Data Inicial',
        'data_final'                            => 'Data Final',
        'hora_inicio'                           => 'Hora Inicial',
        'hora_final'                            => 'Hora Final',
        'data_inicio.after_or_equal'            => 'A data de início precisa ser igual ou maior que hoje.',
        'data_final.after_or_equal'             => 'A data final deve ser maior ou igual a data de início.',
        'data_inicio.date'                      => 'A data de início precisa ser uma data válida',
        'nome_curso'                            => 'Nome do curso',
        'fk_parceiro'                           => 'Informe o parceiro',
        'fk_projeto'                            => 'Informe o projeto',
        'fk_produtora'                          => 'Informe a produtora',
        'nome.required'                         => 'Nome é obrigatório',
        'sobre_nome.required'                   => 'Sobrenome é obrigatório',
        'data_nascimento.required'              => 'Data de nascimento é obrigatório!',

        // 'share.required' => 'Share é um campo obrigatório!',
        'cep.required'                          => 'Cep é obrigatório!',
        'logradouro.required'                   => 'Logradouro é obrigatório!',
        'numero.required'                       => 'Número é obrigatório!',
        'fk_estado_id.required'                 => 'Estado é obrigatório!',
        'fk_cidade_id.required'                 => 'Cidade é obrigatório!',
        'bairro.required'                       => 'Bairro é um campo obrigatório!',
        'curso_superior.required'               => 'Curso Superior é um campo obrigatório!',
        'telefone_1.required'                   => 'Telefone Fixo é um campo obrigatório!',
        'telefone_2.required'                   => 'Telefone Celular é um campo obrigatório!',
        'cpf.required'                          => 'CPF é obrigatório',
        'cpf.unique'                            => 'CPF já cadastrado no sistema!',
        'fk_usuario_id'                         => 'Usuário do Aluno é obrigatório',
        'fk_faculdade_id.required'              => 'Projeto é obrigatório',
        'fk_endereco_id'                        => 'Endereço é obrigatório',
        'password.min'                          => 'A senha deve ter no mínimo 8 caracteres!',
        'password.confirmed'                    => 'A senha e a confirmação de senha devem ser iguais!',
        'password.required'                     => 'A senha é obrigatória!',
        'password_confirmation.min'             => 'A confirmação de senha deve ter no mínimo 8 caracteres!',
        'password_confirmation.required'        => 'A confirmação de senha é obrigatória!',
        'email.unique'                          => 'E-mail já cadastrado no sistema!',
        'email.required'                        => 'E-mail é obrigatório!',
        'tipo'                                  => 'Tipo',
        'url'                                   => 'URL',
        'params'                                => 'Parametros',
        'fk_faculdade'                          => 'Faculdade',
        'status'                                => 'Status',
        'fk_faculdade.gt'                       => 'Selecione uma Faculdade válida.',
        'fk_tipo_assinatura.not_in'             => 'O tipo de assinatura é um campo obrigatório',
        'status.required'                       => 'O status da assinatura é um campo obrigatório',
        'titulo.required'                       => 'O título da assinatura é um campo obrigatório',
        'valor.required'                        => 'O valor da assinatura é um campo obrigatório',
        'valor_de.required'                     => 'O valor de venda assinatura é um campo obrigatório',
        'tipo_periodo.not_in'                   => 'O tipo de plano da assinatura é um campo obrigatório',
        'fk_assinatura_faculdade.required_if'   => 'O projeto da assinatura é um campo obrigatório caso o tipo de assinatura FULL',
        'fk_tipo_assinatura.required'           => 'Tipo assinatura',
        'fk_trilha'                             => 'Trilha',
        'qtd_cursos'                            => 'Quantidade de Cursos',
        'periodo_em_dias'                       => 'Periodo renovação Cursos (em dias)',
        'status'                                => 'Status',
        'numero'                                => 'Número',
        'valor_de.required'                     => 'Valor (De)',
        'valor.required'                        => 'Valor',
        'fk_certificado'                        => 'Certificado',
        'fk_faculdade'                          => 'Projeto/IES',
        'fk_faculdade_id'                       => 'Projeto',
        'tipo_periodo.required'                 => 'Periodo',
        'fk_assinatura'                         => 'Assinatura',
        'fk_conteudo'                           => 'Curso',
        'assinatura'                            => 'Status',
        'fk_curso.required'                     => 'Curso é obrigatório',
        'fk_faculdade.required'                 => 'Projeto é obrigatório',
        'nome_aluno.required'                   => 'O nome do aluno é obrigatório',
        'email_aluno.required'                  => 'O email do aluno é obrigatório',
        'slug'                                  => 'Slug',
        'fk_curso'                              => 'Curso',
        'data_conclusao'                        => 'Data conclusão',
        'fk_usuario'                            => 'Usuário',
        'layout'                                => 'Layout do Certificado',
    		'fk_criador_id'                         => 'Usuário Inclusão',
    		'fk_atualizador_id'                     => 'Usuário Alteração',
    		'data_criacao'                          => 'Data Inclusão',
    		'data_atualizacao'                      => 'Data alteração',
    		'status'                                => 'Status',
        'descricao_cidade'                      => 'Cidade',
        'fk_estado_id'                          => 'Estado',
        'dominio'                               => 'Título',
        'categoria'                             => 'Categoria',
        'posicao'                               => 'Posição',
        'pagina_trilha_conhecimento'            => 'Página de Trilha de Conhecimento',
        'titulo.required' => 'Título do Curso é obrigatório',
        'fk_professor.required' => 'Professor do Curso é obrigatório',
        'titulo.unique' => 'Curso Título deve ser unico por faculdade',
        'fk_cursos_tipo.required' => 'Tipo do Curso é obrigatório',
        'duracao_dias.numeric' => 'A duração em dia deve ser um número inteiro',
        'disponibilidade_dias.numeric' => 'A disponibilidade para venda em dias deve ser um número inteiro',
        'numero_maximo_alunos.numeric' => 'O número máximo de alunos deve ser um número inteiro',
        'numero_minimo_alunos.numeric' => 'O número mínimo de alunos deve ser um número inteiro',
    ],

];

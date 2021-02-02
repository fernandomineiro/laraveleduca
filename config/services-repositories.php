<?php

return [
    [
        'bind' => 'App\Services\CidadeService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\CidadeRepository',
            'injectDependecy' => 'App\Cidade',
        ]
    ],
    [
        'bind' => 'App\Services\EstadoService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\EstadoRepository',
            'injectDependecy' => 'App\Estado',
        ]
    ],
    [
        'bind' => 'App\Services\FaculdadeService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\FaculdadeRepository',
            'injectDependecy' => 'App\Faculdade',
        ]
    ],
    [
        'bind' => 'App\Services\CursoService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\CursoRepository',
            'injectDependecy' => 'App\Curso',
        ]
    ],
    [
        'bind' => 'App\Services\SemestreService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\SemestreRepository',
            'injectDependecy' => '',
        ]
    ],
    [
        'bind' => 'App\Services\GeneroService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\GeneroRepository',
            'injectDependecy' => '',
        ]
    ],
    [
        'bind' => 'App\Services\UsuarioService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\UsuarioRepository',
            'injectDependecy' => 'App\Usuario',
        ]
    ],
    [
        'bind' => 'App\Services\EnderecoService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\EnderecoRepository',
            'injectDependecy' => 'App\Endereco',
        ]
    ],
    [
        'bind' => 'App\Services\AlunoService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\AlunoRepository',
            'injectDependecy' => 'App\Aluno',
        ]
    ],
    [
        'bind' => 'App\Services\ProfessorService',
        'injectDependecy' => [
            'bind' => 'App\Repositories\ProfessorRepository',
            'injectDependecy' => 'App\Professor', 
        ]
    ],
];

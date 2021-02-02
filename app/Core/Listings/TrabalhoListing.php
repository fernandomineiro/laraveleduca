<?php

namespace App\Core\Listings;

use Illuminate\Support\Facades\DB;

class TrabalhoListing extends Listing
{
    public function availableColumns() 
    {
        return [
            'trabalho_id' => 'cursos_trabalhos.id',
            'trabalho_titulo' => 'cursos_trabalhos.titulo', 
            'curso_id' => 'cursos.id',
            'curso_titulo' => 'cursos.titulo',
            'curso_data_criacao' => 'cursos.data_criacao', 
            'tipo_titulo' => 'cursos_tipo.titulo'
        ];
    }

    public function buildQuery()
    {
        $query = DB::table('cursos_trabalhos')
            ->join('cursos', 'cursos_trabalhos.fk_cursos', '=', 'cursos.id')
            ->join('cursos_tipo', 'cursos.fk_cursos_tipo', '=', 'cursos_tipo.id')
            ->join('conclusao_cursos_faculdades', 'cursos.id', '=' , 'conclusao_cursos_faculdades.fk_curso')
            ->whereNotNull('conclusao_cursos_faculdades.nota_trabalho')
            ->whereExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('cursos_trabalhos_usuario')
                    ->where('cursos_trabalhos_usuario.fk_cursos_trabalhos', DB::raw('cursos_trabalhos.id'));

                if ($this->hasFilter('data_envio') && $this->getFilter('data_envio')) {
                    $query->whereDate('cursos_trabalhos_usuario.data_criacao', $this->getFilter('data_envio'));
                }
                
                if ($this->hasFilter('status') && is_bool($this->getFilter('status')) && $this->getFilter('status')) {
                    $query->whereNotNull('cursos_trabalhos_usuario.nota');
                } elseif ($this->hasFilter('status') && is_bool($this->getFilter('status')) && !$this->getFilter('status')) {
                    $query->whereNull('cursos_trabalhos_usuario.nota');
                }
            });

        // o usuário logado é um usuário normal, necessário buscar o professor linkado ao usuário logado para fazer a busca
        if ($this->getFilter('perfil_id') == 2) {
            $query->join('cursos_faculdades', 'cursos.id', '=', 'cursos_faculdades.fk_curso')
                ->where('cursos_faculdades.fk_faculdade', $this->getFilter('faculdade_id'));
        } else {
            $query->where('cursos.fk_professor', $this->getFilter('professor_id'))
                ->whereNotNull('conclusao_cursos_faculdades.nota_trabalho');
        }

        if ($this->hasFilter('curso_id') && $this->getFilter('curso_id')) {
            $query->where('cursos_trabalhos.fk_cursos', $this->getFilter('curso_id'));
        }

        return $query;
    }
}

<?php

namespace App\Core\Listings;

use Illuminate\Support\Facades\DB;

class EnvioTrabalhoListing extends Listing 
{
    public function availableColumns()
    {
        return [
            'id' => 'cursos_trabalhos_usuario.id',
            'student' => 'usuarios.nome',
            'studentId' => 'usuarios.id',
            'grade' => 'cursos_trabalhos_usuario.nota',
            'downloadPath' => 'cursos_trabalhos_usuario.downloadPath',
            'filename' => 'cursos_trabalhos_usuario.filename',
            'data_envio' => 'cursos_trabalhos_usuario.data_criacao'
        ];
    }

    public function buildQuery()
    {
        $query = DB::table('cursos_trabalhos_usuario')
            ->join('usuarios', 'cursos_trabalhos_usuario.fk_usuario', 'usuarios.id')
            ->where('cursos_trabalhos_usuario.fk_cursos_trabalhos', $this->getFilter('trabalho_id'));

        if ($this->hasFilter('status') && is_bool($this->getFilter('status')) && $this->getFilter('status')) {
            $query->whereNotNull('cursos_trabalhos_usuario.nota');
        } elseif ($this->hasFilter('status') && is_bool($this->getFilter('status')) && !$this->getFilter('status')) {
            $query->whereNull('cursos_trabalhos_usuario.nota');
        }

        if ($this->hasFilter('data_envio') && $this->getFilter('data_envio')) {
            $query->whereDate('cursos_trabalhos_usuario.data_criacao', $this->getFilter('data_envio'));
        }

        return $query;
    }
}

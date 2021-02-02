<?php

namespace App\Services;

use App\EstruturaCurricular;
use App\EstruturaCurricularConteudo;
use App\EstruturaCurricularFaculdade;
use App\Repositories\EstruturaCurricularRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use function foo\func;

class EstruturaCurricularService {

    protected $estruturaCurricularRepository;
    
    public function __construct() {
        $this->estruturaCurricularRepository = (new EstruturaCurricularRepository(new EstruturaCurricular()));
    }

    /**
     * @param $idEstruturaCurricular
     * @return mixed
     */
    public function retornaIdsProjetosEstruturaCurricular($idEstruturaCurricular) {
        return $this->estruturaCurricularRepository
                ->retornaIdsProjetosEstruturaCurricular($idEstruturaCurricular);
    }

    /**
     * @param $idEstruturaCurricular
     * @return array
     */
    public function listarCursosNaoAdicionadosNaEstruturaCurricular($idEstruturaCurricular) {
        return $this->estruturaCurricularRepository
                ->listarCursosNaoAdicionadosNaEstruturaCurricular($idEstruturaCurricular);
    }

    /**
     * @param $idEstruturaCurricular
     * @return array
     */
    public function listarCursosAdicionadosNaEstruturaCurricular($idEstruturaCurricular) {
        return $this->estruturaCurricularRepository
            ->listarCursosAdicionadosNaEstruturaCurricular($idEstruturaCurricular);
    }

    public function salvar(array $data) {
        
        $estruturaCurricular = $this->estruturaCurricularRepository->model->create($data);
        $this->inserirEstruturaCurricularConteudo(Arr::get($data, 'fk_curso'), $estruturaCurricular);
        $this->inserirEstruturaCurricularFaculdade(Arr::get($data, 'faculdades'), $estruturaCurricular);
        return $estruturaCurricular;
    }

    public function atualizar(array $data, $id) {
        $estruturaCurricular = $this->estruturaCurricularRepository->model->findOrFail($id);

        $estruturaCurricular->update($data);
        $this->inserirEstruturaCurricularConteudo(Arr::get($data, 'fk_curso'), $estruturaCurricular);
        $this->inserirEstruturaCurricularFaculdade(Arr::get($data, 'faculdades'), $estruturaCurricular);
        
        return $estruturaCurricular;
    }

    /**
     * @param array $cursos
     * @param EstruturaCurricular $estruturaCurricular
     */
    private function inserirEstruturaCurricularConteudo($cursos, EstruturaCurricular $estruturaCurricular): void {
        EstruturaCurricularConteudo::where('fk_estrutura', $estruturaCurricular->id)->delete();
        collect($cursos)->each(function ($curso) use ($estruturaCurricular) {
            EstruturaCurricularConteudo::updateOrCreate([
                'fk_conteudo' => $curso['id'],
                'fk_estrutura' => $estruturaCurricular->id,
                'fk_categoria' => $curso['fk_categoria']
            ], [
                'fk_conteudo' => $curso['id'],
                'fk_estrutura' => $estruturaCurricular->id,
                'ordem' => $curso['ordem'],
                'fk_categoria' => $curso['fk_categoria'],
                'modalidade' => $curso['modalidade'],
                'data_inicio' => $this->tratarData(Arr::get($curso, 'data_inicio'))
            ]);
        });
    }

    /**
     * @param int $idFaculdade
     * @param EstruturaCurricular $estruturaCurricular
     */
    private function inserirEstruturaCurricularFaculdade($faculdades, EstruturaCurricular $estruturaCurricular): void {
        EstruturaCurricularFaculdade::where('fk_estrutura', $estruturaCurricular->id)->delete();
        collect($faculdades)->each(function ($idFaculdade) use ($estruturaCurricular) {
            EstruturaCurricularFaculdade::updateOrCreate(
                [
                    'fk_estrutura' => $estruturaCurricular->id, 
                    'fk_faculdade' => $idFaculdade
                ], [
                'fk_estrutura' => $estruturaCurricular->id,
                'fk_faculdade' => $idFaculdade,
                'status' => 1,
            ]);
        });
    }

    /**
     * @param $data
     * @return \DateTime|false
     */
    private function tratarData($data) {
        $dateTime = \DateTime::createFromFormat('d/m/Y', $data);
        return !empty($dateTime) ? $dateTime->format('Y-m-d') : null;
    }
}

<?php

namespace App\Services;

use App\Curso;
use App\CursoCategoria;
use App\EstruturaCurricular;
use App\Repositories\CursoCategoriaRepository;
use App\Repositories\CursoRepository;
use App\Repositories\EstruturaCurricularRepository;
use App\Usuario;
use Illuminate\Database\Eloquent\Collection;

class ItvService {

    protected $idFaculdade = 6;
    protected $idCurso;

    /**
     * @param mixed $idCurso
     */
    public function setIdCurso($idCurso) {
        $this->idCurso = $idCurso;
        return $this;
    }

    /**
     * @param $idFaculdade
     */
    public function setIdFaculdade(int $idFaculdade) {
        $this->idFaculdade = $idFaculdade;
        return $this;
    }

    /**
     * @param Usuario $user
     * @return array
     */
    public function retornarCursosAlunoItv(Usuario $user) {

        $estruturas = $this->retornarEstruturasCurricularesUsuario($user);

        $categorias = (new CursoCategoriaRepository(new CursoCategoria()))->retornarCategoriasAtivas();

        $cursosCategoria = [];
        /** @var EstruturaCurricular $estrutura */
        foreach ($estruturas as $estrutura) {

            $estruturaCollection = $this->inicializarCollectionEstrutura($estrutura);

            /** @var CursoCategoria $categoria */
            foreach ($categorias as $categoria) {
                
                $listaCursos = $this->listarCursosItv($categoria, $user, $estrutura);
                if (!empty($listaCursos)) {
                    $estruturaCollection->get('categorias')
                        ->add($this->inicialiarCollectionCategoria($categoria, $listaCursos));
                }
            }
            array_push($cursosCategoria, $estruturaCollection);
        }
        
        return $cursosCategoria;
    }

    /**
     * @param Usuario $user
     * @return mixed
     */
    public function retornarEstruturasCurricularesUsuario(Usuario $user) {
        return (new EstruturaCurricularRepository(new EstruturaCurricular()))
                    ->retornaEstruturasCurricularesUsuario($user->id);
    }

    /**
     * @param EstruturaCurricular $estrutura
     * @return \Illuminate\Support\Collection
     */
    public function inicializarCollectionEstrutura(EstruturaCurricular $estrutura): \Illuminate\Support\Collection {
        $estruturaCollection = collect($estrutura);
        $estruturaCollection->put('categorias', collect([]));
        
        return $estruturaCollection;
    }

    /**
     * @param CursoCategoria $categoria
     * @param array $listaCursos
     * @return \Illuminate\Support\Collection
     */
    public function inicialiarCollectionCategoria(
        CursoCategoria $categoria,
        array $listaCursos
    ): \Illuminate\Support\Collection {
        $categoria = collect($categoria);
        $categoria->put('cursos', $listaCursos);
        return $categoria;
    }

    /**
     * @param CursoCategoria $categoria
     * @param Usuario $user
     * @param EstruturaCurricular $estrutura
     * @return array
     */
    public function listarCursosItv(CursoCategoria $categoria, Usuario $user, EstruturaCurricular $estrutura): array {
        return (new CursoRepository(new Curso()))
                    ->setStatus(5)
                    ->setIdFaculdade($this->idFaculdade)
                    ->setIdCategoria($categoria->id)
                    ->setIdCurso($this->idCurso)
                    ->listaITV($user->getId(), $estrutura->id);
    }

    /**
     * @param Usuario $user
     * @return array
     */
    public function retornarCalendarioItv(Usuario $user) {
        $cursos = (new CursoRepository(new Curso()))
                        ->setStatus(5)
                        ->setIdFaculdade($this->idFaculdade)
                        ->listaITV($user->getId());
        
        return (new Collection($cursos))->transform(function ($curso) {
                return [
                    'id' => $curso['id'],
                    'subject' => $curso['nome_curso'],
                    'startTime' => $curso['data_inicio'],
                    'endTime' => $curso['data_inicio'],
                ];
            })->all();
    }
    
}

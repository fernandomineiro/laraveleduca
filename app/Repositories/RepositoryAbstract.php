<?php

namespace App\Repositories;

use App\Aluno;
use Illuminate\Database\Eloquent\Model;

abstract class RepositoryAbstract {

    /** @var Model */
    public $model;

    public function __construct(Model $model) {
        $this->model = $model;
    }

    /**
     * @return mixed
     */
    public function getAll() {
        return $this->model->where('status', 1)->get();
    }

    /**
     * @param $id
     * @return mixed
     */
    public function load($id) {
        return $this->model = $this->model->find($id);
    }

    public function save(array $data) {
        $this->model->fill($data);
        $this->model->save();
        return  $this->model->fresh();
    }
}

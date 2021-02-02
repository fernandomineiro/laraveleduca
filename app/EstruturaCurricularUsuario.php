<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\hasOne;

class EstruturaCurricularUsuario extends Model {

    protected $fillable = [
        'fk_estrutura',
        'fk_usuario',
    ];

    protected $primaryKey = 'id';
    protected $table = "estrutura_curricular_usuario";
    public $timestamps = false;

    /**
     *
     * @return hasOne
     */
    public function estrutura() {
        return $this->hasOne('App\EstruturaCurricular', 'id', 'fk_estrutura');
    }
}

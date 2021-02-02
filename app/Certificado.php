<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Certificado extends Model
{
    protected $table = 'certificados';
    protected $fillable = ['fk_curso', 'data_conclusao', 'fk_usuario', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'criacao', 'atualizacao', 'status', 'fk_estrutura'];
    public $timestamps = false;
    public $rules = [
        'fk_curso' => 'required',
        'data_conclusao' => 'required',
        'fk_usuario' => 'required'
    ];

    public $messages = [
        'fk_curso' => 'Curso',
        'data_conclusao' => 'Data conclusão',
        'fk_usuario' => 'Usuário',
    ];

    public function scopeActive($query) {
        return $query->where('certificados.status', 1);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function usuario() {
        return $this->belongsTo(Usuario::class, 'fk_usuario');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function curso() {
        return $this->belongsTo(Curso::class, 'fk_curso');
    }

    public function estrutura() {
        return $this->belongsTo(EstruturaCurricular::class, 'fk_estrutura');
    }

    public function path() {
        return url('/files/certificado/emitidos/'.$this->downloadPath);
    }
}

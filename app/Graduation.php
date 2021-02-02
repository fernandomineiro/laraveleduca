<?php

namespace App;

use App\Models\BaseModel;

class Graduation extends BaseModel
{
    protected $table = 'graduation';

    protected $fillable = [
        'title',
        'description',
        'public',
        'teaser',
        'language',
        'formater',
        'profile_id',
        'course_id',
        'start_date',
        'final_date',
    ];



    public function Curso()
    {
        return $this->belongsTo(Curso::class);
    }

    public function CursoCategoria()
    {
        return $this->belongsTo(CursoCategoria::class);
    }

    public function CursoTipo()
    {
        return $this->hasOne(CursoTipo::class);
    }

    public function Cursovalor()
    {
        return $this->belongsTo(CursoValor::class);
    }

    public function CursoCategoriaCurso()
    {
        return $this->belongsTo(CursoCategoriaCurso::class);
    }

    public function Cursotag()
    {
        return $this->belongsTo(CursoTag::class);
    }

    public function CursoFaculdade()
    {
        return $this->belongsTo(CursosFaculdades::class);
    }

    public function Faculdade()
    {
        return $this->hasOne(Faculdade::class);
    }

    public function Professor()
    {
        return $this->hasOne(Professor::class);
    }

    public function Produtora()
    {
        return $this->belongsTo(Produtora::class);
    }

    public function Parceiro()
    {
        return $this->belongsTo(Parceiro::class);
    }

    public function Curador()
    {
        return $this->belongsTo(Curador::class);
    }

    public function turmas()
    {
        return $this->hasMany(CursoTurma::class);
    }

    public function valor()
    {
        return $this->hasOne(CursoValor::class);
    }

    public function CertificadoLayout()
    {
        return $this->belongsTo(CertificadoLayout::class);
    }
}



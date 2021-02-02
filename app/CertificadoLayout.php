<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class CertificadoLayout extends Model
{
    protected $table = 'certificado_layout';
	protected $fillable = [ 'layout', 'fk_faculdade', 'fk_criador_id', 'fk_atualizador_id', 'data_criacao', 'data_atualizacao', 'status', 'tipo', 'titulo' ];
	public $timestamps = false;
	public $rules = [
		'layout' => 'required',
		'tipo' => 'required',
        'fk_faculdade' => 'required|numeric|min:0|not_in:0',
		'fk_criador_id' => 'required',
		'fk_atualizador_id' => 'required',
		'data_criacao' => 'required',
		'data_atualizacao' => 'required',
		'titulo' => 'required',
		'status' => 'required',
	];

    public $messages = [
        'layout' => 'Layout do Certificado',
        'tipo' => 'Tipo de Certificado',
        'fk_faculdade' => 'Projeto',
        'fk_criador_id' => 'Usuário Inclusão',
        'fk_atualizador_id' => 'Usuário Alteração',
        'data_criacao' => 'Data Inclusão',
        'data_atualizacao' => 'Data alteração',
        'titulo' => 'Informe o título',
        'status' => 'Status',

    ];


}

<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class FluxoAprovacaoConteudo extends Model
{
    protected $table = 'fluxo_aprovacao_conteudo';
	protected $fillable = [ 
		'tipo_conteudo', 'fk_conteudo', 'fk_usuario_id', 'data_criacao', 'motivo', 'status',
	];
	public $timestamps = false;
	public $rules = [
		'tipo_conteudo' => 'required',
		'fk_conteudo' => 'required',
		'fk_usuario_id' => 'required',
		'status' => 'required',
		'data_criacao' => 'required',
	];
	
	public $messages = [
		'tipo_conteudo' => 'Tipo do Conteúdo',
		'conteudo' => 'Conteúdo',
		'fk_usuario_id' => 'Usuário',
		'status' => 'Status',
		'data_criacao' => 'Data'		
	];	
}
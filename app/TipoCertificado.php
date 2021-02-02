<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class TipoCertificado extends Model
{
    protected $table = 'tipo_certificado';
	protected $fillable = [ 'status', 'titulo' ];	
	public $timestamps = false;
	public $rules = [
		'titulo' => 'required',
		'status' => 'required',
	];
	public $messages = [
		'titulo' => 'Informe o título',
		'status' => 'Status',
	];	

}
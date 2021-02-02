<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;

use App\Estado;
use App\Cidade;
use Illuminate\Support\Str;

class EnderecoController extends Controller {
    
    public function carregaCidade(Request $request)
    {
        $dados = $request->all();

        $estado = Estado::select('*')->where('uf_estado', strtoupper($dados['uf']))
                                     ->first();

        $cidades = Cidade::select('*')->where('fk_estado_id', $estado->id)
                                      ->get();

        $retorno = '<select class="form-control" name="fk_cidade_id">';

        $retorno .= '<option value="">Selecione uma cidade!</option>';
        foreach ($cidades as $key => $campo) {
            if (strtoupper($this->__sanitizeString($campo['descricao_cidade'])) == strtoupper($this->__sanitizeString($dados['cidade']))) {
                $retorno .= '<option value="' . $campo['id'] . '" selected="true">' . Str::title($campo['descricao_cidade']) . '</option>';
            } else {
                $retorno .= '<option value="' . $campo['id'] . '">' . Str::title($campo['descricao_cidade']) . '</option>';
            }
        }
        $retorno .= '</select>';

        echo $retorno;
        exit;
    }

    private function __sanitizeString($string) {
        $what = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ó','Ú','ñ','Ñ','ç','Ç');
        $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','U','n','n','c','C');
        return str_replace($what, $by, $string);
    }

    public function carregaEstado($uf)
    {
        $estados = Estado::select('*')->get();
        $estado = Estado::select('*')->where('uf_estado', strtoupper($uf))->first();

        $retorno = '<select class="form-control" name="fk_estado_id" onchange="carregaCidades($(this).val());">';

        $retorno .= '<option value="">Selecione um estado!</option>';
        foreach ($estados as $key => $campo) {
            if ($estado->id == $campo['id']) {
                $retorno .= '<option value="' . $campo['id'] . '" selected="true">' . Str::title($campo['descricao_estado']) . '</option>';
            } else {
                $retorno .= '<option value="' . $campo['id'] . '">' . Str::title($campo['descricao_estado']) . '</option>';
            }
        }
        $retorno .= '</select>';

        echo $retorno;
        exit;
    } 
    
    public function carregaCidades($idEstado)
    {
        $cidades = Cidade::select('id', 'descricao_cidade')->where('fk_estado_id', $idEstado)->get()->toArray();
        $retorno = '<select class="form-control" name="fk_cidade_id">';

        $retorno .= '<option value="">Selecione uma cidade!</option>';
        foreach($cidades as $key => $campo) {
            $retorno .= '<option value="'.$campo['id'].'">' . Str::title($campo['descricao_cidade']) . '</option>';
        }
        $retorno .= '</select>';

        echo $retorno;
        exit;
    }    
}

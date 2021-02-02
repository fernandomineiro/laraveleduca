<?php

namespace App\Http\Controllers\Admin;

use App\ConfiguracoesVariaveis;

use Illuminate\Support\Facades\DB;
use ScssPhp\ScssPhp\Compiler;
use ScssPhp\ScssPhp\Formatter\Compact;
use ScssPhp\ScssPhp\Formatter\Compressed;
use ScssPhp\ScssPhp\Formatter\Crunched;
use ScssPhp\ScssPhp\Formatter\Debug;
use ScssPhp\ScssPhp\Formatter\Expanded;
use ScssPhp\ScssPhp\Formatter\Nested;

class DynamicScssPhpController {

    public function index($idFaculdade) {
//        echo '<pre>';
        try {
            $dir = public_path(). '/frontcss/';

            $scss = new Compiler();
            $scss->setFormatter(Crunched::class);

            $scss->addImportPath(function($path) use ($dir) {
                if (!file_exists($dir.$path)) {
                    return null;
                };
                return $dir.$path;
            });

            $variaveis = ConfiguracoesVariaveis::where('editavel', 0)->get();

            $data = [];
            foreach ($variaveis as $variavel) {
                $data[$variavel->nome] = $variavel->default;
            }

            $estilosVariaveis = DB::select("select configuracoes_variaveis.nome, configuracoes_estilos_variaveis.value
                                from configuracoes_estilos_variaveis
                            join configuracoes_estilos on configuracoes_estilos_variaveis.fk_configuracoes_estilos_id  = configuracoes_estilos.id
                            join configuracoes_variaveis on configuracoes_estilos_variaveis.fk_configuracoes_variaveis_id = configuracoes_variaveis.id

                            where configuracoes_estilos.fk_faculdade_id = {$idFaculdade}
                            AND configuracoes_estilos.status = 1");

            if (empty($estilosVariaveis)) {
                throw new \Exception('Faculdade não tem estilos criados ainda!');
            }

            foreach ($estilosVariaveis as $variavel) {
                $data[$variavel->nome] = $variavel->value;
            }


            $scss->setVariables($data);

            $scss_string = file_get_contents($dir.'/style.scss');
            $compiledScss =  $scss->compile($scss_string);
            echo $compiledScss;
            if (!is_dir($dir.'/'.$idFaculdade)) {
                @mkdir($dir.'/'.$idFaculdade, 0777);
            }

            if (file_exists($dir.'/'.$idFaculdade.'/style.css')) {
                unlink($dir.'/'.$idFaculdade.'/style.css');
            }

            file_put_contents($dir.'/'.$idFaculdade.'/style.css', $compiledScss);

            return true;
        } catch (\Exception $error) {
            throw $error;
        }
    }
}

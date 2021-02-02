<?php

namespace App\Http\Controllers;

use App\ConfiguracaoEmailsFaculdade;
use App\ConfiguracoesBanners;
use App\ConfiguracoesEstilos;
use App\ConfiguracoesEstilosVariaveis;
use App\ConfiguracoesHomeFooterHeader;
use App\ConfiguracoesLogotipos;
use App\ConfiguracoesPaginas;
use App\ConfiguracoesParceiros;
use App\ConfiguracoesPixels;
use App\ConfiguracoesPolitica;
use App\ConfiguracoesRedesSociais;
use App\ConfiguracoesSac;
use App\ConfiguracoesTermo;
use App\ConfiguracoesTiposCursosAtivos;
use App\ConfiguracoesVariaveis;
use App\Faculdade;
use App\Http\Controllers\Admin\DynamicScssPhpController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Class ConfiguracaoControllers
 * Classe de suporte para beckend e API para configurações de diversas plataformas!
 * EDUCACIONAL
 * CORPORATIVA
 * MENTORIA
 * @package App\Http\Controllers
 */
class ConfigPerfilEducazController extends Controller{

    /**
     * Retorna a lista de faculdades para serem selecionadadas -> PLUCK
     * @return \Illuminate\Support\Collection
     */
    public function GetAllFaculdades(){

        return Faculdade::all()->where('status', '=', 1)->pluck('razao_social', 'id');

    }

    /**
     * Index com base no nome da rota para exibir o array com o conteúdo
     * @param array $params
     * @return array
     */
    public function GetListConfiguracoes(array $params = []) {

        $moduloConfig = explode('.', Route::currentRouteName())[1];

        switch ($moduloConfig) {
            case 'configuracoestiposcursosativos':
                return ConfiguracoesTiposCursosAtivos::select('configuracoes_tipos_cursos_ativos.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_tipos_cursos_ativos.fk_faculdade_id')
                    ->where('configuracoes_tipos_cursos_ativos.status', 1)
                    ->get();
                break;
            case 'configuracoesbanners':

                $banners = ConfiguracoesBanners::select('configuracoes_banners.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_banners.fk_faculdade_id')
                    ->where('configuracoes_banners.status', 1);

                if (!empty($params['fk_faculdade_id'])) {
                    $banners->where('fk_faculdade_id', '=', $params['fk_faculdade_id']);
                }

                if (!empty($params['titulo'])) {
                    $banners->where('titulo', 'like', '%'.$params['titulo'].'%');
                }

                if (!empty($params['pagina'])) {
                    $banners->where('pagina',  '=', $params['pagina']);
                }

                return $banners->get();
                break;
            case 'configuracoesemailsfaculdades':

                $config = ConfiguracaoEmailsFaculdade::select('configuracoes_emails_faculdade.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_emails_faculdade.fk_faculdade_id')
                    ->where('configuracoes_emails_faculdade.status', 1);

                if (!empty($params['fk_faculdade_id'])) {
                    $config->where('fk_faculdade_id', '=', $params['fk_faculdade_id']);
                }

                if (!empty($params['nome'])) {
                    $config->where('nome', 'like', '%'.$params['nome'].'%');
                }

                return $config->get();
                break;
            case 'configuracoespolitica':
                return ConfiguracoesPolitica::select('configuracoes_politica.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_politica.fk_faculdade_id')
                    ->where('configuracoes_politica.status', 1)
                    ->get();
                break;
            case 'configuracoespaginas':
                return ConfiguracoesPaginas::select('configuracoes_paginas.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_paginas.fk_faculdade_id')
                    ->where('configuracoes_paginas.status', 1)
                    ->get();
                break;
            case 'configuracoestermo':
                return ConfiguracoesTermo::select('configuracoes_termo.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_termo.fk_faculdade_id')
                    ->where('configuracoes_termo.status', 1)
                    ->get();
                break;
            case 'configuracoesheaderhomefooter':
                return ConfiguracoesHomeFooterHeader::select('configuracoes_home_footer_header.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_home_footer_header.fk_faculdade_id')
                    ->where('configuracoes_home_footer_header.status', 1)
                    ->get();
                break;
            case 'configuracoeslogotipos':
                return ConfiguracoesLogotipos::select('configuracoes_logotipos.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_logotipos.fk_faculdade_id')
                    ->where('configuracoes_logotipos.status', 1)
                    ->get();
                break;
            case 'configuracoesredessociais':
                return ConfiguracoesRedesSociais::select('configuracoes_redes_sociais.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_redes_sociais.fk_faculdade_id')
                    ->where('configuracoes_redes_sociais.status', 1)
                    ->get();
                break;
            case 'configuracoessac':
                return ConfiguracoesSac::select('configuracoes_sac.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_sac.fk_faculdade_id')
                    ->where('configuracoes_sac.status', 1)
                    ->get();
                break;
            case 'configuracoesestilos':
                return ConfiguracoesEstilos::select('configuracoes_estilos.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_estilos.fk_faculdade_id')
                    ->where('configuracoes_estilos.status', 1)
                    ->get();
                break;

            case 'configuracoespixels':
                return ConfiguracoesPixels::select('configuracoes_pixel.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_pixel.fk_faculdade_id')
                    ->where('configuracoes_pixel.status', 1)
                    ->get();
                break;

            case 'configuracoesparceiros':
                return ConfiguracoesParceiros::select('configuracoes_parceiros.*', 'faculdades.razao_social')
                    ->join('faculdades', 'faculdades.id', 'configuracoes_parceiros.fk_faculdade_id')
                    ->where('configuracoes_parceiros.status', 1)
                    ->get();
                break;


            default:
                return array();
        }
    }

    /**
     * Recebe a request e processa o salvamento do dado, verificando se o ID = 0 para novo,
     * e se for maior Localiza o registro e atualiza.
     * Método otimizado!
     * @param $obj
     * @return int
     */
    public function salvarRegistro(Request $request, $id, $delete = false){

        $moduloConfig = $this->getRouterByUri();

        $data = array();
        $data = $request->except('_token');

        if ($id > 0)
            $obj = $this->GetObjConfiguracao($id);

        switch ($moduloConfig) {

            case 'configuracoestiposcursosativos':
                if ($id == 0)
                    $obj = new ConfiguracoesTiposCursosAtivos();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhuma configuração.';
                        return -3;
                    }
                }
                if (!$delete){
                    if ($id == 0){
                        if ($this->localizarRegistroExistente('ativar_cursos_online', $data['ativar_cursos_online'], $obj, $obj['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A título (' . $data['titulo'] . ') já existe no banco.';
                            return -4;
                        }
                    }
                }

                $file = $request->file('banner_lateral');
                if ($request->hasFile('banner_lateral') && $file->isValid()) {

//                    $dimensionsBanner = 'dimensions:width=1920,height=500';
//                    $sMessage = '1920x500px';
//                    if ($data['pagina'] == 'home') {
//                        $dimensionsBanner = 'dimensions:width=1920,height=800';
//                        $sMessage = '1920x800px';
//                    }
//
//                    $dimensions = getimagesize($request->file('banner_url'));
//                    $this->validate($request, [
//                        'banner_url' => $dimensionsBanner
//                    ], [
//                        'banner_url.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
//                    ]);

                    $ext = explode('.', $file->getClientOriginalName());

                    $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($file->move('files/banners/', $fileName)) {
                        $data['banner_lateral'] = $fileName;
                    }
                }

                $file = $request->file('banner_lateral');
                if ($request->hasFile('banner_lateral') && $file->isValid()) {

//                    $dimensionsBanner = 'dimensions:width=1920,height=500';
//                    $sMessage = '1920x500px';
//                    if ($data['pagina'] == 'home') {
//                        $dimensionsBanner = 'dimensions:width=1920,height=800';
//                        $sMessage = '1920x800px';
//                    }
//
//                    $dimensions = getimagesize($request->file('banner_url'));
//                    $this->validate($request, [
//                        'banner_url' => $dimensionsBanner
//                    ], [
//                        'banner_url.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
//                    ]);

                    $ext = explode('.', $file->getClientOriginalName());

                    $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($file->move('files/banners/', $fileName)) {
                        $data['banner_lateral'] = $fileName;
                    }
                }

                $fileBannerCentral = $request->file('banner_central');
                if ($request->hasFile('banner_central') && $fileBannerCentral->isValid()) {

                    $sMessage = '823x442px';

                    $dimensions = getimagesize($request->file('banner_central'));
                    /*$this->validate($request, [
                        'banner_central' => 'dimensions:width=823,height=442'
                    ], [
                        'banner_central.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
                    ]);*/

                    $ext = explode('.', $fileBannerCentral->getClientOriginalName());

                    $fileName = sha1(date('Y-m-d') . '_' . $fileBannerCentral->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($fileBannerCentral->move('files/banners/', $fileName)) {
                        $data['banner_central'] = $fileName;
                    }
                }

                $favicon = $request->file('favicon');
                if ($request->hasFile('favicon') && $favicon->isValid()) {
                    $request->validate([
                        'favicon' => 'mimes:ico',
                    ], [
                        'favicon.mimes' => 'Arquivo precisa ser do tipo ico'
                    ]);

                    $fileName = 'favicon.ico';
                    $favicon->move('favicons/'.$request->get('fk_faculdade_id').'/', $fileName);
                }

                break;

            case 'configuracoesredessociais':
                if ($id == 0)
                    $obj = new ConfiguracoesRedesSociais();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhuma rede social.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('titulo', $data['titulo'], $obj, $obj['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A título (' . $data['titulo'] . ') já existe no banco.';
                            return -4;
                        }
                break;

            case 'configuracoesemailsfaculdades':
                if ($id == 0)
                    $obj = new ConfiguracaoEmailsFaculdade();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhuma configuração.';
                        return -3;
                    }
                }
                if (!$delete) {
                    $data['conta'] = $data['email'];
                    $data['smtp_server'] = env('MAIL_HOST');
                    $data['smtp_port'] = env('MAIL_PORT');
                    $data['smtp_ssl_tls'] = env('MAIL_ENCRYPTION');

                    if ($id == 0)
                        if ($this->localizarRegistroExistente('email', $data['email'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': O E-Mail (' . $data['email'] . ') já existe no banco.';
                            return -4;
                        }
                }
                break;
            case 'configuracoesestilos':

                if ($id == 0)
                    $obj = new ConfiguracoesEstilos();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum estilo personalizado.';
                        return -3;
                    }
                }
                if (!$delete) {
                    $data['slug'] = Str::slug($data['descricao'], '_');
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': O slug (' . $data['slug'] . ') já existe no banco.';
                            return -4;
                        }
                }

                break;

            case 'configuracoespixels':
                if ($id == 0)
                    $obj = new ConfiguracoesPixels();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum pixel.';
                        return -3;
                    }
                }
                // TODO: check if we need to prevent duplicates, if not, remove below lines
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('pixel', $data['pixel'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': O pixel (' . $data['pixel'] . ') já existe no banco.';
                            return -4;
                        }
                break;

            case 'configuracoespolitica':
                if ($id == 0)
                    $obj = new ConfiguracoesPolitica();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhuma Politica.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A SLUG (' . $data['slug'] . ') já existe no banco.';
                            return -4;
                        }
                break;

            case 'configuracoespaginas':

                if ($id == 0)
                    $obj = new ConfiguracoesPaginas();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhuma Pagina.';
                        return -3;
                    }
                }
                if (!$delete) {
                    if ($id == 0) {
                        if ($this->localizarRegistroExistente('fk_faculdade_id', $data['fk_faculdade_id'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': As páginas para esta Faculdade já existe no banco.';
                            return -4;
                        }
                    }
                }

                break;

            case 'configuracoestermo':
                if ($id == 0)
                    $obj = new ConfiguracoesTermo();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum Termo.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A SLUG (' . $data['slug'] . ') já existe no banco.';
                            return -4;
                        }
                break;
            case 'configuracoessac':
                if ($id == 0)
                    $obj = new ConfiguracoesSac();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum SAC.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('descricao', $data['descricao'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A descrição do SAC (' . $data['descricao'] . ') já existe no banco.';
                            return -4;
                        }
                break;
            case 'configuracoesheaderhomefooter':
                if ($id == 0)
                    $obj = new ConfiguracoesHomeFooterHeader();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum: Home, Header ou Footer.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': o Slug (' . $data['slug'] . ') já existe no banco.';
                            return -4;
                        }
                break;
            case 'configuracoeslogotipos':
                $file = $request->file('url_logtipo');
                if ($request->hasFile('url_logtipo') && $file->isValid()) {

                    $dimensions = getimagesize($request->file('url_logtipo'));
                    $this->validate($request, [
                        'url_logtipo' => 'dimensions:width=280,height=75'
                    ], [
                        'url_logtipo.dimensions' => 'As dimensões da imagem deverá ser 280x75px. Sua imagem tem as dimensões de '. $dimensions[3]
                    ]);

                    $ext = explode('.', $file->getClientOriginalName());

                    $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($file->move('files/logotipos/', $fileName)) {
                        $data['url_logtipo'] = $fileName;
                    }
                } else {
                    if ($id == 0)
                        return 8;
                }

                if ($id == 0)
                    $obj = new ConfiguracoesLogotipos();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum Logotipo.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A slug do Logotipo (' . $data['slug'] . ') já existe no banco.';
                            return -4;
                        }
                //var_dump($data);die();
                break;
            case 'configuracoesparceiros':
                $file = $request->file('imagem');
                if ($request->hasFile('imagem') && $file->isValid()) {
                    $ext = explode('.', $file->getClientOriginalName());

                    $dimensions = getimagesize($request->file('imagem'));
                    $this->validate($request, [
                        'imagem' => 'dimensions:width=150,height=75'
                    ], [
                        'imagem.dimensions' => 'As dimensões da imagem deverá ser 150x75px. Sua imagem tem as dimensões de '. $dimensions[3]
                    ]);

                    $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($file->move('files/parceiros/', $fileName)) {
                        $data['imagem'] = $fileName;
                    }
                } else {
                    if ($id == 0)
                        return 8;
                }

                if ($id == 0)
                    $obj = new ConfiguracoesParceiros();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum parceiro.';
                        return -3;
                    }
                }
                if (!$delete)
                    if ($id == 0)
                        if ($this->localizarRegistroExistente('descricao', $data['descricao'], $obj, $data['fk_faculdade_id']) > 0) {
                            $this->msgErroRegistroExiste .= ': A slug do Logotipo (' . $data['descricao'] . ') já existe no banco.';
                            return -4;
                        }
                //var_dump($data);die();
                break;

            case 'configuracoesbanners':

                $file = $request->file('banner_url');
                if ($request->hasFile('banner_url') && $file->isValid()) {

                    $dimensionsBanner = 'dimensions:width=1920,height=500';
                    $sMessage = '1920x500px';
                    if ($data['pagina'] == 'home') {
                        $dimensionsBanner = 'dimensions:width=1920,height=800';
                        $sMessage = '1920x800px';
                    }

                    $dimensions = getimagesize($request->file('banner_url'));
                    $this->validate($request, [
                        'banner_url' => $dimensionsBanner
                    ], [
                        'banner_url.dimensions' => 'As dimensões da imagem deverá ser '.$sMessage.'. Sua imagem tem as dimensões de '. $dimensions[3]
                    ]);

                    $ext = explode('.', $file->getClientOriginalName());

                    $fileName = sha1(date('Y-m-d') . '_' . $file->getClientOriginalName());
                    $fileName .= '.' . $ext[count($ext) - 1];

                    if ($file->move('files/banners/', $fileName)) {
                        $data['banner_url'] = $fileName;
                    }
                } else {
                    if ($id == 0)
                        return 8;
                }


                if ($id == 0)
                    $obj = new ConfiguracoesBanners();
                else {
                    if ($obj == null) {
                        $this->msgRegistroInexistente .= ': Este ID fornecido não está vinculado a nenhum Banner.';
                        return -3;
                    }
                }

                if (isset($data['data_inicio'])) {
                    if (trim($data['data_inicio'])) {
                        $data['data_inicio'] = $this->transformaDataSql($data['data_inicio']);
                        if (isset($data['hora_inicio']))
                            $data['data_inicio'] .= ' ' . trim(str_replace(' ', '', $data['hora_inicio'])) . ':00';
                        else
                            $data['data_inicio'] .= ' 00:00:00';

                        $data['data_hora_inicio'] = $data['data_inicio'];
                        unset($data['data_inicio']);
                    }
                }

                if (isset($data['data_final'])) {
                    if (trim($data['data_final']) != '') {
                        $data['data_final'] = $this->transformaDataSql($data['data_final']);
                        if (isset($data['hora_inicio']))
                            $data['data_final'] .= ' ' . trim(str_replace(' ', '', $data['hora_final'])) . ':59';
                        else
                            $data['data_final'] .= ' 23:59:59';

                        $data['data_hora_termino'] = $data['data_final'];
                        unset($data['data_final']);
                    }
                }

//                if (!$delete)
//                    if ($id == 0)
//                        if ($this->localizarRegistroExistente('slug', $data['slug'], $obj, $data['fk_faculdade_id']) > 0) {
//                            $this->msgErroRegistroExiste .= ': o Slug (' . $data['slug'] . ') já existe no banco.';
//                            return -4;
//                        }
                break;
            default:
                return -2;
        }

        $validator = Validator::make($request->all(), $obj->rules, $obj->messages);

        if (!$validator) {
            $this->validatorMsg = $validator;
            return -1;
        }

        if ($moduloConfig == 'configuracoesestilos') {

            if ($delete) {
                return $obj->update(['status' => 0]) ? 6 : 7;
            }

            $dataVariaveis = [
                "cardBackgroundColor" => $data['cardBackgroundColor'],
//                "adicionadosRecentementeColor" => $data['adicionadosRecentementeColor'],
//                "orangeDarkBackground" => $data['orangeDarkBackground'],
                "orangeLightBackground" => $data['orangeLightBackground'],
                "yellowBackColor" => $data['yellowBackColor'],
                "yellowDarkBackground" => $data['yellowDarkBackground'],
                "yellowLightBackground" => $data['yellowLightBackground'],
                "footerBackgroundColor" => $data['footerBackgroundColor'],
                "defaultColorTheme" => $data['defaultColorTheme'],
                "bodyColor" => $data['bodyColor'],
                "linksColor" => $data['linksColor'],
                "headerH2Color" => $data['headerH2Color'],
//                "categoryMobileBackColor" => $data['categoryMobileBackColor'],
                "navBarResponsiveColor" => $data['navBarResponsiveColor'],
                "navBarResponsiveDropdownMenuColor" => $data['navBarResponsiveDropdownMenuColor'],
                "shopCartIconColor" => $data['shopCartIconColor'],
                "userIconColor" => $data['userIconColor'],
                "sidebarColoredLinks" => $data['sidebarColoredLinks'],
                "corMenuAtivo" => $data['corMenuAtivo'],
                "corTitulo" => $data['corTitulo'],
            ];

            $data = [
                "fk_faculdade_id" => $data['fk_faculdade_id'],
                "slug" => Str::slug($data['descricao'], '_'),
                "descricao" => $data['descricao'],
//                "categoria" => $data['categoria'],
//                "url" => $data['url'],
//                "css_personalizado" => $data['css_personalizado'],
            ];

            $data = $this->insertAuditData($data,  empty($obj['id']));

            $obj->fill($data);
            $obj->save();

//            dd($obj);
            if (!$obj) {
                return $obj['id'] === 0 ? 2 : 5;
            }

            $dataVariaveis = array_filter($dataVariaveis);

            foreach ($dataVariaveis as $key => $value) {
                $variable = ConfiguracoesVariaveis::where('nome', '=', $key)->first();
                $variableStyle = ConfiguracoesEstilosVariaveis::firstOrCreate(
                    ['fk_configuracoes_estilos_id' => $obj->id, 'fk_configuracoes_variaveis_id' => $variable->id]
                );
                $variableStyle->value = $value;
                $variableStyle->save();
            }

            $scss = new DynamicScssPhpController();
            $scss->index($obj->fk_faculdade_id);

            return $id === 0 ? 1 : 4;
        }

        if ($obj['id'] == 0) {
            $data = $this->insertAuditData($data, true);
            return $obj->create($data) ? 1 : 2;
        } else {
            $data = $this->insertAuditData($data, false);
            if (!$delete) {
                return $obj->update($data) ? 4 : 5;
            } else {
                $data['status'] = 0;
                return $obj->update($data) ? 6 : 7;
            }
        }
    }

    /**
     * Localiza se um registro existe, evitando que seja inserido mais de 1x
     * @param $field
     * @param $value
     * @param $obj
     * @return int
     */
    private function localizarRegistroExistente($field, $value, $obj, $faculdadeId)
    {
        $objTb = DB::table($obj->getTable())
            ->where($field, $value)
            ->where('fk_faculdade_id', $faculdadeId)
            ->where('status', '!=', 0)
            ->first();
        if ($objTb) return 1;
        return 0;
    }

    /**
     * Verifica se a opçao é válida para o módulo de configuração
     * @return mixed
     */
    public function getRouterByUri(){

        $urlData = explode('/', str_replace('/admin/', '', \Request::getRequestUri()))[0];
        $data = DB::table('vw_perfil_x_modulos_acoes')->where('uri', $urlData)->first();
        return $data->rota;

    }

    /**
     * Obtendo classe para edição
     * @param $id
     * @return |null
     */
    public function GetObjConfiguracao($id)
    {
        $moduloConfig = $this->getRouterByUri();

        switch ($moduloConfig) {
            case 'configuracoestiposcursosativos':
                return ConfiguracoesTiposCursosAtivos::findOrFail($id);
                break;
            case 'configuracoesbanners':
                return ConfiguracoesBanners::findOrFail($id);
                break;
            case 'configuracoesemailsfaculdades':
                return ConfiguracaoEmailsFaculdade::findOrFail($id);
                break;
            case 'configuracoespolitica':
                return ConfiguracoesPolitica::findOrFail($id);
            case 'configuracoespaginas':
                return ConfiguracoesPaginas::findOrFail($id);
                break;
            case 'configuracoestermo':
                return ConfiguracoesTermo::findOrFail($id);
                break;
            case 'configuracoesheaderhomefooter':
                return ConfiguracoesHomeFooterHeader::findOrFail($id);
                break;
            case 'configuracoeslogotipos':
                return ConfiguracoesLogotipos::findOrFail($id);
                break;
            case 'configuracoesredessociais':
                return ConfiguracoesRedesSociais::findOrFail($id);
                break;
            case 'configuracoessac':
                return ConfiguracoesSac::findOrFail($id);
                break;
            case 'configuracoesestilos':
                return ConfiguracoesEstilos::findOrFail($id);
                break;
            case 'configuracoespixels':
                return ConfiguracoesPixels::findOrFail($id);
                break;
            case 'configuracoesparceiros':
                return ConfiguracoesParceiros::findOrFail($id);
                break;

            default:
                return null;
        }
    }

}

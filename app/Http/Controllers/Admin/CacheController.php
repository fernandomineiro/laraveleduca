<?php
/**
 * Created by PhpStorm.
 * User: gabrielresende
 * Date: 27/04/2020
 * Time: 18:49
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;

class CacheController extends Controller {

    public function index() {

        $run = Artisan::call('config:clear');
        $run = Artisan::call('route:clear');
        $run = Artisan::call('cache:clear');
        $run = Artisan::call('config:cache');
        echo 'Cache limpo com sucesso';
        die;
    }

}
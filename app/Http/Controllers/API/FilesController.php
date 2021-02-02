<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;

class FilesController extends Controller {

    public function index($path) {

        if (File::exists('files/'.$path)) {
            return '/files/'.$path;
        }

        return '/files/az.png';
    }
}

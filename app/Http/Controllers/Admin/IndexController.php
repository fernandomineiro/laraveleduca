<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redirect;

class IndexController extends Controller
{

	public function index()
    {
		if(!Auth()->guard('admin')->check()) {
			return redirect()->route('admin.login');
		}
    }
}

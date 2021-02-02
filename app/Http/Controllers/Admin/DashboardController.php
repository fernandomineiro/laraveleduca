<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        if(!Auth()->guard('admin')->check()) {
            return redirect()->route('admin.login');
        }

        if (!$this->validateAccess(\Session::get('user.logged'))) return redirect()->route($this->redirecTo);

        return view('dashboard.index', $this->arrayViewData);
    }
}

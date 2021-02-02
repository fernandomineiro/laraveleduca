<?php

namespace App\Http\Controllers\Charts;

use Illuminate\Http\Request;
use App\Charts\UserChart;
use App\Usuario;
use App\Http\Controllers\Controller;

class UserChartController extends Controller
{
    public function index()
    {
        $todos_user = Usuario::where('created_at');
        
        $usersChart = new UserChart;
        $usersChart->labels(['Jan', 'Feb', 'Mar']);
        $usersChart->dataset('Users by trimester', 'line', [10, 25, 13] , [$todos_user])
           ->color("rgb(255, 99, 132)");
       return view('charts.chartusers', [ 'usersChart' => $usersChart ] );
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
        //
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }
}

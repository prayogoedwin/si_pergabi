<?php

namespace App\Http\Controllers;

use App\Helpers\Area;
use Illuminate\View\View;

class AreaController extends Controller
{
    public function index(): View
    {
        return view('area.index', [
            'areas' => Area::all(),
        ]);
    }
}

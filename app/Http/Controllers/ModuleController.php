<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class ModuleController extends Controller
{
    public function comingSoon(): View
    {
        $moduleName = (string) request()->segment(1);

        return view('coming-soon', [
            'module' => ucfirst($moduleName),
        ]);
    }
}

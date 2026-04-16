<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $films = Film::query()
            ->with('localisations')
            ->orderBy('name')
            ->get();

        return view('home', ['films' => $films]);
    }
}

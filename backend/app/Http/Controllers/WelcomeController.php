<?php

namespace App\Http\Controllers;

use App\Models\Career;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        try {
            $featuredCareers = Career::take(6)->get();
            $careerCount = Career::count();
        } catch (QueryException) {
            $featuredCareers = Collection::make();
            $careerCount = 0;
        }

        return view('welcome', compact('featuredCareers', 'careerCount'));
    }
}

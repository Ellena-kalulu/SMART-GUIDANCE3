<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function setLocale(Request $request): RedirectResponse
    {
        $locale = $request->validate([
            'locale' => ['required', 'in:en,ny,tum'],
        ])['locale'];

        session(['locale' => $locale]);

        return back()->with('success', __('app.language_changed'));
    }
}

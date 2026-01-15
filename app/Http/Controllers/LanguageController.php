<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    /**
     * Switch the application language.
     *
     * @param  string  $locale
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switch($locale)
    {
        // Validate locale
        if (!in_array($locale, ['en', 'id'])) {
            $locale = 'en';
        }

        // Store in session
        session(['locale' => $locale]);

        // Redirect back
        return redirect()->back();
    }
}

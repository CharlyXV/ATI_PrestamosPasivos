<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class LanguageController extends Controller
{
    public function changeLanguage($lang)
    {
        App::setLocale($lang);
        session(['locale' => $lang]);
        return redirect()->back();
    }
}

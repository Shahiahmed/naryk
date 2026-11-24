<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class UrpaqKapitalyController extends Controller
{
    public function __invoke(): View
    {
        $companies = [
            ['ticker' => 'KZTO', 'name' => 'ҚазТрансОйл', 'rate' => 10.2, 'badge' => 'KTO', 'color' => '#0b5cab', 'defaultSelected' => false],
            ['ticker' => 'KEGC', 'name' => 'KEGOC', 'rate' => 15.4, 'badge' => 'KEG', 'color' => '#1d4ed8', 'defaultSelected' => false],
            ['ticker' => 'KZTK', 'name' => 'Қазақтелеком', 'rate' => 5.7, 'badge' => 'KT', 'color' => '#0284c7', 'defaultSelected' => false],
            ['ticker' => 'CCBN', 'name' => 'ЦентрКредит Банкі', 'rate' => 7.5, 'badge' => 'BCC', 'color' => '#15803d', 'defaultSelected' => false],
            ['ticker' => 'HSBK', 'name' => 'Halyk Bank', 'rate' => 2.6, 'badge' => 'HB', 'color' => '#047857', 'defaultSelected' => false],
            ['ticker' => 'KCEL', 'name' => 'Kcell', 'rate' => 7.7, 'badge' => 'KC', 'color' => '#6d28d9', 'defaultSelected' => false],
        ];

        return view('site.urpaq-kapitaly', ['companies' => $companies]);
    }
}

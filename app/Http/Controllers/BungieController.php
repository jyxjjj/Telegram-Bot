<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BungieController extends BaseController
{
    public function login(Request $request): void
    {
        dump($request->query());
    }

    public function redirect(Request $request): void
    {
        dump($request->query());
    }
}

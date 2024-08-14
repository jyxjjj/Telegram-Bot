<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BungieController extends BaseController
{
    public function login(Request $request): string|RedirectResponse
    {
        //  BUNGIE_OAUTH_URL=https://www.bungie.net/en/OAuth/Authorize
        $user_id = $request->query('user_id') ?? '';
        if (!is_numeric($user_id)) {
            return 'Invalid user';
        }
    }

    public function redirect(Request $request): void
    {
        dump($request->all());
    }
}

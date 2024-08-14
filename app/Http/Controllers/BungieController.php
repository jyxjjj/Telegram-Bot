<?php
/**
 * DESMG Telegram Bot
 * This file is a part of our Open Source Project (https://github.com/jyxjjj/Telegram-Bot)
 *
 * @copyright 2015-2024 DESMG
 * @license GNU Affero General Public License v3.0 (https://www.gnu.org/licenses/agpl-3.0.html)
 * @author DESMG (www.desmg.com) < opensource@desmg.org >
 *
 * @QQ 773933146
 * @Telegram jyxjjj (https://t.me/jyxjjj)
 * @Producer DESMG
 *
 * Copyright (C) 2015-2024 DESMG
 * All Rights Reserved.
 *
 * Released under GNU Affero General Public License Version 3.0
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers;

use App\Jobs\SendMessageJob;
use App\Models\TBungieBind;
use DESMG\RFC4122\UUID;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Http;

class BungieController extends BaseController
{
    public function login(Request $request): string|RedirectResponse
    {
        $user_id = $request->query('user_id') ?? '';
        $state = UUID::DEID64();
        if (!is_numeric($user_id)) {
            return 'Invalid user';
        }
        $request->session()->put('user_id', $user_id);
        $request->session()->put('state', $state);
        $request->session()->save();
        $query = http_build_query([
            'client_id' => env('BUNGIE_CLIENT_ID'),
            'response_type' => 'code',
            'state' => $state,
        ]);
        return new RedirectResponse('https://www.bungie.net/en/OAuth/Authorize?' . $query);
    }

    public function redirect(Request $request): Response
    {
        $code = $request->query('code') ?? '';
        $state = $request->query('state') ?? '';
        $realState = $request->session()->get('state');
        $user_id = $request->session()->get('user_id');
        $request->session()->flush();
        Cookie::queue(Cookie::forget($request->session()->getName()));
        if (empty($code) || empty($state)) {
            return response('Invalid code or state');
        }
        if ($realState !== $state) {
            return response('Invalid state');
        }
        $tokenUrl = 'https://www.bungie.net/platform/app/oauth/token/';
        try {
            $response = Http::asForm()
                ->post($tokenUrl, [
                    'grant_type' => 'authorization_code',
                    'code' => $code,
                    'client_id' => env('BUNGIE_CLIENT_ID'),
                    'client_secret' => env('BUNGIE_CLIENT_SECRET'),
                ]);
        } catch (ConnectionException) {
            return response("<!DOCTYPE html>
<html lang='en'>
<head>
<title>Bind Failed</title>
</head>
<body>
<h1>Failed to connect to bungie server.</h1>
<a href='login?user_id=$user_id'>>>>Retry<<<</a>
</body>
</html>");
        }
        if ($response->successful()) {
            $response = $response->json();
            $membership_id = $response['membership_id'];
            $access_token = $response['access_token'];
            $refresh_token = $response['refresh_token'];
            $expires_in = $response['expires_in'] - 600;
            $refresh_expires_in = $response['refresh_expires_in'] - 600;
            $model = new TBungieBind;
            $model->saveUser($user_id, $membership_id, $access_token, $refresh_token, $expires_in, $refresh_expires_in);
            $data = [
                'chat_id' => $user_id,
                'text' => "Your Bungie Account has been successfully authorized.\nYour Bundle Account: $membership_id",
                'protect_content' => true,
            ];
            $this->dispatch(new SendMessageJob($data, null, 0));
            return response("<!DOCTYPE html>
<html lang='en'>
<head>
<title>Bind Successful</title>
</head>
<body>
<h1>Successfully Authorized</h1>
<p>User ID: $user_id</p>
<p>Your Bundle Account: $membership_id</p>
<script>
    setTimeout(() => {
        if (confirm('This page intends to close. Do you wish to proceed?')) {
            window.close();
        }
    }, 3000);
</script>
</body>
</html>");
        } else {
            $response = $response->json();
            $error = $response['error_description'];
            return response("<!DOCTYPE html>
<html lang='en'>
<head>
<title>Bind Failed</title>
</head>
<body>
<h1>Failed to authorize your bungie account.</h1>
<a href='login?user_id=$user_id'>>>>Retry<<<</a>
<script>
    console.error('$error');
</script>
</body>
</html>");
        }
    }
}

<?php

namespace App\Common;

class Config
{
    const array FILE_PERMISSIONS = [
        'file' => [
            'public' => 0644,
            'private' => 0644,
        ],
        'dir' => [
            'public' => 0775,
            'private' => 0775,
        ],
    ];

    const array CURL_HEADERS = [
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
        'Pragma' => 'no-cache',
        'Sec-CH-DPR' => '1',
        'Sec-CH-Prefers-Color-Scheme' => 'dark',
        'Sec-CH-Prefers-Reduced-Motion' => 'no-preference',
        'Sec-CH-Prefers-Reduced-Transparency' => 'no-preference',
        'Sec-CH-UA' => '"Google Chrome";v="120", "Chromium";v="120","DESMG Web Client";v="2"',
        'Sec-CH-UA-Arch' => 'x86',
        'Sec-CH-UA-Bitness' => '64',
        'Sec-CH-UA-Full-Version-List' => '"Google Chrome";v="120.0.0.0", "Chromium";v="120.0.0.0","DESMG Web Client";v="2.3"',
        'Sec-CH-UA-Mobile' => '?0',
        'Sec-CH-UA-Model' => '',
        'Sec-CH-UA-Platform' => 'Fedora',
        'Sec-CH-UA-Platform-Version' => '39',
        'Sec-CH-UA-WoW64' => '?0',
        'Sec-CH-Viewport-Width' => '2560',
        'Sec-CH-Width' => '2560',
        'Sec-GPC' => '1',
        'Sec-Fetch-Dest' => 'document',
        'Sec-Fetch-Mode' => 'navigate',
        'Sec-Fetch-Site' => 'none',
        'Sec-Fetch-User' => '?1',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => "User_Agent_Protected_By_Client_Hints (https://web.dev/user-agent-client-hints/) Linux/6 Fedora/39 IA64 x86_64 Chrome/120.0.0.0 DESMG-Web-Client/2.3",
    ];

    const array PLAIN_HEADER = [
        'Content-Type' => 'text/plain; charset=UTF-8',
    ];

    const array CORS_HEADER = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'HEAD, GET, POST, OPTIONS',
        'Access-Control-Max-Age' => '3600',
    ];
}

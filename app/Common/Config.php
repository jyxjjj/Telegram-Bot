<?php

namespace App\Common;

class Config
{
    const FILE_PERMISSIONS = [
        'file' => [
            'public' => 0644,
            'private' => 0644,
        ],
        'dir' => [
            'public' => 0775,
            'private' => 0775,
        ],
    ];

    const CURL_HEADERS = [
        'Accept-Encoding' => 'gzip, deflate, br',
        'Accept-Language' => 'zh-CN,zh;q=0.9,en-US;q=0.8,en;q=0.7',
        'Cache-Control' => 'no-cache',
        'DNT' => '1',
        'DPR' => '1',
        'Pragma' => 'no-cache',
        'Sec-CH-Prefers-Reduced-Motion' => 'no-preference',
        'SEC-CH-UA' => '"Google Chrome";v="114", "Chromium";v="114", "DESMG Web Client";v="2"',
        'SEC-CH-UA-Arch' => 'x86',
        'SEC-CH-UA-Bitness' => '64',
        'SEC-CH-UA-Full-Version' => '2.2',
        'SEC-CH-UA-Full-Version-List' => '"Google Chrome";v="114.0.0.0", "Chrome";v="114.0.0.0", "DESMG Web Client";v="2.2"',
        'SEC-CH-UA-Mobile' => '?0',
        'SEC-CH-UA-Model' => '',
        'SEC-CH-UA-Platform' => 'Fedora',
        'SEC-CH-UA-Platform-Version' => '38',
        'SEC-CH-UA-WoW64' => '?0',
        'SEC-GPC' => '1',
        'SEC-Fetch-Dest' => 'document',
        'SEC-Fetch-Mode' => 'navigate',
        'SEC-Fetch-Site' => 'none',
        'SEC-Fetch-User' => '?1',
        'Upgrade-Insecure-Requests' => '1',
        'User-Agent' => 'User_Agent_Protected_By_Client_Hints (https://web.dev/user-agent-client-hints/) Linux/6 Fedora/38 IA64 x86_64 Chrome/114.0.0.0 DESMG-Web-Client/2.2',
        'Viewport-Width' => '2560',
        'Width' => '2560',
    ];

    const PLAIN_HEADER = [
        'Content-Type' => 'text/plain; charset=UTF-8'
    ];

    const CORS_HEADER = [
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Methods' => 'HEAD, GET, POST, OPTIONS',
        'Access-Control-Max-Age' => '3600',
    ];
}

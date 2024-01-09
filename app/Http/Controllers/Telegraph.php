<?php

namespace App\Http\Controllers;

use App\Common\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Telegraph extends BaseController
{
    public function getFile(Request $request): Response
    {
        $fileType = $request->route('type');
        if (!in_array($fileType, ['png', 'mp4'])) {
            return $this->json([
                'code' => 400,
                'msg' => 'param invalid',
                'ok' => false,
                'result' => false,
                'description' => "requested file type $fileType not supported",
            ]);
        }
        $file = $request->route('file');
        if (!in_array($file, ['123456', '654321'])) {
            return $this->json([
                'code' => 404,
                'msg' => 'not found',
                'ok' => false,
                'result' => false,
                'description' => "requested file $file.$fileType not found",
            ]);
        }
        try {
            $connectTimeout = 5;
            $timeout = 5;
            $retry = 3;
            $retryDelay = 1000;
            $options = [];
            $headers = Config::CURL_HEADERS;
            $ts = Carbon::now()->getTimestamp();
            $headers['User-Agent'] .= " Telegram-Telegraph-File-Proxy/$ts";
            $data = Http::withHeaders($headers)
                ->withOptions([
                    'force_ip_resolve' => 'v4',
                ])
                ->withOptions($options)
                ->connectTimeout($connectTimeout)
                ->timeout($timeout)
                ->retry($retry, $retryDelay, throw: false)->get("https://telegra.ph/file/$file.$fileType");
            $etag = $data->header('ETag');
            $date = $data->header('Date');
            $expires = $data->header('Expires');
            $cacheControl = $data->header('Cache-Control');
            $type = $data->header('Content-Type');
            $body = $data->body();
            $length = strlen($body);
            $ifNoneMatch = $request->header('If-None-Match');
            ob_end_clean();
            ob_implicit_flush();
            if ($ifNoneMatch === $etag) {
                return new Response('', 304, [
                    'Date' => $date,
                    'Expires' => $expires,
                    'ETag' => $etag,
                    'Cache-Control' => $cacheControl,
                    'CDN-Cache-Control' => $cacheControl,
                    'CloudFlare-CDN-Cache-Control' => $cacheControl,
                ]);
            }
            return new Response($body, 200, [
                'Content-Type' => $type,
                'Content-Length' => $length,
                'Date' => $date,
                'Expires' => $expires,
                'ETag' => $etag,
                'Cache-Control' => $cacheControl,
                'CDN-Cache-Control' => $cacheControl,
                'CloudFlare-CDN-Cache-Control' => $cacheControl,
            ]);
        } catch (Throwable) {
            return $this->json([
                'code' => 404,
                'msg' => 'not found',
                'ok' => false,
                'result' => false,
                'description' => "requested file $file.$fileType not found",
            ]);
        }
    }
}

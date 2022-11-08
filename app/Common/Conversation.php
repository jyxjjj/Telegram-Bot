<?php

namespace App\Common;

use Illuminate\Support\Facades\Storage;

class Conversation
{
    public static function get(int $user_id, string $type): array
    {
        $fs = Storage::disk('telegram');
        $path = "conversation/{$type}";
        $file = "{$path}/{$user_id}.json";
        if ($fs->exists($file)) {
            return json_decode($fs->get($file), true);
        }
        return [];
    }

    public static function save(int $user_id, string $type, array $value): bool
    {
        $fs = Storage::disk('telegram');
        $path = "conversation/{$type}";
        $file = "{$path}/{$user_id}.json";
        if (!$fs->exists($path)) {
            $fs->makeDirectory($path);
        }
        return $fs->put($file, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}

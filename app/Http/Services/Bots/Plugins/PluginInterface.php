<?php

namespace App\Http\Services\Bots\Plugins;

interface PluginInterface
{
    public static function getName(): string;
    public static function newMemberMessage(array &$data, int $chatId, string $newChatMemberName, int $newChatMemberId): void;
}

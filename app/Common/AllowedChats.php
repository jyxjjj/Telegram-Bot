<?php

namespace App\Common;

class AllowedChats
{
    public static function getChannels(): array
    {
        $allowed_channels_and_groups = env('ALLOWED_SAVE_CHANNELS_AND_ITS_GROUPS');
        $allowed_channels_and_groups = explode(',', $allowed_channels_and_groups);
        $channels = [];
        foreach ($allowed_channels_and_groups as $channels_and_groups) {
            $channels_and_groups = explode(':', $channels_and_groups);
            $channel_id = (int)$channels_and_groups[0];
            $channels[] = $channel_id;
        }
        return $channels;
    }

    public static function getGroups(): array
    {
        $allowed_channels_and_groups = env('ALLOWED_SAVE_CHANNELS_AND_ITS_GROUPS');
        $allowed_channels_and_groups = explode(',', $allowed_channels_and_groups);
        $groups = [];
        foreach ($allowed_channels_and_groups as $channels_and_groups) {
            $channels_and_groups = explode(':', $channels_and_groups);
            $group_id = (int)$channels_and_groups[1];
            $groups[] = $group_id;
        }
        return $groups;
    }

    public static function channelGetGroup(int $channel): int
    {
        $allowed_channels_and_groups = env('ALLOWED_SAVE_CHANNELS_AND_ITS_GROUPS');
        $allowed_channels_and_groups = explode(',', $allowed_channels_and_groups);
        foreach ($allowed_channels_and_groups as $channels_and_groups) {
            $channels_and_groups = explode(':', $channels_and_groups);
            $channel_id = (int)$channels_and_groups[0];
            $group_id = (int)$channels_and_groups[1];
            if ($channel_id == $channel) {
                return $group_id;
            }
        }
        return 0;
    }

    public static function groupGetChannel(int $group): int
    {
        $allowed_channels_and_groups = env('ALLOWED_SAVE_CHANNELS_AND_ITS_GROUPS');
        $allowed_channels_and_groups = explode(',', $allowed_channels_and_groups);
        foreach ($allowed_channels_and_groups as $channels_and_groups) {
            $channels_and_groups = explode(':', $channels_and_groups);
            $channel_id = (int)$channels_and_groups[0];
            $group_id = (int)$channels_and_groups[1];
            if ($group_id == $group) {
                return $channel_id;
            }
        }
        return 0;
    }
}

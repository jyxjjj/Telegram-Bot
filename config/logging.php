<?php

use App\Common\Log\LogFormatter;

$logging = [];
$channels = [
    'single',
    'sql',
    'perf',
    'deprecations',
    'emergency'
];

$date = date('Y-m-d');
foreach ($channels as $channel) {
    $logging['channels'][$channel]['driver'] = 'single';
    $logging['channels'][$channel]['name'] = $channel;
    $logging['channels'][$channel]['path'] = storage_path("logs/$date.$channel.log");
    $logging['channels'][$channel]['formatter'] = LogFormatter::class;
    $logging['channels'][$channel]['level'] = 'debug';
    $logging['channels'][$channel]['permission'] = 0644;
    $logging['channels'][$channel]['bubble'] = false;
    $logging['channels'][$channel]['locking'] = false;
}

$logging['default'] = 'single';
$logging['deprecations'] = 'deprecations';

return $logging;

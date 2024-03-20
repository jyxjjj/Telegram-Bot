<?php

namespace App\Common\Log;

use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\LogRecord;

class DataBaseLogger extends AbstractProcessingHandler
{
    public function __construct($level = Level::Debug, $bubble = false)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(LogRecord $record): void
    {
        $data = [
            'channel' => $record->channel,
            'level' => $record->level->getName(),
            'message' => $record->message,
            'context' => json_encode($record->context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'extra' => json_encode($record->extra),
        ];
        DB::table('logs')->insert($data);
    }
}

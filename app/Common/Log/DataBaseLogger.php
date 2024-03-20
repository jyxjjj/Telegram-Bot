<?php

namespace App\Common\Log;

use Illuminate\Support\Facades\DB;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DataBaseLogger extends AbstractProcessingHandler
{
    public function __construct($level = Logger::DEBUG, $bubble = false)
    {
        parent::__construct($level, $bubble);
    }

    protected function write(array $record): void
    {
        $data = [
            'channel' => $record['channel'],
            'level' => $record['level_name'],
            'message' => $record['message'],
            'context' => json_encode($record['context']),
            'extra' => json_encode($record['extra']),
        ];
        DB::table('logs')->insert($data);
    }
}

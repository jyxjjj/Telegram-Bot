<?php

namespace App\Common\Log;

use Monolog\Formatter\LineFormatter;

class LogFormatter extends LineFormatter
{
    public const SIMPLE_FORMAT = <<<EOF
[%datetime%]
channel=%channel%
level=%level_name%
message=%message%
context=%context%
extra=%extra%


EOF;

    public function __construct()
    {
        parent::__construct(self::SIMPLE_FORMAT, 'Y-m-d H:i:s.u', false, true);
    }
}

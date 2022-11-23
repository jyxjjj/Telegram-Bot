<?php

namespace App\Models;

enum TChatKeywordsOperationEnum: string
{
    case OPERATION_REPLY = 'REPLY';
    case OPERATION_DELETE = 'DELETE';
    case OPERATION_WARN = 'WARN';
    case OPERATION_BAN = 'BAN';
    case OPERATION_RESTRICT = 'RESTRICT';
    case OPERATION_FORWARD = 'FORWARD';
    case OPERATION_REPEAT = 'REPEAT';
}

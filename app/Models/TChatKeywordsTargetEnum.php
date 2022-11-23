<?php

namespace App\Models;

enum TChatKeywordsTargetEnum: string
{
    case TARGET_CHATID = 'CHATID';
    case TARGET_USERID = 'USERID';
    case TARGET_NAME = 'NAME';
    case TARGET_FROMNAME = 'FROMNAME';
    case TARGET_TITLE = 'TITLE';
    case TARGET_TEXT = 'TEXT';
    case TARGET_DICE = 'DICE';
}

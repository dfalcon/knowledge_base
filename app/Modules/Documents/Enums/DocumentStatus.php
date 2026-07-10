<?php

namespace App\Modules\Documents\Enums;

enum DocumentStatus: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Indexed    = 'indexed';
    case Failed     = 'failed';
}

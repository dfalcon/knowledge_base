<?php

namespace App\Modules\Users\Enums;

enum UserStatus: string
{
    case Pending = 'pending';
    case Active  = 'active';
    case Blocked = 'blocked';
}

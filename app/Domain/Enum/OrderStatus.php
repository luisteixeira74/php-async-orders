<?php

namespace App\Domain\Enum;

enum OrderStatus: string
{
    case RECEIVED   = 'received';
    case PROCESSING = 'processing';
    case PROCESSED  = 'processed';
    case FAILED     = 'failed';
}

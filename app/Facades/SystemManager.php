<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SystemManager extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'system.management';
    }
}
<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class SystemManagementFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'system.management';
    }
}
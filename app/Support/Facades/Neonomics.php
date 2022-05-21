<?php

namespace App\Support\Facades;

use Illuminate\Support\Facades\Facade;

class Neonomics extends Facade
{
    protected static function getFacadeAccessor() {
        return 'neonomics';
    }
}

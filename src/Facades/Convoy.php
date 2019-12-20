<?php

namespace AdditionApps\Convoy\Facades;

use AdditionApps\Convoy\Contracts\ConvoyContract;
use Illuminate\Support\Facades\Facade;

class Convoy extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ConvoyContract::class;
    }
}

<?php

namespace Crumby\RouteResolver\Facades;

use Illuminate\Support\Facades\Facade;

class RouteResolver extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'RouteResolver';
    }
}

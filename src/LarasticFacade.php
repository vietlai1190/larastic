<?php

namespace Larastic;

use Illuminate\Support\Facades\Facade;

/**
 * Class LarasticFacade
 * @package Larastic
 */
class LarasticFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'larastic';
    }
}
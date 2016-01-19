<?php namespace Arcanedev\Socialite\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class     Socialite
 *
 * @package  Arcanedev\Socialite\Facades
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class Socialite extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \Arcanedev\Socialite\Contracts\Factory::class;
    }
}

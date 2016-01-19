<?php namespace Arcanedev\Socialite\Contracts;

/**
 * Interface  Factory
 *
 * @package   Arcanedev\Socialite\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface Factory
{
    /**
     * Get an OAuth provider implementation.
     *
     * @param  string  $driver
     *
     * @return \Arcanedev\Socialite\Contracts\Provider
     */
    public function driver($driver = null);
}

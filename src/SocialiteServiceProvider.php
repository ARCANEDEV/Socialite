<?php namespace Arcanedev\Socialite;

use Arcanedev\Support\PackageServiceProvider as ServiceProvider;

/**
 * Class     SocialiteServiceProvider
 *
 * @package  Arcanedev\Socialite
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SocialiteServiceProvider extends ServiceProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Vendor name.
     *
     * @var string
     */
    protected $vendor  = 'arcanedev';

    /**
     * Package name.
     *
     * @var string
     */
    protected $package = 'socialite';

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the base path of the package.
     *
     * @return string
     */
    public function getBasePath()
    {
        return dirname(__DIR__);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->singleton(\Arcanedev\Socialite\Contracts\Factory::class, function ($app) {
            return new SocialiteManager($app);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            \Arcanedev\Socialite\Contracts\Factory::class,
        ];
    }
}

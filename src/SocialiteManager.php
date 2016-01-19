<?php namespace Arcanedev\Socialite;

use Arcanedev\Socialite\OAuth\One as OAuthOne;
use Arcanedev\Socialite\OAuth\Two as OAuthTwo;
use Illuminate\Support\Manager;
use League\OAuth1\Client\Server\Bitbucket as BitbucketServer;
use League\OAuth1\Client\Server\Twitter as TwitterServer;

/**
 * Class     SocialiteManager
 *
 * @package  Arcanedev\Socialite
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SocialiteManager extends Manager implements Contracts\Factory
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get a driver instance.
     *
     * @param  string  $driver
     *
     * @return mixed
     */
    public function with($driver)
    {
        return $this->driver($driver);
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        throw new Exceptions\UnspecifiedDriverException(
            'No Socialite driver was specified.'
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  OAuth V1 Drivers
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthOneProvider
     */
    protected function createTwitterDriver()
    {
        $config = $this->config()->get('services.twitter');

        return new OAuthOne\TwitterProvider(
            $this->app['request'],
            new TwitterServer($this->formatConfig($config))
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthOneProvider
     */
    protected function createBitbucketDriver()
    {
        $config = $this->config()->get('services.bitbucket');

        return new OAuthOne\BitbucketProvider(
            $this->app['request'],
            new BitbucketServer($this->formatConfig($config))
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  OAuth V2 Drivers
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthTwoProvider
     */
    protected function createGithubDriver()
    {
        return $this->buildProvider(
            OAuthTwo\GithubProvider::class,
            $this->config()->get('services.github')
        );
    }

    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthTwoProvider
     */
    protected function createFacebookDriver()
    {
        return $this->buildProvider(
            OAuthTwo\FacebookProvider::class,
            $this->config()->get('services.facebook')
        );
    }
    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthTwoProvider
     */
    protected function createGoogleDriver()
    {
        return $this->buildProvider(
            OAuthTwo\GoogleProvider::class,
            $this->config()->get('services.google')
        );
    }
    /**
     * Create an instance of the specified driver.
     *
     * @return \Arcanedev\Socialite\Base\OAuthTwoProvider
     */
    protected function createLinkedinDriver()
    {
        return $this->buildProvider(
            OAuthTwo\LinkedInProvider::class,
            $this->config()->get('services.linkedin')
        );
    }

    /**
     * Build an OAuth 2 provider instance.
     *
     * @param  string  $provider
     * @param  array   $config
     *
     * @return \Arcanedev\Socialite\Base\OAuthTwoProvider
     */
    public function buildProvider($provider, $config)
    {
        return new $provider(
            $this->app['request'],
            $config['client_id'],
            $config['client_secret'],
            $config['redirect']
        );
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the config repository.
     *
     * @return \Illuminate\Contracts\Config\Repository
     */
    protected function config()
    {
        return $this->app['config'];
    }

    /**
     * Format the server configuration.
     *
     * @param  array  $config
     *
     * @return array
     */
    protected function formatConfig(array $config)
    {
        return array_merge([
            'identifier'   => $config['client_id'],
            'secret'       => $config['client_secret'],
            'callback_uri' => $config['redirect'],
        ], $config);
    }
}

<?php namespace Arcanedev\Socialite\Tests\Stubs;

use Mockery as m;
use Arcanedev\Socialite\OAuth\Two\FacebookProvider;

class FacebookTestProviderStub extends FacebookProvider
{
    public $http;

    protected function getUserByToken($token)
    {
        return ['id' => 'foo'];
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        if ($this->http) {
            return $this->http;
        }

        return $this->http = m::mock('StdClass');
    }
}

<?php namespace Arcanedev\Socialite\OAuth\One;

use Arcanedev\Socialite\Contracts\Provider;
use Illuminate\Http\Request;
use League\OAuth1\Client\Credentials\TokenCredentials;
use League\OAuth1\Client\Server\Server;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class     AbstractProvider
 *
 * @package  Arcanedev\Socialite\OAuth\One
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class AbstractProvider implements Provider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The HTTP request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * The OAuth server implementation.
     *
     * @var \League\OAuth1\Client\Server\Server
     */
    protected $server;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new provider instance.
     *
     * @param  \Illuminate\Http\Request             $request
     * @param  \League\OAuth1\Client\Server\Server  $server
     */
    public function __construct(Request $request, Server $server)
    {
        $this->server  = $server;
        $this->setRequest($request);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the request instance.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        $this->request->session()->set(
            'oauth.temp', $temp = $this->server->getTemporaryCredentials()
        );

        return new RedirectResponse($this->server->getAuthorizationUrl($temp));
    }

    /**
     * Get the User instance for the authenticated user.
     *
     * @throws \InvalidArgumentException
     *
     * @return \Arcanedev\Socialite\OAuth\One\User
     */
    public function user()
    {
        if ( ! $this->hasNecessaryVerifier()) {
            throw new \InvalidArgumentException(
                'Invalid request. Missing OAuth verifier.'
            );
        }

        $user     = $this->server->getUserDetails($token = $this->getToken());
        $instance = (new User)->setRaw($user->extra)
            ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id'       => $user->uid,
            'nickname' => $user->nickname,
            'name'     => $user->name,
            'email'    => $user->email,
            'avatar'   => $user->imageUrl,
        ]);
    }

    /**
     * Get a Social User instance from a known access token and secret.
     *
     * @param  string  $token
     * @param  string  $secret
     *
     * @return \Arcanedev\Socialite\OAuth\One\User
     */
    public function userFromTokenAndSecret($token, $secret)
    {
        $tokenCredentials = new TokenCredentials;
        $tokenCredentials->setIdentifier($token);
        $tokenCredentials->setSecret($secret);

        $user = $this->server->getUserDetails($tokenCredentials);

        $instance = (new User)->setRaw($user->extra)
            ->setToken($tokenCredentials->getIdentifier(), $tokenCredentials->getSecret());

        return $instance->map([
            'id'       => $user->uid,
            'nickname' => $user->nickname,
            'name'     => $user->name,
            'email'    => $user->email,
            'avatar'   => $user->imageUrl,
        ]);
    }

    /* ------------------------------------------------------------------------------------------------
     |  Other Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the token credentials for the request.
     *
     * @return \League\OAuth1\Client\Credentials\TokenCredentials
     */
    protected function getToken()
    {
        $temp = $this->request->session()->get('oauth.temp');

        return $this->server->getTokenCredentials(
            $temp, $this->request->get('oauth_token'), $this->request->get('oauth_verifier')
        );
    }

    /**
     * Determine if the request has the necessary OAuth verifier.
     *
     * @return bool
     */
    protected function hasNecessaryVerifier()
    {
        return $this->request->has('oauth_token') && $this->request->has('oauth_verifier');
    }
}

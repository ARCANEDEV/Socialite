<?php namespace Arcanedev\Socialite\Base;

use Arcanedev\Socialite\Contracts\Provider;
use Arcanedev\Socialite\OAuth\One\User;
use Illuminate\Http\Request;
use League\OAuth1\Client\Server\Server;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class     OAuthOneProvider
 *
 * @package  Arcanedev\Socialite\Base
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class OAuthOneProvider implements Provider
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
        $this->request->getSession()->set(
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
        $temp = $this->request->getSession()->get('oauth.temp');

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

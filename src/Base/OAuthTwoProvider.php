<?php namespace Arcanedev\Socialite\Base;

use Arcanedev\Socialite\Contracts\Provider;
use Arcanedev\Socialite\Exceptions\InvalidStateException;
use GuzzleHttp\ClientInterface;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class     OAuthTwoProvider
 *
 * @package  Arcanedev\Socialite\Base
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
abstract class OAuthTwoProvider implements Provider
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
     * The client ID.
     *
     * @var string
     */
    protected $clientId;

    /**
     * The client secret.
     *
     * @var string
     */
    protected $clientSecret;

    /**
     * The redirect URL.
     *
     * @var string
     */
    protected $redirectUrl;

    /**
     * The custom parameters to be sent with the request.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ',';

    /**
     * The type of the encoding in the query.
     *
     * @var int Can be either PHP_QUERY_RFC3986 or PHP_QUERY_RFC1738.
     */
    protected $encodingType = PHP_QUERY_RFC1738;

    /**
     * Indicates if the session state should be utilized.
     *
     * @var bool
     */
    protected $stateless = false;

    /* ------------------------------------------------------------------------------------------------
     |  Constructor
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Create a new provider instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string                    $clientId
     * @param  string                    $clientSecret
     * @param  string                    $redirectUrl
     */
    public function __construct(Request $request, $clientId, $clientSecret, $redirectUrl)
    {
        $this->setRequest($request);
        $this->clientId     = $clientId;
        $this->redirectUrl  = $redirectUrl;
        $this->clientSecret = $clientSecret;
    }

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $state
     *
     * @return string
     */
    abstract protected function getAuthUrl($state);

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    abstract protected function getTokenUrl();

    /**
     * Get the raw user for the given access token.
     *
     * @param  string  $token
     *
     * @return array
     */
    abstract protected function getUserByToken($token);

    /**
     * Get the code from the request.
     *
     * @return string
     */
    protected function getCode()
    {
        return $this->request->input('code');
    }

    /**
     * Set the scopes of the requested access.
     *
     * @param  array  $scopes
     *
     * @return self
     */
    public function scopes(array $scopes)
    {
        $this->scopes = array_unique(array_merge($this->scopes, $scopes));

        return $this;
    }

    /**
     * Get the current scopes.
     *
     * @return array
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * Get a fresh instance of the Guzzle HTTP client.
     *
     * @return \GuzzleHttp\Client
     */
    protected function getHttpClient()
    {
        return new \GuzzleHttp\Client;
    }

    /**
     * Set the request instance.
     *
     * @param  Request  $request
     * @return $this
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * Determine if the provider is operating with state.
     *
     * @return bool
     */
    protected function usesState()
    {
        return ! $this->stateless;
    }

    /**
     * Determine if the provider is operating as stateless.
     *
     * @return bool
     */
    protected function isStateless()
    {
        return $this->stateless;
    }

    /**
     * Indicates that the provider should operate as stateless.
     *
     * @return $this
     */
    public function stateless()
    {
        $this->stateless = true;

        return $this;
    }

    /**
     * Set the custom parameters of the request.
     *
     * @param  array  $parameters
     *
     * @return self
     */
    public function with(array $parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Get the POST fields for the token request.
     *
     * @param  string  $code
     *
     * @return array
     */
    protected function getTokenFields($code)
    {
        return [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUrl,
        ];
    }

    /**
     * Get the access token from the token response body.
     *
     * @param  string  $body
     *
     * @return string
     */
    protected function parseAccessToken($body)
    {
        return json_decode($body, true)['access_token'];
    }

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Redirect the user of the application to the provider's authentication screen.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect()
    {
        $state = null;

        if ($this->usesState()) {
            $this->request->getSession()->set('state', $state = str_random(40));
        }

        return new RedirectResponse($this->getAuthUrl($state));
    }

    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ($this->hasInvalidState()) {
            throw new InvalidStateException;
        }

        $user = $this->mapUserToObject($this->getUserByToken(
            $token = $this->getAccessToken($this->getCode())
        ));

        return $user->setToken($token);
    }

    /**
     * Get the authentication URL for the provider.
     *
     * @param  string  $url
     * @param  string  $state
     *
     * @return string
     */
    protected function buildAuthUrlFromBase($url, $state)
    {
        return $url . '?' . http_build_query($this->getCodeFields($state), '', '&', $this->encodingType);
    }

    /**
     * Get the GET parameters for the code request.
     *
     * @param  string|null  $state
     *
     * @return array
     */
    protected function getCodeFields($state = null)
    {
        $fields = [
            'client_id'     => $this->clientId, 'redirect_uri' => $this->redirectUrl,
            'scope'         => $this->formatScopes($this->scopes, $this->scopeSeparator),
            'response_type' => 'code',
        ];

        if ($this->usesState()) {
            $fields['state'] = $state;
        }

        return array_merge($fields, $this->parameters);
    }
    /**
     * Format the given scopes.
     *
     * @param  array   $scopes
     * @param  string  $scopeSeparator
     *
     * @return string
     */
    protected function formatScopes(array $scopes, $scopeSeparator)
    {
        return implode($scopeSeparator, $scopes);
    }

    /**
     * Get a Social User instance from a known access token.
     *
     * @param  string  $token
     *
     * @return \Arcanedev\Socialite\OAuth\Two\User
     */
    public function userFromToken($token)
    {
        $user = $this->mapUserToObject($this->getUserByToken($token));

        return $user->setToken($token);
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param  array  $user
     *
     * @return \Arcanedev\Socialite\OAuth\Two\User
     */
    abstract protected function mapUserToObject(array $user);

    /**
     * Determine if the current request / session has a mismatching "state".
     *
     * @return bool
     */
    protected function hasInvalidState()
    {
        if ($this->isStateless()) {
            return false;
        }

        $state = $this->request->getSession()->pull('state');

        return ! (strlen($state) > 0 && $this->request->input('state') === $state);
    }

    /**
     * Get the access token for the given code.
     *
     * @param  string  $code
     *
     * @return string
     */
    public function getAccessToken($code)
    {
        $postKey  = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            'headers' => ['Accept' => 'application/json'],
            $postKey  => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }
}
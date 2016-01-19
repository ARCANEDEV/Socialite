<?php namespace Arcanedev\Socialite\OAuth\Two;

use Arcanedev\Socialite\Base\OAuthTwoProvider;

/**
 * Class     FacebookProvider
 *
 * @package  Arcanedev\Socialite\OAuth\Two
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class FacebookProvider extends OAuthTwoProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The base Facebook Graph URL.
     *
     * @var string
     */
    protected $graphUrl = 'https://graph.facebook.com';

    /**
     * The Graph API version for the request.
     *
     * @var string
     */
    protected $version = 'v2.5';

    /**
     * The user fields being requested.
     *
     * @var array
     */
    protected $fields = ['name', 'email', 'gender', 'verified'];

    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = ['email'];

    /**
     * Display the dialog in a popup view.
     *
     * @var bool
     */
    protected $popup = false;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * {@inheritdoc}
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase(
            "https://www.facebook.com/{$this->version}/dialog/oauth", $state
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getTokenUrl()
    {
        return "{$this->graphUrl}/oauth/access_token";
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
        $response = $this->getHttpClient()->get($this->getTokenUrl(), [
            'query' => $this->getTokenFields($code),
        ]);

        return $this->parseAccessToken($response->getBody());
    }

    /**
     * {@inheritdoc}
     */
    protected function parseAccessToken($body)
    {
        parse_str($body);

        return $access_token;
    }

    /**
     * {@inheritdoc}
     */
    protected function getUserByToken($token)
    {
        $appSecretProof = hash_hmac('sha256', $token, $this->clientSecret);
        $response       = $this->getHttpClient()->get("{$this->graphUrl}/{$this->version}/me?access_token={$token}&appsecret_proof={$appSecretProof}&fields=" . implode(',', $this->fields), [
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    /**
     * {@inheritdoc}
     */
    protected function mapUserToObject(array $user)
    {
        $avatarUrl = "{$this->graphUrl}/{$this->version}/{$user['id']}/picture";

        return (new User)->setRaw($user)->map([
            'id'              => $user['id'],
            'nickname'        => null,
            'name'            => isset($user['name']) ? $user['name'] : null,
            'email'           => isset($user['email']) ? $user['email'] : null,
            'avatar'          => $avatarUrl . '?type=normal',
            'avatar_original' => $avatarUrl . '?width=1920',
        ]);
    }
    /**
     * {@inheritdoc}
     */
    protected function getCodeFields($state = null)
    {
        $fields = parent::getCodeFields($state);

        if ($this->popup) {
            $fields['display'] = 'popup';
        }

        return $fields;
    }

    /**
     * Set the user fields to request from Facebook.
     *
     * @param  array  $fields
     *
     * @return self
     */
    public function fields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the dialog to be displayed as a popup.
     *
     * @return self
     */
    public function asPopup()
    {
        $this->popup = true;

        return $this;
    }
}

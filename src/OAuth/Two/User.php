<?php namespace Arcanedev\Socialite\OAuth\Two;

use Arcanedev\Socialite\OAuth\AbstractUser;

/**
 * Class     User
 *
 * @package  Arcanedev\Socialite\OAuth\Two
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class User extends AbstractUser
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The refresh token that can be exchanged for a new access token.
     *
     * @var string
     */
    public $refreshToken;

    /**
     * The number of seconds the access token is valid for.
     *
     * @var int
     */
    public $expiresIn;

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the token on the user.
     *
     * @param  string  $token
     *
     * @return self
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Set the refresh token required to obtain a new access token.
     *
     * @param  string  $refreshToken
     *
     * @return self
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * Set the number of seconds the access token is valid for as measured from when the access token was granted.
     *
     * @param  int  $expiresIn
     *
     * @return self
     */
    public function setExpiresIn($expiresIn)
    {
        $this->expiresIn = $expiresIn;

        return $this;
    }

    /**
     * Set the raw user array from the provider.
     *
     * @param  array  $user
     *
     * @return self
     */
    public function setRaw(array $user)
    {
        return parent::setRaw($user);
    }
}

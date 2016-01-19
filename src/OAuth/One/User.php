<?php namespace Arcanedev\Socialite\OAuth\One;

use Arcanedev\Socialite\Base\AbstractUser;

/**
 * Class     User
 *
 * @package  Arcanedev\Socialite\OAuth\One
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class User extends AbstractUser
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * The user's access token secret.
     *
     * @var string
     */
    public $tokenSecret;

    /* ------------------------------------------------------------------------------------------------
     |  Getters & Setters
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Set the token on the user.
     *
     * @param  string  $token
     * @param  string  $tokenSecret
     *
     * @return self
     */
    public function setToken($token, $tokenSecret)
    {
        $this->token       = $token;
        $this->tokenSecret = $tokenSecret;

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

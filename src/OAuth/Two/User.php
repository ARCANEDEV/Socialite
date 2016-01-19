<?php namespace Arcanedev\Socialite\OAuth\Two;

use Arcanedev\Socialite\Base\AbstractUser;

/**
 * Class     User
 *
 * @package  Arcanedev\Socialite\OAuth\Two
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class User extends AbstractUser
{
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

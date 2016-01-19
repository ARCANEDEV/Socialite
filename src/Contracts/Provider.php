<?php namespace Arcanedev\Socialite\Contracts;

/**
 * Interface  Provider
 *
 * @package   Arcanedev\Socialite\Contracts
 * @author    ARCANEDEV <arcanedev.maroc@gmail.com>
 */
interface Provider
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * Redirect the user to the authentication page for the provider.
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirect();

    /**
     * Get the User instance for the authenticated user.
     *
     * @return \Arcanedev\Socialite\Contracts\User
     */
    public function user();
}

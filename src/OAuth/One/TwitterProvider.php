<?php namespace Arcanedev\Socialite\OAuth\One;

use Arcanedev\Socialite\Base\OAuthOneProvider;
use InvalidArgumentException;

/**
 * Class     TwitterProvider
 *
 * @package  Arcanedev\Socialite\OAuth\One
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class TwitterProvider extends OAuthOneProvider
{
    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    /**
     * {@inheritdoc}
     */
    public function user()
    {
        if ( ! $this->hasNecessaryVerifier()) {
            throw new InvalidArgumentException(
                'Invalid request. Missing OAuth verifier.'
            );
        }

        $user         = $this->server->getUserDetails($token = $this->getToken());
        $extraDetails = [
            'location'    => $user->location,
            'description' => $user->description,
        ];

        $instance = (new User)->setRaw(array_merge($user->extra, $user->urls, $extraDetails))
            ->setToken($token->getIdentifier(), $token->getSecret());

        return $instance->map([
            'id'              => $user->uid,
            'nickname'        => $user->nickname,
            'name'            => $user->name,
            'email'           => $user->email,
            'avatar'          => $user->imageUrl,
            'avatar_original' => str_replace('_normal', '', $user->imageUrl),
        ]);
    }
}

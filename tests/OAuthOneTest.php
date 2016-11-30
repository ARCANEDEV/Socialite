<?php namespace Arcanedev\Socialite\Tests;

use Arcanedev\Socialite\Tests\Stubs\OAuthOneProviderStub;
use Illuminate\Http\Request;
use Mockery as m;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class     OAuthOneTest
 *
 * @package  Arcanedev\Socialite\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class OAuthOneTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function it_can_generates_the_proper_symfony_redirect_response()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $server->shouldReceive('getTemporaryCredentials')->once()->andReturn('temp');
        $server->shouldReceive('getAuthorizationUrl')->once()->with('temp')->andReturn('http://auth.url');

        $request = Request::create('foo');
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $session->shouldReceive('set')->once()->with('oauth.temp', 'temp');

        $provider = new OAuthOneProviderStub($request, $server);
        $response = $provider->redirect();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\RedirectResponse::class, $response);
    }

    /** @test */
    public function it_returns_a_user_instance_for_the_authenticated_request()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $temp   = m::mock(\League\OAuth1\Client\Credentials\TemporaryCredentials::class);
        $server->shouldReceive('getTokenCredentials')->once()->with($temp, 'oauth_token', 'oauth_verifier')->andReturn(
            $token = m::mock(\League\OAuth1\Client\Credentials\TokenCredentials::class)
        );
        $server->shouldReceive('getUserDetails')->once()->with($token)->andReturn($user = m::mock(\League\OAuth1\Client\Server\User::class));
        $token->shouldReceive('getIdentifier')->once()->andReturn('identifier');
        $token->shouldReceive('getSecret')->once()->andReturn('secret');

        $user->uid = 'uid';
        $user->email = 'foo@bar.com';
        $user->extra = ['extra' => 'extra'];

        $request = Request::create('foo', 'GET', ['oauth_token' => 'oauth_token', 'oauth_verifier' => 'oauth_verifier']);
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $session->shouldReceive('get')->once()->with('oauth.temp')->andReturn($temp);
        $provider = new OAuthOneProviderStub($request, $server);

        $user = $provider->user();
        $this->assertInstanceOf(\Arcanedev\Socialite\OAuth\One\User::class, $user);
        $this->assertSame('uid', $user->id);
        $this->assertSame('foo@bar.com', $user->email);
        $this->assertSame(['extra' => 'extra'], $user->user);
    }

    /**
    /* @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function it_must_throw_an_exception_when_verifier_is_missing()
    {
        $server = m::mock(\League\OAuth1\Client\Server\Twitter::class);
        $request = Request::create('foo');
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $provider = new OAuthOneProviderStub($request, $server);

        $provider->user();
    }
}

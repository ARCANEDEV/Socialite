<?php namespace Arcanedev\Socialite\Tests;

use Arcanedev\Socialite\Tests\Stubs\FacebookTestProviderStub;
use Arcanedev\Socialite\Tests\Stubs\OAuthTwoProviderStub;
use GuzzleHttp\ClientInterface;
use Illuminate\Http\Request;
use Mockery as m;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class     OAuthTwoTest
 *
 * @package  Arcanedev\Socialite\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class OAuthTwoTest extends TestCase
{
    public function tearDown()
    {
        m::close();
    }

    /** @test */
    public function it_can_generates_the_proper_symfony_redirect_response()
    {
        $request = Request::create('foo');
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $session->shouldReceive('set')->once();

        $provider = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf(\Symfony\Component\HttpFoundation\RedirectResponse::class, $response);
        $this->assertSame('http://auth.url', $response->getTargetUrl());
    }

    /** @test */
    public function it_returns_a_user_instance_for_the_authenticated_request()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'],
            $postKey  => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));
        $response->shouldReceive('getBody')->once()->andReturn('{ "access_token" : "access_token", "refresh_token" : "refresh_token", "expires_in" : 3600 }');

        $this->assertInstanceOf(\Arcanedev\Socialite\OAuth\Two\User::class, $user = $provider->user());
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertSame('refresh_token', $user->refreshToken);
        $this->assertSame(3600, $user->expiresIn);
    }

    /** @test */
    public function it_returns_a_user_instance_for_the_authenticated_facebook_request()
    {
        $request = Request::create('foo', 'GET', ['state' => str_repeat('A', 40), 'code' => 'code']);
        $request->setSession($session = m::mock(\Symfony\Component\HttpFoundation\Session\SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new FacebookTestProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $postKey = (version_compare(ClientInterface::VERSION, '6') === 1) ? 'form_params' : 'body';
        $provider->http->shouldReceive('post')->once()->with('https://graph.facebook.com/oauth/access_token', [
            $postKey => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));
        $response->shouldReceive('getBody')->once()->andReturn('access_token=access_token&expires=5183085');

        $this->assertInstanceOf(\Arcanedev\Socialite\OAuth\Two\User::class, $user = $provider->user());
        $this->assertSame('foo', $user->id);
        $this->assertSame('access_token', $user->token);
        $this->assertNull($user->refreshToken);
        $this->assertEquals(5183085, $user->expiresIn);
    }

    /**
     * @test
     *
     * @expectedException  \Arcanedev\Socialite\Exceptions\InvalidStateException
     */
    public function it_must_throw_an_exception_if_state_is_invalid()
    {
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('B', 40), 'code' => 'code'
        ]);
        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }

    /**
     * @expectedException \Arcanedev\Socialite\Exceptions\InvalidStateException
     */
    public function it_must_throw_an_exception_if_state_is_not_set()
    {
        $request = Request::create('foo', 'GET', [
            'state' => 'state', 'code' => 'code'
        ]);
        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state');

        $provider = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $provider->user();
    }
}

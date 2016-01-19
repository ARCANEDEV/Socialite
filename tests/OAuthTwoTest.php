<?php namespace Arcanedev\Socialite\Tests;

use Arcanedev\Socialite\Tests\Stubs\OAuthTwoProviderStub;
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
        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('set')->once();

        $provider = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect');
        $response = $provider->redirect();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $response);
        $this->assertEquals('http://auth.url', $response->getTargetUrl());
    }

    /** @test */
    public function it_returns_a_user_instance_for_the_authenticated_request()
    {
        $request = Request::create('foo', 'GET', [
            'state' => str_repeat('A', 40), 'code' => 'code'
        ]);

        $request->setSession($session = m::mock(SessionInterface::class));
        $session->shouldReceive('pull')->once()->with('state')->andReturn(str_repeat('A', 40));

        $provider       = new OAuthTwoProviderStub($request, 'client_id', 'client_secret', 'redirect_uri');
        $provider->http = m::mock('StdClass');
        $provider->http->shouldReceive('post')->once()->with('http://token.url', [
            'headers' => ['Accept' => 'application/json'], 'form_params' => ['client_id' => 'client_id', 'client_secret' => 'client_secret', 'code' => 'code', 'redirect_uri' => 'redirect_uri'],
        ])->andReturn($response = m::mock('StdClass'));

        $response->shouldReceive('getBody')->once()->andReturn('access_token=access_token');
        $user = $provider->user();

        $this->assertInstanceOf(\Arcanedev\Socialite\OAuth\Two\User::class, $user);
        $this->assertEquals('foo', $user->id);
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
        $user     = $provider->user();
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
        $user     = $provider->user();
    }
}

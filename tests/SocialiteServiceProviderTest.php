<?php namespace Arcanedev\Socialite\Tests;

use Arcanedev\Socialite\SocialiteServiceProvider;

/**
 * Class     SocialiteServiceProviderTest
 *
 * @package  Arcanedev\Socialite\Tests
 * @author   ARCANEDEV <arcanedev.maroc@gmail.com>
 */
class SocialiteServiceProviderTest extends TestCase
{
    /* ------------------------------------------------------------------------------------------------
     |  Properties
     | ------------------------------------------------------------------------------------------------
     */
    /** @var  SocialiteServiceProvider  */
    private $provider;

    /* ------------------------------------------------------------------------------------------------
     |  Main Functions
     | ------------------------------------------------------------------------------------------------
     */
    public function setUp()
    {
        parent::setUp();

        $this->provider = $this->app->getProvider(SocialiteServiceProvider::class);
    }

    public function tearDown()
    {
        unset($this->provider);

        parent::tearDown();
    }

    /* ------------------------------------------------------------------------------------------------
     |  Test Functions
     | ------------------------------------------------------------------------------------------------
     */
    /** @test */
    public function it_can_be_instantiated()
    {
        $expectations = [
            \Illuminate\Support\ServiceProvider::class,
            \Arcanedev\Support\ServiceProvider::class,
            \Arcanedev\Support\PackageServiceProvider::class,
            \Arcanedev\Socialite\SocialiteServiceProvider::class,
        ];

        foreach ($expectations as $expected) {
            $this->assertInstanceOf($expected, $this->provider);
        }
    }

    /** @test */
    public function it_can_provides()
    {
        $expected = [
            \Arcanedev\Socialite\Contracts\Factory::class
        ];

        $this->assertEquals($expected, $this->provider->provides());
    }
}

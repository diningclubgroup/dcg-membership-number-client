<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Config;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Stream\Stream;
use PHPUnit\Framework\TestCase;

class GetNewMembershipNumberTest extends TestCase
{
    private $testConfig;
    private $prodConfig;

    public function setUp()
    {
        parent::setUp();

        $this->prodConfig = Config::getInstance(__DIR__.'/../../config.php');
        $this->testConfig = Config::getInstance(__DIR__.'/../../config.php', \Dcg\Config::ENV_TEST);
    }

    /**
     * @test
     */
    public function does_client_return_membership_number()
    {
        $mock = new MockHandler([
            new Response(200, [], Stream::factory(json_encode(['membership_number' => '1234567'])))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $this->assertEquals('1234567', $client->getNewMembershipNumber());
    }

    public function does_client_set_access_token_header()
    {
        $mock = new MockHandler([
            new Response(200, [], Stream::factory(json_encode(['membership_number' => '1234567'])))
        ]);
        $handler = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handler->push($history);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $client->getNewMembershipNumber();

        $lastRequest = end($container);

        $this->assertEquals('TEST_TOKEN', $lastRequest->getHeader('Access-Token'));
    }

    /**
     * @test
     */
    public function does_client_handle_404_error()
    {
        $mock = new MockHandler([
            new Response(404, [], Stream::factory(json_encode(['error' => 'Unable to allocate membership number'])))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $this->setExpectedException(
            '\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException',
            'Unable to allocate membership number'
        );

        $client->getNewMembershipNumber();
    }

    /**
     * @test
     */
    public function does_client_handle_500_error()
    {
        $mock = new MockHandler([
            new Response(500)
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException');

        $client->getNewMembershipNumber();
    }

    /**
     * @test
     */
    public function gets_test_config() {
        $client = new Client([], $this->testConfig);
        $headers = $client->getHeaders();
        $this->assertEquals($headers['Access-Token'], 'TEST_TOKEN');
    }

    /**
     * @test
     */
    public function gets_prod_config() {
        $client = new Client([], $this->prodConfig);
        $headers = $client->getHeaders();
        $this->assertEquals($headers['Access-Token'], 'PROD_TOKEN');
    }
}
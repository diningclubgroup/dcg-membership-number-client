<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Config;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class GetNewBulkMembershipNumberTest extends TestCase
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
    public function does_client_return_membership_numbers()
    {
        $membershipNumbers = ['1234567'];
        $mock = new MockHandler([
            new Response(200, [], json_encode($membershipNumbers))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $payload = ['limit' => 1];
        $this->assertEquals($membershipNumbers, $client->getNewBulkMembershipNumber($payload));
    }

    /**
     * @test
     */
    public function does_client_set_access_token_header()
    {
        $membershipNumbers = ['1234567'];
        $mock = new MockHandler([
            new Response(200, [], json_encode($membershipNumbers))
        ]);
        $handler = HandlerStack::create($mock);

        $container = [];
        $history = Middleware::history($container);
        $handler->push($history);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $payload = ['limit' => 1];
        $client->getNewBulkMembershipNumber($payload);

        $lastRequest = end($container)['request'];

        $this->assertEquals('TEST_TOKEN', $lastRequest->getHeader('Access-Token')[0]);
    }

    /**
     * @test
     */
    public function does_client_handle_404_error()
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode(['error' => 'Unable to allocate membership number']))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException');

        $payload = ['limit' => 1];
        $client->getNewBulkMembershipNumber($payload);
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

        $payload = ['limit' => 1];
        $client->getNewBulkMembershipNumber($payload);
    }

    /**
     * @test
     */
    public function does_client_handle_422_error()
    {
        $mock = new MockHandler([
            new Response(422)
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->testConfig);

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException');

        $payload = ['limit' => -1];
        $client->getNewBulkMembershipNumber($payload);

        $payload = ['limit' => 0];
        $client->getNewBulkMembershipNumber($payload);

        $payload = ['limit' => null];
        $client->getNewBulkMembershipNumber($payload);

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException');
        $payload = [];
        $client->getNewBulkMembershipNumber($payload);
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
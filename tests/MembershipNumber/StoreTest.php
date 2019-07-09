<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Config;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{

    protected $config = null;

    public function setUp()
    {
        parent::setUp();

        $this->config = Config::getInstance(__DIR__.'/../../config.php');
    }

    /**
     * @test
     */
    public function does_client_return_success()
    {
        $mock = new MockHandler([
            new Response(200, [], json_encode([['message' => 'Success']]))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->config);

        $toCreate = [
            ['membership_number' => '888888', 'brand' => 'TC'],
            ['membership_number' => '777777', 'brand' => 'GS']
        ];

        $this->assertEquals(true, $client->store($toCreate));
    }

    /**
     * @test
     */
    public function does_client_throw_exception_for_invalid_input()
    {
        $this->setExpectedException(
            '\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException',
            'Invalid data passed into store'
        );
        $client = new Client([], $this->config);

        $toCreate = [
            ['membership_number' => '888888', 'brand' => 'TC'],
            ['membership_number' => '777777', 'InvalidKey' => 'GS']
        ];

        $this->assertEquals(true, $client->store($toCreate));
    }

    /**
     * @test
     */
    public function does_client_handle_500_error()
    {
        $mock = new MockHandler([
            new Response(404, [], json_encode(['error' => 'Unable to store membership numbers']))
        ]);
        $handler = HandlerStack::create($mock);

        $client = new Client(['handler' => $handler], $this->config);

        $this->setExpectedException(
            '\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException',
            'Unable to store membership numbers'
        );

        $toCreate = [
            ['membership_number' => '888888', 'brand' => 'TC'],
            ['membership_number' => '777777', 'brand' => 'GS']
        ];

        $client->store($toCreate);
    }
}
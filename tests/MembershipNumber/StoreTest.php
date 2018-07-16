<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Exception\MembershipNumberException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StoreTest extends TestCase
{
    /**
     * @test
     */
    public function does_client_return_success()
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new Response(200, [], json_encode([['message' => 'Success']]))
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);

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
        $this->setExpectedException(MembershipNumberException::class, 'Invalid data passed into store');
        $client = new Client();

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
        $mockHandler = new MockHandler([
            new Response(404, [], json_encode(['error' => 'Unable to store membership numbers']))
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->setExpectedException(MembershipNumberException::class, 'Unable to store membership numbers');

        $toCreate = [
            ['membership_number' => '888888', 'brand' => 'TC'],
            ['membership_number' => '777777', 'brand' => 'GS']
        ];

        $client->store($toCreate);
    }
}
<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Exception\MembershipNumberException;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use PHPUnit\Framework\TestCase;

class GetNewMembershipNumberTest extends TestCase
{
    /**
     * @test
     */
    public function does_client_return_membership_number()
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new Response(200, [], json_encode(['membership_number' => '1234567']))
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);


        $this->assertEquals('1234567', $client->getNewMembershipNumber('TC'));
    }

    /**
     * @test
     */
    public function does_client_handle_404_error()
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new Response(404, [], json_encode(['error' => 'Unable to allocate membership number']))
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->setExpectedException(MembershipNumberException::class, 'Unable to allocate membership number');

        $client->getNewMembershipNumber('TC');
    }

    /**
     * @test
     */
    public function does_client_handle_422_error()
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new Response(422, [], json_encode(['error' => 'Brand is missing']))
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->setExpectedException(MembershipNumberException::class, 'Brand is missing');

        $client->getNewMembershipNumber('');
    }

    /**
     * @test
     */
    public function does_client_handle_500_error()
    {
        $mockHandler = new \GuzzleHttp\Handler\MockHandler([
            new Response(500)
        ]);

        $handler = \GuzzleHttp\HandlerStack::create($mockHandler);
        $client = new Client(['handler' => $handler]);

        $this->setExpectedException(MembershipNumberException::class, 'There was an error while contacting Membership Number Service. Response code : 500');

        $client->getNewMembershipNumber('TC');
    }
}
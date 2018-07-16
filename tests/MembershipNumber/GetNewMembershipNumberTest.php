<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Exception\MembershipNumberException;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;

class GetNewMembershipNumberTest extends TestCase
{
    /**
     * @test
     */
    public function does_client_return_membership_number()
    {
        $mock = new Mock([
            new Response(200, [], Stream::factory(json_encode(['membership_number' => '1234567'])))
        ]);

        $client = new Client();

        $client->getEmitter()->attach($mock);

        $this->assertEquals('1234567', $client->getNewMembershipNumber('TC'));
    }

    /**
     * @test
     */
    public function does_client_handle_404_error()
    {
        $mock = new Mock([
            new Response(404, [], Stream::factory(json_encode(['error' => 'Unable to allocate membership number'])))
        ]);

        $client = new Client();

        $client->getEmitter()->attach($mock);

        $this->setExpectedException(MembershipNumberException::class, 'Unable to allocate membership number');

        $client->getNewMembershipNumber('TC');
    }

    /**
     * @test
     */
    public function does_client_handle_422_error()
    {
        $mock = new Mock([
            new Response(422, [], Stream::factory(json_encode(['error' => 'Brand is missing'])))
        ]);

        $client = new Client();

        $client->getEmitter()->attach($mock);

        $this->setExpectedException(MembershipNumberException::class, 'Brand is missing');

        $client->getNewMembershipNumber('');
    }

    /**
     * @test
     */
    public function does_client_handle_500_error()
    {
        $mock = new Mock([
            new Response(500)
        ]);

        $client = new Client();

        $client->getEmitter()->attach($mock);

        $this->setExpectedException(MembershipNumberException::class, 'There was an error while contacting Membership Number Service. Response code : 500');

        $client->getNewMembershipNumber('TC');
    }
}
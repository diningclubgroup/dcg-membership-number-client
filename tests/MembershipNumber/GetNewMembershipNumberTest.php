<?php

use Dcg\Client\MembershipNumber\Client;
use Dcg\Client\MembershipNumber\Config;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\History;
use GuzzleHttp\Subscriber\Mock;
use PHPUnit\Framework\TestCase;

class GetNewMembershipNumberTest extends TestCase
{
    private $config;

    public function setUp()
    {
        parent::setUp();

        $this->config = Config::getInstance(__DIR__.'/../../config.php');
    }

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

        $this->assertEquals('1234567', $client->getNewMembershipNumber());
    }

    public function does_client_set_access_token_header()
    {
        $mock = new Mock([
            new Response(200, [], Stream::factory(json_encode(['membership_number' => '1234567'])))
        ]);

        $history = new History();

        $client = new Client();

        $client->getEmitter()->attach($mock);
        $client->getEmitter()->attach($history);

        $client->getNewMembershipNumber();

        $lastRequest = $history->getLastRequest();

        $this->assertEquals('TEST_TOKEN', $lastRequest->getHeader('Access-Token'));
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

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException', 'Unable to allocate membership number');

        $client->getNewMembershipNumber();
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

        $this->setExpectedException('\\Dcg\\Client\\MembershipNumber\\Exception\\MembershipNumberException', 'There was an error while contacting Membership Number Service. Response code : 500');

        $client->getNewMembershipNumber();
    }
}
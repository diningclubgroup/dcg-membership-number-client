<?php

use Dcg\Client\MembershipNumberClient\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testClientInitialisedCorrectly()
    {
        $client = new Client();

        //Dummy test
        $this->assertInstanceOf(Client::class, $client);
    }
}
<?php

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use DocumentStore\Dropbox\DropboxHTTPHandler;


class DropboxHTTPHandlerTest extends PHPUnit_Framework_TestCase {

    public function setUp()
    {
        parent::setUp();

        $mock = new MockHandler([
            new Response(200, []),
            new Response(400, [])
        ]);
        $handler = HandlerStack::create($mock);
        $client = new Client(['handler' => $handler]);
        $this->httpHandler = new DropboxHTTPHandler('test', $client);
    }

    /**
     * @expectedException GuzzleHttp\Exception\ClientException
     */
    public function testPostGoesThroughAndThrow()
    {
        $response = $this->httpHandler->post('url', 'params');
        $this->assertEquals($response->getStatusCode(), 200);
        $response = $this->httpHandler->post('url', 'params');
    }
}

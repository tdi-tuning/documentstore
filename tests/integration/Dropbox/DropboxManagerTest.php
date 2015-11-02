<?php

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;

use DocumentStore\Dropbox\DropboxHTTPHandler;
use DocumentStore\Dropbox\DropboxManager;


class DropboxManagerTest extends PHPUnit_Framework_TestCase {

    private $container = [];

    public function setUp()
    {
        parent::setUp();

        $mock = new MockHandler([new Response(200, [])]);
        $stack = HandlerStack::create($mock);
        $history = Middleware::history($this->container);
        $stack->push($history);
        $client = new Client(["handler" => $stack]);
        $httpHandler = new DropboxHTTPHandler('test', $client, true);
        $this->dropboxManager = new DropboxManager($httpHandler);
    }

    public function testCreate()
    {
        $this->dropboxManager->create('path', 'content');
        $request = $this->container[0]['request'];
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $request->getBody());
        $this->assertEquals($request->getHeader('Content-Type')[0], 'application/octet-stream');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
        $this->assertEquals($request->getHeader('Dropbox-API-Arg')[0], '{"path":"path","mode":"add","autorename":false,"mute":true}');
    }

    public function testUpdate()
    {
        $this->dropboxManager->update('path', 'content', 'rev');
        $request = $this->container[0]['request'];
        $this->assertInstanceOf('GuzzleHttp\Psr7\Stream', $request->getBody());
        $this->assertEquals($request->getHeader('Content-Type')[0], 'application/octet-stream');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
        $this->assertEquals($request->getHeader('Dropbox-API-Arg')[0], '{"path":"path","mode":"overwrite","autorename":false,"mute":true}');
    }

    public function testDownload()
    {
        $this->dropboxManager->download('path');
        $request = $this->container[0]['request'];
        $this->assertEquals($request->getBody(), '');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
        $this->assertEquals($request->getHeader('Dropbox-API-Arg')[0], '{"path":"path"}');
    }

    public function testDownloadRevision()
    {
        $this->dropboxManager->download('path', 'rev');
        $request = $this->container[0]['request'];
        $this->assertEquals($request->getBody(), '');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
        $this->assertEquals($request->getHeader('Dropbox-API-Arg')[0], '{"path":"path","rev":"rev"}');
    }

    public function testDelete()
    {
        $this->dropboxManager->delete('path');
        $request = $this->container[0]['request'];
        $this->assertEquals($request->getBody(), '{"path":"path"}');
        $this->assertEquals($request->getHeader('Content-Type')[0], 'application/json');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
    }

    public function testCreateSharedLink()
    {
        $this->dropboxManager->createSharedLink('path');
        $request = $this->container[0]['request'];
        $this->assertEquals($request->getBody(), '{"path":"path","short_url":false}');
        $this->assertEquals($request->getHeader('Content-Type')[0], 'application/json');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
    }

    public function testRestore()
    {
        $this->dropboxManager->restore('path', 'rev');
        $request = $this->container[0]['request'];
        $this->assertEquals($request->getBody(), '{"path":"path","rev":"rev"}');
        $this->assertEquals($request->getHeader('Content-Type')[0], 'application/json');
        $this->assertEquals($request->getHeader('Authorization')[0], 'Bearer test');
    }
}

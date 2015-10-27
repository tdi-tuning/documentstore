<?php

require_once __DIR__.'/../LaravelTestCase.php';

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Middleware;

use DocumentStore\Dropbox\DropboxHTTPHandler;
use DocumentStore\Models\File;


class ServiceProviderTest extends LaravelTestCase
{

    private $container = [];

    public function setUp()
    {
        parent::setUp();

        $mock = new MockHandler([
            new Response(200, [], json_encode([
                "path_lower" => "/homework/math/prime_numbers.txt",
                "rev" => "a1c10ce0dd78",
                "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
            ]))
        ]);
        $stack = HandlerStack::create($mock);
        $history = Middleware::history($this->container);
        $stack->push($history);
        $client = new Client(["handler" => $stack]);
        App::instance('DocumentStore\Dropbox\DropboxHTTPHandler', new DropboxHTTPHandler('test', $client, true));
    }


    public function testCreate()
    {
        $documentStore = App::make('DocumentStore\DocumentStore');
        $result = $documentStore->create('path', 'content');
        $this->executeCreate($result);
    }

    public function testFacadeCreate()
    {
        $result = DocumentStore::create('path', 'content');
        $this->executeCreate($result);
    }

    public function executeCreate($result)
    {
        $this->assertTrue($result);
        $file = File::find(1);
        $revision = $file->revisions->first();
        $this->assertEquals($file->path, '/homework/math/prime_numbers.txt');
        $this->assertEquals($file->dp_id, 'id:a4ayc_80_OEAAAAAAAAAXw');
        $this->assertEquals($revision->rev, 'a1c10ce0dd78');
        $this->assertEquals($revision->type, 'C');
        $this->assertEquals($revision->id, $file->revision_id);
    }
}
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
                "rev" => "a1c10ce0dd78"
            ])),
            new Response(200, [], json_encode([
                "path_lower" => "/homework/math/prime_numbers.txt",
                "rev" => "a1c10ce0dd79"
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

    public function testDoubleCreateFallbackToUpdate()
    {
        $result = DocumentStore::create("/homework/math/prime_numbers.txt", 'content');
        $this->assertTrue($result);
        $result = DocumentStore::create("/homework/math/prime_numbers.txt", 'content');
        $this->assertTrue($result);
        $revisions = DocumentStore::revisions("/homework/math/prime_numbers.txt");
        $this->assertEquals($revisions[0]['type'], 'C');
        $this->assertEquals($revisions[1]['type'], 'U');
    }

    public function executeCreate($result)
    {
        $file = File::find(1);
        $revision = $file->revisions->first();
        $this->assertEquals($file->path, '/homework/math/prime_numbers.txt');
        $this->assertEquals($revision->rev, 'a1c10ce0dd78');
        $this->assertEquals($revision->type, 'C');
        $this->assertEquals($revision->id, $file->revision_id);
    }
}

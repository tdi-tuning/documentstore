<?php

require_once __DIR__.'/../LaravelTestCase.php';

use DocumentStore\Models\File;


class FullTest extends LaravelTestCase
{

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('docstore.access_token', '');
    }

    public function testAll()
    {
        if (!Config::get('docstore.access_token')) return;
        
        $documentStore = App::make('DocumentStore\DocumentStore');

        $result = $documentStore->create('/path/file.txt', __DIR__.'/file1.txt');
        $this->assertTrue($result);
        $content = $documentStore->download('/path/file.txt');
        $this->assertEquals($content, "v1 file\n");

        $result = $documentStore->update('/path/file.txt', __DIR__.'/file2.txt');
        $this->assertTrue($result);
        $content = $documentStore->download('/path/file.txt');
        $this->assertEquals($content, "v2 file\n");

        $revisions = $documentStore->revisions('/path/file.txt');
        $rev1 = $revisions[0]['rev'];
        $rev2 = $revisions[1]['rev'];

        $result = $documentStore->restore('/path/file.txt', $rev1);
        $this->assertTrue($result);
        $content = $documentStore->download('/path/file.txt');
        $this->assertEquals($content, "v1 file\n");

        $result = $documentStore->restore('/path/file.txt', $rev2);
        $this->assertTrue($result);
        $content = $documentStore->download('/path/file.txt');
        $this->assertEquals($content, "v2 file\n");

        $content = $documentStore->download('/path/file.txt', $rev1);
        $this->assertEquals($content, "v1 file\n");
        $content = $documentStore->download('/path/file.txt', $rev2);
        $this->assertEquals($content, "v2 file\n");
        
        $result = $documentStore->delete('/path/file.txt');
        $this->assertTrue($result);

        $revisions = $documentStore->revisions('/path/file.txt');
        $type = $revisions[2]['type'];
        $this->assertEquals($type, 'D');
    }
}

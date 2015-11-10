<?php

require_once __DIR__.'/../LaravelTestCase.php';
require_once __DIR__.'/../Meta.php';


class FullTest extends LaravelTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../migrations')
        ]);
    }

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('docstore.access_token', '');
    }

    public function testCreateDelete()
    {
        if (!Config::get('docstore.access_token')) return;
        
        $meta = new Meta;
        $meta->user_id = 1;
        $result = DocumentStore::create('/path_dir/file1.txt', __DIR__.'/file1.txt', $meta);
        $this->assertTrue($result);

        $meta = new Meta;
        $meta->user_id = 2;
        $result = DocumentStore::delete('/path_dir/file1.txt', $meta);
        $this->assertTrue($result);

        $revisions = DocumentStore::revisions('/path_dir/file1.txt');
        $this->assertEquals(count($revisions), 2);
        $this->assertEquals($revisions[0]['type'], 'C');
        $this->assertEquals($revisions[1]['type'], 'D');

        $this->assertTrue(DocumentStore::isDeleted('/path_dir/file1.txt'));
    }

    public function testAll()
    {
        if (!Config::get('docstore.access_token')) return;
        
        $meta = new Meta;
        $meta->user_id = 1;
        $result = DocumentStore::create('/path_dir/file2.txt', __DIR__.'/file1.txt', $meta);
        $this->assertTrue($result);

        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt');
        $this->assertEquals($content, "v1 file\n");
        
        $url = DocumentStore::createSharedLink('/path_dir/file2.txt');
        $this->assertNotEmpty($url);

        $meta = new Meta;
        $meta->user_id = 2;
        $result = DocumentStore::update('/path_dir/file2.txt', __DIR__.'/file2.txt', $meta);
        $this->assertTrue($result);
        
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt');
        $this->assertEquals($content, "v2 file\n");

        $revisions = DocumentStore::revisions('/path_dir/file2.txt');
        $rev1 = $revisions[0]['rev'];
        $rev2 = $revisions[1]['rev'];

        $result = DocumentStore::restore('/path_dir/file2.txt', $rev1);
        $this->assertTrue($result);
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt');
        $this->assertEquals($content, "v1 file\n");

        $result = DocumentStore::restore('/path_dir/file2.txt', $rev2);
        $this->assertTrue($result);
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt');
        $this->assertEquals($content, "v2 file\n");

        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt', $rev1);
        $this->assertEquals($content, "v1 file\n");
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt', $rev2);
        $this->assertEquals($content, "v2 file\n");
        
        $meta = new Meta;
        $meta->user_id = 3;
        $result = DocumentStore::delete('/path_dir/file2.txt', $meta);
        $this->assertTrue($result);

        $meta = new Meta;
        $meta->user_id = 3;
        $result = DocumentStore::delete('/path_dir/file2.txt', $meta);
        $this->assertFalse($result);

        $revisions = DocumentStore::revisions('/path_dir/file2.txt');
        $this->assertEquals($revisions[0]['type'], 'C');
        $this->assertEquals($revisions[1]['type'], 'U');
        $this->assertEquals($revisions[2]['type'], 'D');

        $this->assertTrue(DocumentStore::isDeleted('/path_dir/file2.txt'));
        $this->assertFalse(DocumentStore::download('/path_dir/file2.txt'));
        $this->assertFalse(DocumentStore::createSharedLink('/path_dir/file2.txt'));
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt', $rev2);
        $this->assertEquals($content, "v2 file\n");

        $result = DocumentStore::restore('/path_dir/file2.txt', $rev2);
        $this->assertTrue($result);
        list($content, $mime) = DocumentStore::download('/path_dir/file2.txt');
        $this->assertEquals($content, "v2 file\n");
    }
}

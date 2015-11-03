<?php

require_once __DIR__.'/../LaravelTestCase.php';
require_once 'Meta.php';

use DocumentStore\DBStorageManager;
use DocumentStore\Models\File;
use App\User;


class DBStorageManagerMetaTest extends LaravelTestCase
{

    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/migrations'),
        ]);
    }

    public function testCreateWithMeta()
    {
        $meta = new Meta;
        $meta->user_id = 12;

        $dbStorageManager = App::make('DocumentStore\DBStorageManager');
        $result = $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], $meta);
        $this->assertTrue($result);
        $file = File::find(1);
        $revision = $file->revisions->first();        
        $this->assertEquals($revision->meta->user_id, 12);
    }

    public function testRevisionsWithMeta()
    {

        $user = new User;
        $user->name = "admin";
        $user->save();

        $meta = new Meta;
        $meta->user_id = 1;

        $dbStorageManager = App::make('DocumentStore\DBStorageManager');
        $result = $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], $meta);
        $this->assertTrue($result);
        $revisions = $dbStorageManager->revisions("/homework/math/prime_numbers.txt", ['meta.user']);
        $this->assertEquals($revisions[0]['meta']['user_id'], 1);
        $this->assertEquals($revisions[0]['meta']['user']['name'], "admin");
    }
}

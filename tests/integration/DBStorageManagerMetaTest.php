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

    public function testDuplicatedRevisionsMetaShouldntBeSaved()
    {
        $meta1 = new Meta;
        $meta1->user_id = 1;

        $dbStorageManager = new DBStorageManager;
        $result1 = $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], $meta1);
        $this->assertTrue($result1);
        
        $meta2 = new Meta;
        $meta2->user_id = 2;
        $result2 = $dbStorageManager->update((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], $meta2);
        $this->assertFalse($result2);

        $meta3 = new Meta;
        $meta3->user_id = 3;
        $result3 = $dbStorageManager->update((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], $meta3);
        $this->assertFalse($result3);

        $file = File::find(1);
        $revisions = $file->revisions;
        $this->assertEquals(count($revisions), 1);

        $metas = Meta::all()->toArray();
        $this->assertEquals(count($metas), 1);
    }
}

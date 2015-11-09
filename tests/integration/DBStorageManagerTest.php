<?php

require_once __DIR__.'/../LaravelTestCase.php';

use DocumentStore\DBStorageManager;
use DocumentStore\Models\File;


class DBStorageManagerTest extends LaravelTestCase
{
    public function testCreate()
    {
        $dbStorageManager = new DBStorageManager;
        $result = $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ]);
        $this->assertTrue($result);
        $file = File::find(1);
        $revision = $file->revisions->first();
        $this->assertEquals($file->path, '/homework/math/prime_numbers.txt');
        $this->assertEquals($file->dp_id, 'id:a4ayc_80_OEAAAAAAAAAXw');
        $this->assertEquals($revision->rev, 'a1c10ce0dd78');
        $this->assertEquals($revision->type, 'C');
        $this->assertEquals($revision->id, $file->revision_id);
    }

    public function testUpdate()
    {
        $dbStorageManager = new DBStorageManager;
        $result = $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ]);
        $this->assertTrue($result);
        $result = $dbStorageManager->update((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd79",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ]);
        $this->assertTrue($result);
        $file = File::find(1);
        $revisions = $file->revisions;
        $this->assertEquals($revisions[0]->rev, 'a1c10ce0dd78');
        $this->assertEquals($revisions[0]->type, 'C');
        $this->assertEquals($revisions[1]->rev, 'a1c10ce0dd79');
        $this->assertEquals($revisions[1]->type, 'U');
        $this->assertEquals($revisions[1]->id, $file->revision_id);
    }

    public function testRestoreAndRevisions()
    {
        $dbStorageManager = new DBStorageManager;
        $dbStorageManager->create((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd78",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ]);
        $dbStorageManager->update((object) [
            "path_lower" => "/homework/math/prime_numbers.txt",
            "rev" => "a1c10ce0dd79",
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ]);
        $file = File::find(1);
        $revisions = $file->revisions;
        $this->assertEquals($file->revision_id, $revisions[1]->id);

        $dbStorageManager->restore((object) [
            "id" => "id:a4ayc_80_OEAAAAAAAAAXw"
        ], "a1c10ce0dd78");
        $file = File::find(1);
        $this->assertEquals($file->revision_id, $revisions[0]->id);

        $revisions = $dbStorageManager->revisions("/homework/math/prime_numbers.txt");
        $this->assertEquals(sizeof($revisions), 2);
        $this->assertTrue($revisions[0]['current']);
    }
}

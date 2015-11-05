<?php namespace DocumentStore;

use DocumentStore\Dropbox\DropboxManager;
use DocumentStore\DBStorageManager;


class DocumentStore
{
    /**
     * Dropbox manager instance
     *
     * @var DocumentStore\Dropbox\DropboxManager
     */
    protected $dropboxManager;

    /**
     * Database storage manager instance
     *
     * @var DocumentStore\DBStorageManager
     */
    protected $dbStorageManager;
    

    /**
     * Create a new DocumentStore instance
     *
     * @param  DocumentStore\Dropbox\DropboxManager $dropboxManager Dropbox manager instance
     * @param  DocumentStore\DBStorageManager $dbStorageManager Database storage manager instance
     * @return void
     */
    public function __construct(DropboxManager $dropboxManager, DBStorageManager $dbStorageManager)
    {
        $this->dropboxManager = $dropboxManager;
        $this->dbStorageManager = $dbStorageManager;
    }

    /**
     * Upload new file
     *
     * @param  string $path dropbox file
     * @param  string $file the file local path
     * @param  object $meta Meta Eloquent object
     * @return bool
     */
    public function create($path, $file, $meta=null)
    {
        if ($this->dbStorageManager->exists($path)) {
            return $this->update($path, $file, $meta);
        }
        $result = $this->dropboxManager->create($path, $file);
        return $this->dbStorageManager->create($result, $meta);
    }

    /**
     * Upload new version of file
     *
     * @param  string $path dropbox file
     * @param  string $file the file local path
     * @param  object $meta Meta Eloquent object
     * @return bool
     */
    public function update($path, $file, $meta=null)
    {
        $rev = $this->dbStorageManager->currentRevision($path);
        $result = $this->dropboxManager->update($path, $file, $rev);
        return $this->dbStorageManager->update($result, $meta);
    }
    
    /**
     * Delete file
     *
     * @param  string $path dropbox file
     * @param  object $meta Meta Eloquent object
     * @return bool
     */
    public function delete($path, $meta=null)
    {
        $result = $this->dropboxManager->delete($path);
        if ($result)
            return $this->dbStorageManager->delete($result, $meta);
        return false;
    }

    /**
     * Restore revision
     *
     * @param  string $path dropbox file
     * @param  string $rev dropbox revision
     * @return bool
     */
    public function restore($path, $rev)
    {
        $result = $this->dropboxManager->restore($path, $rev);
        return $this->dbStorageManager->restore($result, $rev);
    }
    
    /**
     * Download file, returns false if file deleted
     *
     * @param  string $path dropbox file
     * @param  string $rev dropbox revision
     * @return mixed
     */
    public function download($path, $rev=null)
    {
        if ($rev === null) {
            if ($this->isDeleted($path)) return false;
        }

        $mime = \Defr\MimeType::get($path);
        $stream = $this->dropboxManager->download($path, $rev);
        return [$stream, $mime];
    }

    /**
     * Create shared link for path, returns false if file deleted
     *
     * @param  string $path dropbox file
     * @return mixed
     */
    public function createSharedLink($path)
    {
        if ($this->isDeleted($path)) return false;

        $object = $this->dropboxManager->createSharedLink($path);
        if ($object)
            return $object->url;

        return false;
    }

    /**
     * Get if the file is deleted
     *
     * @param  string $path dropbox file
     * @return bool
     */
    public function isDeleted($path)
    {
        return $this->dbStorageManager->isDeleted($path);
    }

    /**
     * Get revisions of file
     *
     * @param  string $path dropbox file
     * @param  array $eagerLoading relashionships to preload
     * @return object
     */
    public function revisions($id, $eagerLoading=[])
    {
        return $this->dbStorageManager->revisions($id, $eagerLoading);
    }
}

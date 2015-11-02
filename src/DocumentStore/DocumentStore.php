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
        return $this->dbStorageManager->delete($result, $meta);
    }

    /**
     * Delete file
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
     * Download file
     *
     * @param  string $path dropbox file
     * @return string
     */
    public function download($path)
    {
        return $this->dropboxManager->download($path);
    }

    /**
     * Create shared link for path
     *
     * @param  string $path dropbox file
     * @return string
     */
    public function createSharedLink($path)
    {
        $object = $this->dropboxManager->createSharedLink($path);
        
        return $object->url;
    }

    /**
     * Get revisions of file
     *
     * @param  string $path dropbox file
     * @return object
     */
    public function revisions($id)
    {
        return $this->dbStorageManager->revisions($id);
    }
}

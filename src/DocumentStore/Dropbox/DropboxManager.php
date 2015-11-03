<?php namespace DocumentStore\Dropbox;

use League\Flysystem\File;
use DocumentStore\Dropbox\DropboxHTTPHandler;


class DropboxManager
{
    /**
     * HTTP handler
     *
     * @var DocumentStore\Dropbox\DropboxHTTPHandler
     */
    protected $httpHandler;


    /**
     * Create a new dropbox instance
     *
     * @param  DocumentStore\Dropbox\DropboxHTTPHandler $httpHandler HTTP handler
     * @return void
     */
    public function __construct(DropboxHTTPHandler $httpHandler)
    {
        $this->httpHandler  = $httpHandler;
    }

    /**
     * Upload new file
     *
     * @param  string $path dropbox file
     * @param  string $file the file local path
     * @return object
     */
    public function create($path, $file)
    {
        return $this->upload($path, $file, 'add');
    }

    /**
     * Upload updated file
     *
     * @param  string $path dropbox file
     * @param  string $file the file local path
     * @return object
     */
    public function update($path, $file, $rev)
    {
        return $this->upload($path, $file, 'overwrite');
    }

    /**
     * Delete file
     *
     * @param  string $path dropbox file
     * @return object
     */
    public function delete($path)
    {
        $url = "https://api.dropboxapi.com/2/files/delete";

        return $this->post($url, ['path' => $path], [], '', true, false, false);
    }

    /**
     * Restore file to revision
     *
     * @param  string $path dropbox file
     * @param  string $rev dropbox revision
     * @return object
     */
    public function restore($path, $rev)
    {
        $url = "https://api.dropboxapi.com/2/files/restore";
        
        return $this->post($url, [
            'path' => $path,
            'rev' => $rev
        ], [], '', true, false, false);
    }

    /**
     * Download a file with a revision
     *
     * @param  string $path dropbox file
     * @param  string $rev dropbox revision
     * @return string
     */
    public function download($path, $rev=null)
    {
        $url = "https://content.dropboxapi.com/2/files/download";
        
        if ($rev) {
            return $this->post($url, ['path' => $path, 'rev' => $rev], [], '', false, false, true, false);
        }
        return $this->post($url, ['path' => $path], [], '', false, false, true, false);
    }

    /**
     * Create shared link for path
     *
     * @param  string $path dropbox file
     * @return object
     */
    public function createSharedLink($path)
    {
        $url = "https://api.dropboxapi.com/2/sharing/create_shared_link";

        return $this->post($url, [
            'path' => $path,
            'short_url' => false
        ], [], '', true);
    }

    /**
     * Upload file
     *
     * @param  string $path dropbox file
     * @param  string $file the file local path
     * @param  string $mode mode
     * @return object
     */
    private function upload($path, $file, $mode)
    {
        $url = "https://content.dropboxapi.com/2/files/upload";
        
        return $this->post($url, [
            "path"       => $path,
            "mode"       => $mode,
            "autorename" => false,
            "mute"       => true
        ], [
            'Content-Type' =>'application/octet-stream'
        ], $file, false, true, true);
    }

    /**
     * Send POST request
     *
     * @param  string $url url
     * @param  array $params json params
     * @param  array $headers headers
     * @param  string $body body or file path
     * @param  bool $isJson use params as body in JSON format
     * @param  bool $isStream create a stream as body
     * @param  bool $useHeader force using Dropbox-API-Arg
     * @param  bool $isJsonResponse decode JSON response or not
     * @return object
     */
    private function post($url, $params, $headers=[], $body='',
         $isJson=false, $isStream=false, $useHeader=false, $isJsonResponse=true)
    {
        $response = $this->httpHandler->post($url, $params, $headers, $body, $isJson, $isStream, $useHeader);

        if ($isJsonResponse) {
            return json_decode($response->getBody());
        }
        return $response->getBody()->getContents();
    }
}

<?php namespace DocumentStore\Dropbox;

use League\Flysystem\File;
use GuzzleHttp\Client;


class DropboxHTTPHandler
{
    /**
     * Access token
     *
     * @var string
     */
    protected $access_token;

    /**
     * Http client
     *
     * @var GuzzleHttp\Client
     */
    protected $client;

    /**
     * Test mode
     *
     * @var bool
     */
    protected $test_mode;


    /**
     * Create a new dropbox http client instance
     *
     * @param  string $access_token Access token
     * @param  GuzzleHttp\Client $client Http client
     * @return void
     */
    public function __construct($access_token, Client $client, $test_mode=false)
    {
        $this->access_token = $access_token;
        $this->client = $client;
        $this->test_mode = $test_mode;
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
     * @return GuzzleHttp\Psr7\Response
     */
    public function post($url, $params, $headers=[], $body='',
         $isJson=false, $isStream=false, $useHeader=false)
    {
        if ($isJson) {
            $body = json_encode($params);
            $headers = array_merge($headers, [
                'Content-Type' => 'application/json'
            ]);
        }

        if ($useHeader) {
            $headers = array_merge($headers, [
                'Dropbox-API-Arg' => json_encode($params)
            ]);
        }

        if ($isStream && !$this->test_mode) {
            $body = fopen($body, 'r');
        }

        $response = $this->client->request('POST', $url, [
            'headers' => array_merge($headers, [
                'Authorization' => 'Bearer '.$this->access_token
            ]),
            'body' => $body
        ]);

        return $response;
    }

}

<?php namespace DocumentStore;

use Illuminate\Support\ServiceProvider;
use DocumentStore\Dropbox\DropboxHTTPHandler;
use DocumentStore\DBStorageManager;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Handler\CurlHandler;


class DocumentStoreServiceProvider extends ServiceProvider
{
    
    /**
     * Indicates of loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;
    

    /**
     * Boot the service provider
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/docstore.php' => config_path('docstore.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/../migrations/' => database_path('migrations')
        ], 'migrations');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('DocumentStore\Dropbox\DropboxHTTPHandler', function ($app) {
            $stack = new HandlerStack();
            $stack->setHandler(new CurlHandler());
            $client = new Client(['handler' => $stack]);
            return new DropboxHTTPHandler($app['config']['docstore']['access_token'], $client);
        });
        
        $this->app->singleton('DocumentStore\DBStorageManager', function ($app) {
            return new DBStorageManager;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['DocumentStore\DocumentStore'];
    }
}

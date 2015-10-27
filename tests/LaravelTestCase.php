<?php


class LaravelTestCase extends Orchestra\Testbench\TestCase
{

    protected function getPackageProviders($app)
    {
        return ['DocumentStore\DocumentStoreServiceProvider'];
    }

    protected function getPackageAliases($app)
    {
        return [
            'DocumentStore' => 'DocumentStore\DocumentStoreFacade'
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('docstore', [
            'access_token'  => 'testkey',
            'meta_table'    => 'docstore_meta',
            'meta_table_id' => 'id',
            'meta_model'    => '\Meta'
        ]);
    }

    public function setUp()
    {
        parent::setUp();
        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__."/../src/migrations"),
        ]);
    }
}

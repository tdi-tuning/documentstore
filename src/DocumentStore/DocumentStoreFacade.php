<?php namespace DocumentStore;

use Illuminate\Support\Facades\Facade;


class DocumentStoreFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'DocumentStore\DocumentStore';
    }
}

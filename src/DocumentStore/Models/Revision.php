<?php namespace DocumentStore\Models;

use Illuminate\Database\Eloquent\Model;


class Revision extends Model {
    
    protected $table = 'docstore_revisions';

    function file() {
        return $this->belongsTo('DocumentStore\Models\File');
    }

    function meta() {
        return $this->belongsTo(\Config::get('docstore.meta_model'));
    }
}

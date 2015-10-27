<?php namespace DocumentStore\Models;

use Illuminate\Database\Eloquent\Model;


class File extends Model {
    
    protected $table = 'docstore_files';

    function revisions() {
        return $this->hasMany('DocumentStore\Models\Revision');
    }

    function revision() {
        return $this->belongsTo('DocumentStore\Models\Revision');
    }
}

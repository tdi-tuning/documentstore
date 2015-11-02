<?php

use Illuminate\Database\Eloquent\Model;


class Meta extends Model {

    protected $table = 'docstore_meta';
    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}

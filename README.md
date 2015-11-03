# Installation

Add service provider and facade:

```
DocumentStore\DocumentStoreServiceProvider::class
```

```
'DocumentStore' => DocumentStore\DocumentStoreFacade::class
```

Run:

```
php artisan vendor:publish
```

Create Meta model:

```
<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


class Meta extends Model {

    protected $table = 'docstore_meta';
    public $timestamps = false;
}
```


Edit config:

```
<?php

return [

    // Dropbox access token
    'access_token' => 'testkey',

    // Meta table
    'meta_table' => 'docstore_meta',

    // Meta table primary key
    'meta_table_id' => 'id',

    // Meta model class
    'meta_model' => '\App\Meta'

];
```

Run migration:

```
php artisan migrate
```


# Methods

## Upload a file to a path
```
$result = DocumentStore::create('/path/file.txt', '/file1.txt', $meta);
```

## Upload new version of file to a path
```
$result = DocumentStore::update('/path/file.txt', '/file2.txt', $meta);
```

## Download latest version of a file
```
$content = DocumentStore::download('/path/file.txt');
```

## Download other version of a file
```
$content = DocumentStore::download('/path/file.txt', 'revision');
```

## Get revisions of a file
```
$revisions = DocumentStore::revisions('/path/file.txt');
```

## Get revisions of a file with eager loading
```
$revisions = DocumentStore::revisions('/path/file.txt', ['meta.xxx']);
```


## Restore a file to a version
```
$result = DocumentStore::restore('/path/file.txt', 'revision');
```

## Delete a file
```
$result = DocumentStore::delete('/path/file.txt', $meta);
```

## Create public link to a file
```
$link = DocumentStore::createSharedLink('/path/file.txt');
```

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model{

    protected $table = 'products';
    // ulid as primary key
    protected $keyType = 'string';

}

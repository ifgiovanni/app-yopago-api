<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Status extends Model{

    use SoftDeletes;
    use HasUlids;

    protected $table = 'cat_status';
    // ulid as primary key
    protected $keyType = 'string';

    
}

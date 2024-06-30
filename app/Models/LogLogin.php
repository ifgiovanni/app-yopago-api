<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

use Illuminate\Database\Eloquent\Model;

class LogLogin extends Model{

    use SoftDeletes;
    use HasUlids;

    protected $table = 'logs_logins';
    // ulid as primary key
    protected $keyType = 'string';

}

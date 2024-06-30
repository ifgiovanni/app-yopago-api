<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class DepositOption extends Model{

    use SoftDeletes;
    use HasUlids;

    protected $table = 'deposit_options';
    // ulid as primary key
    protected $keyType = 'string';

}

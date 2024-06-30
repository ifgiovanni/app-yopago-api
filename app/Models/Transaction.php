<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model{

    use SoftDeletes;
    use HasUlids;

    protected $table = 'transactions';
    // ulid as primary key
    protected $keyType = 'string';

    // with product

    public function product(){
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    // status
    public function status(){
        return $this->belongsTo('App\Models\Status', 'status_id', 'id');
    }
}

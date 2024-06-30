<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Coupon extends Model{

    use SoftDeletes;
    use HasUlids;

    protected $table = 'coupons';
    // ulid as primary key
    protected $keyType = 'string';

    // user
    public function createdBy(){
        return $this->belongsTo('App\Models\User', 'created_by', 'id');
    }

    // user
    public function redeemBy(){
        return $this->belongsTo('App\Models\User', 'redeem_by', 'id');
    }

    // user
    public function blockedBy(){
        return $this->belongsTo('App\Models\User', 'blocked_by', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model{

    protected $table = 'user_has_roles';
    // ulid as primary key
    protected $keyType = 'string';

    // join with roles table
    public function role(){
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }
}

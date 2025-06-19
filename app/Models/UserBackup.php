<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBackup extends Model
{
    protected $fillable = [
        'user_id',
        'path'
    ];
}

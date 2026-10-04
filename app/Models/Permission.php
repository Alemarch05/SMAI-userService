<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
     protected $table = 'US_permissions';

    protected $fillable = [
        'name',
        'description',
    ];

}   
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    // Indicamos a Eloquent que la clave primaria no es 'id', sino 'permissions_id'
    protected $primaryKey = 'permissions_id';

    protected $fillable = [
        'role_id',
        'name',
        'description',
    ];

    /**
     * Relación: Un Permiso pertenece a un Rol.
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}   
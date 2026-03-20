<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    use HasFactory;

    protected $fillable = [
        'marca',
        'modelo',
        'tipo',
        'imagen_url',
    ];

    // Relación: Un dispositivo tiene muchas averías configuradas
    public function averias()
    {
        return $this->hasMany(Averia::class);
    }
}
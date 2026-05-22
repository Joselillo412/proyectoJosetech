<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    use HasFactory;

    // Campos que Laravel tiene permitido guardar
    protected $fillable = [
        'marca',
        'modelo',
        'tipo',
        'imagen_url'
    ];
}

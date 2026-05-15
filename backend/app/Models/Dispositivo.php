<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dispositivo extends Model
{
    use HasFactory;

    // ESTO ES LO QUE DA PERMISO A LA API PARA ESCRIBIR EN LA BD
    protected $fillable = ['marca', 'modelo', 'tipo']; 
}
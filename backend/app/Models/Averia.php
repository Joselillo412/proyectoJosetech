<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Averia extends Model
{
    use HasFactory;

    protected $fillable = [
        'dispositivo_id',
        'nombre',
        'precio',
    ];

    // Relación: Esta avería pertenece a un dispositivo
    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class);
    }

    // Relación: Esta avería puede estar en muchas líneas de pedido diferentes
    public function lineaPedidos()
    {
        return $this->hasMany(LineaPedido::class);
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'codigo_seguimiento',
        'estado',
        'direccion_recogida',
        'total',
    ];

    // Relación: Un pedido pertenece a un usuario (cliente)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relación: Un pedido tiene varias líneas de detalle
    public function lineas()
    {
        return $this->hasMany(LineaPedido::class);
    }
}
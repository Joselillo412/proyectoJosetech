<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineaPedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'pedido_id',
        'averia_id',
        'precio_aplicado',
    ];

    // Relación: Esta línea pertenece a un pedido concreto
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    // Relación: Esta línea hace referencia a una avería concreta
    public function averia()
    {
        return $this->belongsTo(Averia::class);
    }
}
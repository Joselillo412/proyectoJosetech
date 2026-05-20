<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contabilidad extends Model
{
    use HasFactory;
    protected $table = 'contabilidad';

    protected $fillable = [
        'pedido_id',
        'piezas_cambiadas',
        'coste_piezas',
        'ganancias',
        'total_cobrado',
        'metodo_pago'
    ];
}
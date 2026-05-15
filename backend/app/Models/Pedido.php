<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasFactory;

    // Estos son los campos que Laravel PERMITIRÁ guardar. 
    // Si falta uno aquí, MySQL dirá que "no tiene valor por defecto".
    protected $fillable = [
        'user_id',
        'dispositivo_id',
        'codigo_seguimiento',
        'tipo_reparacion',
        'descripcion',
        'precio_estimado',
        'estado'
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function dispositivo()
    {
        return $this->belongsTo(Dispositivo::class, 'dispositivo_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($pedido) {
            if (empty($pedido->codigo_seguimiento)) {
                $pedido->codigo_seguimiento = 'JT-' . strtoupper(Str::random(6));
            }
        });
    }
}
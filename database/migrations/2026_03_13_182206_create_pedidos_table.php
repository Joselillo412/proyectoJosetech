<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecutar las migraciones (Crear la tabla).
     */
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('dispositivo_id')->constrained('dispositivos')->onDelete('cascade');
            $table->string('codigo_seguimiento')->unique();
            $table->string('tipo_reparacion');
            $table->text('descripcion')->nullable();
            $table->string('precio_estimado');
            $table->string('estado')->default('Pendiente');
            $table->timestamps();
        });
    }

    /**
     * Deshacer las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
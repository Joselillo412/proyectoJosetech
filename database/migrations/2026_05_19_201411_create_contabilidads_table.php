<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contabilidad', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos')->onDelete('cascade');
            $table->string('piezas_cambiadas')->nullable();
            $table->decimal('coste_piezas', 8, 2)->default(0);
            $table->decimal('ganancias', 8, 2);
            $table->decimal('total_cobrado', 8, 2);
            $table->string('metodo_pago')->default('Efectivo'); // 'Efectivo' o 'Tarjeta'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contabilidads');
    }
};

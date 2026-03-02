<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compra_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('compra_id')->constrained('compras')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->restrictOnDelete();
            $table->unsignedInteger('quantidade');
            $table->unsignedBigInteger('preco_unitario'); // centavos
            $table->unsignedBigInteger('subtotal')->storedAs('quantidade * preco_unitario'); // centavos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compra_produto');
    }
};

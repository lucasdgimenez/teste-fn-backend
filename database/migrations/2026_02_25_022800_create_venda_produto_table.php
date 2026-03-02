<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venda_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venda_id')->constrained('vendas')->cascadeOnDelete();
            $table->foreignId('produto_id')->constrained('produtos')->restrictOnDelete();
            $table->unsignedInteger('quantidade');
            $table->unsignedBigInteger('preco_unitario'); // centavos
            $table->unsignedBigInteger('subtotal')->storedAs('quantidade * preco_unitario'); // centavos
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venda_produto');
    }
};

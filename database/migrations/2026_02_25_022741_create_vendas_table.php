<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('cliente');
            $table->unsignedBigInteger('total')->default(0); // centavos — receita bruta
            $table->bigInteger('lucro')->default(0);         // centavos — pode ser negativo
            $table->enum('status', ['concluida', 'cancelada'])->default('concluida');
            $table->date('data_venda');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};

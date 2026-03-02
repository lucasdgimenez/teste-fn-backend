<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->string('fornecedor');
            $table->unsignedBigInteger('total')->default(0); // centavos
            $table->enum('status', ['recebida', 'cancelada'])->default('recebida');
            $table->date('data_compra');
            $table->text('observacao')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('compras');
    }
};

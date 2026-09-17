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
        Schema::create('lancamentos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('tipo');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->date('data');
            $table->string('status')->default('pendente');
            $table->string('forma_pagamento')->nullable();

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();
            $table->foreignId('contraparte_id')
                ->nullable()
                ->constrained('contrapartes')
                ->nullOnDelete();
            $table->foreignId('recorrencia_id')
                ->nullable()
                ->constrained('recorrencias')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['empresa_id', 'data', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lancamentos');
    }
};

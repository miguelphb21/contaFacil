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
        Schema::create('recorrencias', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('tipo');
            $table->string('descricao');
            $table->decimal('valor', 10, 2);
            $table->unsignedTinyInteger('dia');
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->string('forma_pagamento')->nullable();
            $table->boolean('ativa')->default(true);

            $table->foreignId('categoria_id')
                ->nullable()
                ->constrained('categorias')
                ->nullOnDelete();
            $table->foreignId('contraparte_id')
                ->nullable()
                ->constrained('contrapartes')
                ->nullOnDelete();

            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencias');
    }
};

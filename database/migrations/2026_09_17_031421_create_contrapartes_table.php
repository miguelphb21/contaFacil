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
        Schema::create('contrapartes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('tipo');
            $table->string('nome');
            $table->string('cpf_cnpj')->nullable();
            $table->string('telefone')->nullable();
            $table->string('email')->nullable();
            $table->text('endereco')->nullable();

            $table->timestamps();

            $table->index(['empresa_id', 'tipo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrapartes');
    }
};

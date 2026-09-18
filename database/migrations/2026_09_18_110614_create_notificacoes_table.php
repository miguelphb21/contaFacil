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
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('lancamento_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('mensagem');
            $table->timestamp('lido_at')->nullable();
            $table->timestamps();

            $table->unique('lancamento_id');
            $table->index(['empresa_id', 'lido_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};

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
        Schema::create('recorrencia_exclusoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recorrencia_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->date('mes');
            $table->timestamps();

            $table->unique(['recorrencia_id', 'mes']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recorrencia_exclusoes');
    }
};

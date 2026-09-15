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
        Schema::create('parcelas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conta_id')
                ->constrained('contas')
                ->cascadeOnDelete();

            $table->unsignedInteger('numero');
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->string('status')->default('pendente');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parcelas');
    }
};

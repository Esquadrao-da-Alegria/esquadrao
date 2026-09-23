<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_sessoes_presenca', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('aberta_por_id')->constrained('users')->restrictOnDelete();
            $table->timestamp('aberta_em');
            $table->timestamp('encerrada_em')->nullable();
            $table->foreignId('encerrada_por_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['evento_id', 'encerrada_em']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_sessoes_presenca');
    }
};

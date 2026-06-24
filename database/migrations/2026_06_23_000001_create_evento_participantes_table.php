<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('evento_participantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('evento_id')->constrained('eventos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('inscrito');
            $table->timestamp('inscrito_em')->nullable();
            $table->timestamp('cancelado_em')->nullable();
            $table->timestamps();

            $table->unique(['evento_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_participantes');
    }
};

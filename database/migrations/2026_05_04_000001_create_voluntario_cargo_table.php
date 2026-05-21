<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntario_cargo', function (Blueprint $table) {
            $table->id();

            $table->foreignId('voluntario_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cargo_id')->constrained('cargos')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['voluntario_id', 'cargo_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('voluntario_cargo');
    }
};

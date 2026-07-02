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
        Schema::create('alas_hospitais', function (Blueprint $table) {
            $table->id();

            $table->string('nome', 255);
            $table->text('observacoes')->nullable();
            $table->foreignId('hospital_id')->constrained('hospitais')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alas_hospitais');
    }
};

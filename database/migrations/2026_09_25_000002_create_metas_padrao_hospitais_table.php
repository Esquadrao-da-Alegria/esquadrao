<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('metas_padrao_hospitais', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hospital_id')->unique()->constrained('hospitais')->restrictOnDelete();
            $table->unsignedSmallInteger('quantidade');
            $table->string('periodicidade', 20);
            $table->boolean('metas_por_ala')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metas_padrao_hospitais');
    }
};

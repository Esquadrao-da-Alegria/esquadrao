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
            $table->unsignedBigInteger('evento_id');
            $table->unsignedBigInteger('user_id');
            $table->string('status')->default('INSCRITO');
            $table->unsignedBigInteger('presenca_confirmada_por_id')->nullable();
            $table->timestamp('presenca_confirmada_em')->nullable();
            $table->timestamps();

            $table->unique(['evento_id', 'user_id']);
            $table->index('status');

            $table->foreign('evento_id')->references('id')->on('eventos')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('presenca_confirmada_por_id')->references('id')->on('users')->onDelete('set null'); //Permitir que o usuário que confirmou a presença seja deletado sem afetar os registros de participação
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evento_participantes');
    }
};

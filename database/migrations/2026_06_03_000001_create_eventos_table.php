<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['OFICINA', 'REUNIAO']);
            $table->string('titulo', 255);  
            $table->text('descricao');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->string('complemento', 500)->nullable();
            $table->decimal('local_latitude', 10, 7)->nullable();
            $table->decimal('local_longitude', 10, 7)->nullable();
            $table->unsignedBigInteger('cidade_id');
            $table->string('status')->default('AGENDADO');
            $table->unsignedInteger('limite_vagas')->nullable(); //Nullable caso o evento não tenha limite de vagas
            $table->boolean('feedback_habilitado')->default(false);
            $table->unsignedBigInteger('evento_origem_id')->nullable();
            $table->unsignedBigInteger('criado_por_id');
            $table->timestamps();
            $table->softDeletes();

            $table->index('cidade_id');
            $table->index('status');
            $table->index('data_inicio');

            $table->foreign('cidade_id')->references('id')->on('cidades')->onDelete('restrict');
            $table->foreign('evento_origem_id')->references('id')->on('eventos')->onDelete('set null');
            $table->foreign('criado_por_id')->references('id')->on('users')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};

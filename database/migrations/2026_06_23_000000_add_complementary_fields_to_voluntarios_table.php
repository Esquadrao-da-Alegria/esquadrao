<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->string('nome_doutor')->nullable();
            $table->string('cpf', 14)->nullable();
            $table->date('data_entrada_ong')->nullable();
            $table->text('observacoes')->nullable();
        });

        Schema::table('voluntarios', function (Blueprint $table) {
            $table->unique('cpf');
        });
    }

    public function down(): void
    {
        Schema::table('voluntarios', function (Blueprint $table) {
            $table->dropUnique('voluntarios_cpf_unique');
            $table->dropColumn([
                'nome_doutor',
                'cpf',
                'data_entrada_ong',
                'observacoes',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convites_cadastro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voluntario_id')->constrained('voluntarios')->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->string('email');
            $table->string('status', 40)->default('PENDENTE');
            $table->timestamp('enviado_em')->nullable();
            $table->timestamp('utilizado_em')->nullable();
            $table->timestamp('expira_em')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['voluntario_id', 'status']);
            $table->index('email');
            $table->index('expira_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convites_cadastro');
    }
};

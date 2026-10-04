<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lembrete_entregas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('lembrete_id')
                ->constrained('lembretes')
                ->cascadeOnDelete();

            $table->string('atividade_tipo');
            $table->unsignedBigInteger('atividade_id');
            $table->string('tipo');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('push_subscription_id')
                ->constrained('push_subscriptions')
                ->cascadeOnDelete();

            $table->string('status')->default('pendente');
            $table->text('erro')->nullable();
            $table->dateTime('enviado_em')->nullable();

            $table->timestamps();

            $table->unique(
                ['atividade_tipo', 'atividade_id', 'tipo', 'push_subscription_id'],
                'lembrete_entregas_unique_entrega'
            );
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lembrete_entregas');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evento_participantes', function (Blueprint $table) {
            $table->string('presenca')->nullable()->after('cancelado_em');
            $table->timestamp('presenca_registrada_em')->nullable()->after('presenca');
            $table->foreignId('presenca_registrada_por_id')->nullable()->constrained('users')->nullOnDelete()->after('presenca_registrada_em');
            $table->text('observacao_presenca')->nullable()->after('presenca_registrada_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('evento_participantes', function (Blueprint $table) {
            $table->dropForeign(['presenca_registrada_por_id']);
            $table->dropColumn(['presenca', 'presenca_registrada_em', 'presenca_registrada_por_id', 'observacao_presenca']);
        });
    }
};

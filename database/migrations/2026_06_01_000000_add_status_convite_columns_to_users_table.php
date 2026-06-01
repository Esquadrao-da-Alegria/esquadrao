<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('status', 40)->default('ativo')->after('email_verified_at');
            $table->timestamp('convite_enviado_em')->nullable()->after('status');
            $table->timestamp('convite_expira_em')->nullable()->after('convite_enviado_em');
            $table->timestamp('inativado_em')->nullable()->after('convite_expira_em');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'convite_enviado_em',
                'convite_expira_em',
                'inativado_em',
            ]);
        });
    }
};

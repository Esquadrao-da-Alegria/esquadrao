<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (Schema::hasColumn('eventos', 'semestre')) {
                $table->dropColumn('semestre');
            }
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            if (!Schema::hasColumn('eventos', 'semestre')) {
                $table->string('semestre', 50)->after('cidade_id');
            }
        });
    }
};

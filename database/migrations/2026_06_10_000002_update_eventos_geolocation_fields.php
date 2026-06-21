<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('eventos', 'local')) {
            return;
        }

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn('local');
            $table->string('complemento', 500)->nullable()->after('data_fim');
            $table->decimal('local_latitude', 10, 7)->nullable()->after('complemento');
            $table->decimal('local_longitude', 10, 7)->nullable()->after('local_latitude');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('eventos', 'complemento')) {
            return;
        }

        Schema::table('eventos', function (Blueprint $table) {
            $table->dropColumn(['complemento', 'local_latitude', 'local_longitude']);
            $table->string('local', 255)->after('data_fim');
        });
    }
};

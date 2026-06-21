<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['evento_origem_id']);
            $table->dropColumn('evento_origem_id');
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->unsignedBigInteger('evento_origem_id')->nullable()->after('feedback_habilitado');
            $table->foreign('evento_origem_id')->references('id')->on('eventos')->onDelete('set null');
        });
    }
};

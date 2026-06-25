<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('voluntarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome_completo');
            $table->string('email')->unique();
            $table->string('telefone', 30)->nullable();
            $table->date('data_nascimento')->nullable();
            $table->foreignId('cidade_base_id')->nullable()->constrained('cidades')->nullOnDelete();
            $table->string('status', 40)->default('ativo');
            $table->timestamps();
        });

        if (DB::getDriverName() === 'sqlite') {
            DB::statement('ALTER TABLE users ADD COLUMN voluntario_id INTEGER REFERENCES voluntarios(id) ON DELETE SET NULL');
            DB::statement('CREATE INDEX users_voluntario_id_index ON users (voluntario_id)');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('voluntario_id')
                    ->nullable()
                    ->constrained('voluntarios')
                    ->nullOnDelete();
            });
        }

        $usuariosVoluntarios = DB::table('users')
            ->whereExists(function ($query) {
                $query
                    ->select(DB::raw(1))
                    ->from('voluntario_cargo')
                    ->join('cargos', 'cargos.id', '=', 'voluntario_cargo.cargo_id')
                    ->whereColumn('voluntario_cargo.voluntario_id', 'users.id')
                    ->where('cargos.slug', '!=', 'administrador');
            })
            ->get();

        foreach ($usuariosVoluntarios as $usuario) {
            $voluntarioId = DB::table('voluntarios')->insertGetId([
                'nome_completo' => $usuario->name,
                'email' => $usuario->email,
                'status' => $usuario->status ?? 'ativo',
                'created_at' => $usuario->created_at,
                'updated_at' => $usuario->updated_at,
            ]);

            DB::table('users')
                ->where('id', $usuario->id)
                ->update(['voluntario_id' => $voluntarioId]);
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex('users_voluntario_id_index');
                $table->dropColumn('voluntario_id');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('voluntario_id');
            });
        }

        Schema::dropIfExists('voluntarios');
    }
};

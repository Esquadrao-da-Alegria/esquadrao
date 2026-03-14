<?php

namespace App\Console\Commands;

use App\Models\Role;
use Illuminate\Console\Command;

class SetupRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Setup default roles for the system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up default roles...');

        $roles = [
            'admin' => 'Administrador do Sistema',
            'diretor' => 'Diretor',
            'coordenador' => 'Coordenador',
            'voluntario' => 'Voluntário'
        ];

        foreach ($roles as $roleName => $description) {
            $role = Role::where('nomeRole', $roleName)->first();
            
            if (!$role) {
                Role::create(['nomeRole' => $roleName]);
                $this->info("✓ Created role: {$roleName} ({$description})");
            } else {
                $this->line("→ Role already exists: {$roleName}");
            }
        }

        $this->info('Roles setup completed!');
        
        // Mostrar estatísticas
        $totalRoles = Role::count();
        $this->info("Total roles in system: {$totalRoles}");
    }
}
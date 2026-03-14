<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Role;
use App\Models\Invitation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateFirstAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create-first 
                            {--name= : Admin name}
                            {--email= : Admin email}
                            {--password= : Admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the first admin user with invitation system';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Verificar se já existem usuários
        if (User::count() > 0) {
            $this->error('Users already exist in the system. This command is only for the first admin.');
            return 1;
        }

        // Coletar dados do admin
        $name = $this->option('name') ?: $this->ask('Admin name');
        $email = $this->option('email') ?: $this->ask('Admin email');
        $password = $this->option('password') ?: $this->secret('Admin password');

        if (!$name || !$email || !$password) {
            $this->error('Name, email and password are required.');
            return 1;
        }

        // Verificar se email já existe
        if (User::where('email', $email)->exists()) {
            $this->error('User with this email already exists.');
            return 1;
        }

        // Verificar se email já tem convite
        if (Invitation::where('email', $email)->exists()) {
            $this->error('Invitation for this email already exists.');
            return 1;
        }

        // Criar convite para o admin
        $invitation = Invitation::create([
            'email' => $email,
            'invitation_token' => \Illuminate\Support\Str::uuid(),
        ]);

        // Buscar ou criar role de diretor
        $role = Role::where('nomeRole', 'diretor')->first();
        if (!$role) {
            $role = Role::create(['nomeRole' => 'diretor']);
        }

        // Criar usuário admin
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role_id' => $role->id,
            'active' => true,
        ]);

        // Marcar convite como usado
        $invitation->markAsRegistered();

        $this->info('✅ First admin created successfully!');
        $this->info("Name: {$name}");
        $this->info("Email: {$email}");
        $this->info("Role: Diretor");
        $this->info("Invitation token: {$invitation->invitation_token}");
        
        $this->newLine();
        $this->info('You can now login with these credentials.');
        $this->info('The admin has full permissions to manage the system.');

        return 0;
    }
}
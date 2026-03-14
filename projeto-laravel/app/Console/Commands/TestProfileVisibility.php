<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Enums\ProfileVisibility;
use Illuminate\Console\Command;

class TestProfileVisibility extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:profile-visibility';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test profile visibility functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing Profile Visibility Functionality');
        $this->newLine();

        // Mostrar opções do enum
        $this->info('Available visibility options:');
        foreach (ProfileVisibility::getOptionsWithDescriptions() as $option) {
            $this->line("• {$option['value']}: {$option['label']}");
            $this->line("  {$option['description']}");
        }

        $this->newLine();

        // Mostrar usuários e suas visibilidades
        $users = User::with('role')->get();
        
        if ($users->isEmpty()) {
            $this->info('No users found in the system.');
            return 0;
        }

        $this->info('Current users and their profile visibility:');
        $this->table(
            ['Name', 'Email', 'Role', 'Profile Visibility', 'Status'],
            $users->map(function ($user) {
                return [
                    $user->name,
                    $user->email,
                    $user->role?->nomeRole ?? 'No Role',
                    $user->profile_visibility?->getLabel() ?? 'Not Set',
                    $user->active ? 'Active' : 'Inactive'
                ];
            })
        );

        // Estatísticas
        $publicCount = User::where('profile_visibility', ProfileVisibility::PUBLIC)->count();
        $privateCount = User::where('profile_visibility', ProfileVisibility::PRIVATE)->count();
        $notSetCount = User::whereNull('profile_visibility')->count();

        $this->newLine();
        $this->info('Statistics:');
        $this->line("• Public profiles: {$publicCount}");
        $this->line("• Private profiles: {$privateCount}");
        $this->line("• Not set: {$notSetCount}");
        $this->line("• Total users: {$users->count()}");

        return 0;
    }
}
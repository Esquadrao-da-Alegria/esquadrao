<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Illuminate\Console\Command;

class ManageInvitations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invitations:manage 
                            {action : Action to perform (list|remove|clean)}
                            {--email= : Email address for specific operations}
                            {--used : Only show/remove used invitations}
                            {--unused : Only show/remove unused invitations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage invitation system - list, remove or clean invitations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');
        $email = $this->option('email');
        $used = $this->option('used');
        $unused = $this->option('unused');

        switch ($action) {
            case 'list':
                $this->listInvitations($email, $used, $unused);
                break;
            case 'remove':
                $this->removeInvitations($email, $used, $unused);
                break;
            case 'clean':
                $this->cleanInvitations($used, $unused);
                break;
            default:
                $this->error('Invalid action. Use: list, remove, or clean');
                return 1;
        }

        return 0;
    }

    private function listInvitations($email = null, $used = false, $unused = false)
    {
        $query = Invitation::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($used) {
            $query->whereNotNull('registered_at');
        }

        if ($unused) {
            $query->whereNull('registered_at');
        }

        $invitations = $query->orderBy('created_at', 'desc')->get();

        if ($invitations->isEmpty()) {
            $this->info('No invitations found.');
            return;
        }

        $this->info('Invitations:');
        $this->table(
            ['Email', 'Token', 'Status', 'Created', 'Registered'],
            $invitations->map(function ($invitation) {
                return [
                    $invitation->email,
                    substr($invitation->invitation_token, 0, 8) . '...',
                    $invitation->registered_at ? 'Used' : 'Unused',
                    $invitation->created_at->format('Y-m-d H:i'),
                    $invitation->registered_at ? $invitation->registered_at->format('Y-m-d H:i') : '-'
                ];
            })
        );
    }

    private function removeInvitations($email = null, $used = false, $unused = false)
    {
        if (!$email && !$used && !$unused) {
            $this->error('You must specify --email, --used, or --unused option');
            return;
        }

        $query = Invitation::query();

        if ($email) {
            $query->where('email', $email);
        }

        if ($used) {
            $query->whereNotNull('registered_at');
        }

        if ($unused) {
            $query->whereNull('registered_at');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No invitations found to remove.');
            return;
        }

        if ($this->confirm("Are you sure you want to remove {$count} invitation(s)?")) {
            $query->delete();
            $this->info("Successfully removed {$count} invitation(s).");
        } else {
            $this->info('Operation cancelled.');
        }
    }

    private function cleanInvitations($used = false, $unused = false)
    {
        if (!$used && !$unused) {
            $this->error('You must specify --used or --unused option for clean operation');
            return;
        }

        if ($used && $unused) {
            $this->error('Cannot specify both --used and --unused for clean operation');
            return;
        }

        $query = Invitation::query();

        if ($used) {
            $query->whereNotNull('registered_at');
        }

        if ($unused) {
            $query->whereNull('registered_at');
        }

        $count = $query->count();

        if ($count === 0) {
            $this->info('No invitations found to clean.');
            return;
        }

        $type = $used ? 'used' : 'unused';
        if ($this->confirm("Are you sure you want to clean all {$count} {$type} invitation(s)?")) {
            $query->delete();
            $this->info("Successfully cleaned {$count} {$type} invitation(s).");
        } else {
            $this->info('Operation cancelled.');
        }
    }
}
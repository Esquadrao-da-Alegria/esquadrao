<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    protected $fillable = [
        'email', 'invitation_token', 'registered_at',
    ];

    public function generateInvitationToken() {
        $this->invitation_token = Str::uuid();
    }

    public function markAsRegistered() {
        $this->registered_at = now();
        $this->save();
    }

    public function isRegistered() {
        return !is_null($this->registered_at);
    }
}
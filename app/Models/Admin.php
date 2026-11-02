<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable implements FilamentUser
{
    use Notifiable;
    use HasRoles;

    protected string $guard_name = 'admin';

    protected $table = 'admin';

    protected $guarded = [];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'admin') {
            return $this->hasRole('admin') || $this->hasRole('super_admin') || $this->hasRole('Super Admin');
        }

        return true;
    }

    public function receivesBroadcastNotificationsOn(): string
    {
        return 'App.Models.Admin.' . $this->getKey();
    }
}
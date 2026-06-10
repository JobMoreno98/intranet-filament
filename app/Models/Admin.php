<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable  implements FilamentUser
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
            return $this->hasRole('admin') || $this->hasRole('super_admin');
        }

        return true;
    }
    public function receivesBroadcastNotificationsOn(): string
    {
        return 'App.Models.User.' . $this->getKey();
    }
    public function getFilamentDatabaseNotificationType(): string
    {
        return 'App\Models\User';
    }
    public function notifications()
    {
        return $this->hasMany(\Illuminate\Notifications\DatabaseNotification::class, 'notifiable_id')
            ->whereIn('notifiable_type', [
                'App\Models\User',
                'App\Models\Admin',
                'admin'
            ])
            ->latest();
    }
}

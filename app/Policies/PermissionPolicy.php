<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Spatie\Permission\Models\Permission;
use Illuminate\Auth\Access\HandlesAuthorization;

class PermissionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:permission');
    }

    public function view(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('view:permission');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:permission');
    }

    public function update(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('update:permission');
    }

    public function delete(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('delete:permission');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:permission');
    }

    public function restore(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('restore:permission');
    }

    public function forceDelete(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('force_delete:permission');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:permission');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:permission');
    }

    public function replicate(AuthUser $authUser, Permission $permission): bool
    {
        return $authUser->can('replicate:permission');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:permission');
    }

}
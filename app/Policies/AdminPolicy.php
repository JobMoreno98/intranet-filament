<?php

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use Illuminate\Auth\Access\HandlesAuthorization;

class AdminPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:admin');
    }

    public function view(AuthUser $authUser): bool
    {
        return $authUser->can('view:admin');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:admin');
    }

    public function update(AuthUser $authUser): bool
    {
        return $authUser->can('update:admin');
    }

    public function delete(AuthUser $authUser): bool
    {
        return $authUser->can('delete:admin');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:admin');
    }

    public function restore(AuthUser $authUser): bool
    {
        return $authUser->can('restore:admin');
    }

    public function forceDelete(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete:admin');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:admin');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:admin');
    }

    public function replicate(AuthUser $authUser): bool
    {
        return $authUser->can('replicate:admin');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:admin');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Recursos;
use Illuminate\Auth\Access\HandlesAuthorization;

class RecursosPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:recursos');
    }

    public function view(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('view:recursos');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:recursos');
    }

    public function update(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('update:recursos');
    }

    public function delete(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('delete:recursos');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:recursos');
    }

    public function restore(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('restore:recursos');
    }

    public function forceDelete(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('force_delete:recursos');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:recursos');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:recursos');
    }

    public function replicate(AuthUser $authUser, Recursos $recursos): bool
    {
        return $authUser->can('replicate:recursos');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:recursos');
    }

}
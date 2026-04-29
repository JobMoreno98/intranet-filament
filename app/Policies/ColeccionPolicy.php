<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Coleccion;
use Illuminate\Auth\Access\HandlesAuthorization;

class ColeccionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:coleccion');
    }

    public function view(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('view:coleccion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:coleccion');
    }

    public function update(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('update:coleccion');
    }

    public function delete(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('delete:coleccion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:coleccion');
    }

    public function restore(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('restore:coleccion');
    }

    public function forceDelete(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('force_delete:coleccion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:coleccion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:coleccion');
    }

    public function replicate(AuthUser $authUser, Coleccion $coleccion): bool
    {
        return $authUser->can('replicate:coleccion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:coleccion');
    }

}
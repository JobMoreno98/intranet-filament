<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\SubColeccion;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubColeccionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:sub_coleccion');
    }

    public function view(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('view:sub_coleccion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:sub_coleccion');
    }

    public function update(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('update:sub_coleccion');
    }

    public function delete(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('delete:sub_coleccion');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:sub_coleccion');
    }

    public function restore(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('restore:sub_coleccion');
    }

    public function forceDelete(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('force_delete:sub_coleccion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:sub_coleccion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:sub_coleccion');
    }

    public function replicate(AuthUser $authUser, SubColeccion $subColeccion): bool
    {
        return $authUser->can('replicate:sub_coleccion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:sub_coleccion');
    }

}
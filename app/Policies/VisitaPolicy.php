<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Visita;
use Illuminate\Auth\Access\HandlesAuthorization;

class VisitaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:visita');
    }

    public function view(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('view:visita');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:visita');
    }

    public function update(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('update:visita');
    }

    public function delete(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('delete:visita');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:visita');
    }

    public function restore(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('restore:visita');
    }

    public function forceDelete(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('force_delete:visita');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:visita');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:visita');
    }

    public function replicate(AuthUser $authUser, Visita $visita): bool
    {
        return $authUser->can('replicate:visita');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:visita');
    }

}
<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\TipoAcervo;
use Illuminate\Auth\Access\HandlesAuthorization;

class TipoAcervoPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:tipo_acervo');
    }

    public function view(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('view:tipo_acervo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:tipo_acervo');
    }

    public function update(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('update:tipo_acervo');
    }

    public function delete(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('delete:tipo_acervo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:tipo_acervo');
    }

    public function restore(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('restore:tipo_acervo');
    }

    public function forceDelete(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('force_delete:tipo_acervo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:tipo_acervo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:tipo_acervo');
    }

    public function replicate(AuthUser $authUser, TipoAcervo $tipoAcervo): bool
    {
        return $authUser->can('replicate:tipo_acervo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:tipo_acervo');
    }

}
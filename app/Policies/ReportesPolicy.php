<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Reportes;
use Illuminate\Auth\Access\HandlesAuthorization;

class ReportesPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:reportes');
    }

    public function view(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('view:reportes');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:reportes');
    }

    public function update(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('update:reportes');
    }

    public function delete(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('delete:reportes');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:reportes');
    }

    public function restore(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('restore:reportes');
    }

    public function forceDelete(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('force_delete:reportes');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:reportes');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:reportes');
    }

    public function replicate(AuthUser $authUser, Reportes $reportes): bool
    {
        return $authUser->can('replicate:reportes');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:reportes');
    }

}
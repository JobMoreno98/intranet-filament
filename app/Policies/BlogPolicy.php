<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Blog;
use Illuminate\Auth\Access\HandlesAuthorization;

class BlogPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('view_any:blog');
    }

    public function view(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('view:blog');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('create:blog');
    }

    public function update(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('update:blog');
    }

    public function delete(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('delete:blog');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('delete_any:blog');
    }

    public function restore(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('restore:blog');
    }

    public function forceDelete(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('force_delete:blog');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('force_delete_any:blog');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('restore_any:blog');
    }

    public function replicate(AuthUser $authUser, Blog $blog): bool
    {
        return $authUser->can('replicate:blog');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('reorder:blog');
    }

}
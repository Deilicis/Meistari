<?php

declare(strict_types=1);

namespace App\Services\Repositories\User;

use App\Models\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserDbRepository
{
    public function __construct(
        private readonly User $user,
        private readonly Profile $profile,
        private readonly Role $role
    ) {
    }

    public function create(array $data): User
    {
        /** @var User $newUser */
        $newUser = $this->user->create($data);
        return $newUser;
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);
        return $user->refresh();
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function createProfile(array $data): Profile
    {
        /** @var Profile $newProfile */
        $newProfile = $this->profile->create($data);
        return $newProfile;
    }

    public function assignRole(User $user, Role $role): void
    {
        $user->roles()->attach($role);
    }

    /**
     * @throws ModelNotFoundException
     */
    public function findRoleByName(string $roleName): Role
    {
        /** @var Role $foundRole */
        $foundRole = $this->role->where(Role::NAME, $roleName)->firstOrFail();
        return $foundRole;
    }
}
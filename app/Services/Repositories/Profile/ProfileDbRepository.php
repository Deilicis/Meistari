<?php

declare(strict_types=1);

namespace App\Services\Repositories\Profile;

use App\Models\Profile;

class ProfileDbRepository
{
    public function update(Profile $profile, array $data): Profile
    {
        $profile->update($data);
        return $profile->refresh();
    }
}
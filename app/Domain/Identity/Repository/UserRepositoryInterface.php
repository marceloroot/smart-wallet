<?php

namespace App\Domain\Identity\Repository;

use App\Domain\Identity\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): User;

    public function existsByEmail(string $email): bool;
}

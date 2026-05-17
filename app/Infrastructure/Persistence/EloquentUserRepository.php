<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Identity\Entity\User as UserEntity;
use App\Domain\Identity\Repository\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

final class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(UserEntity $user): UserEntity
    {
        if ($user->id() !== null) {
            return $user;
        }

        $plainPassword = $user->plainPassword();

        if ($plainPassword === null) {
            throw new \InvalidArgumentException('New users must provide a plain password.');
        }

        $model = User::query()->create([
            'name' => $user->name(),
            'email' => $user->email(),
            'password' => Hash::make($plainPassword),
        ]);

        return $user
            ->withId($model->id)
            ->withHashedPassword($model->password);
    }

    public function existsByEmail(string $email): bool
    {
        return User::query()
            ->where('email', strtolower(trim($email)))
            ->exists();
    }
}

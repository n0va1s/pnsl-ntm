<?php

namespace App\Services;

use App\Models\User;

class UserService
{
    public static function getUsuarioByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }
}

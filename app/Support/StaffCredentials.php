<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

final class StaffCredentials
{
    /**
     * @return array{email: string, password: string}
     */
    public static function make(string $name): array
    {
        return [
            'email' => self::uniqueEmail($name),
            'password' => self::password(),
        ];
    }

    public static function uniqueEmail(string $name): string
    {
        $domain = Str::after((string) config('lab.email'), '@') ?: 'ssml.af';
        $base = Str::slug(Str::ascii($name));

        if ($base === '') {
            $base = 'staff';
        }

        $email = $base.'@'.$domain;
        $suffix = 1;

        while (User::query()->where('email', $email)->exists()) {
            $email = $base.$suffix.'@'.$domain;
            $suffix++;
        }

        return $email;
    }

    public static function password(): string
    {
        return Str::lower(Str::random(4)).(string) random_int(1000, 9999).Str::lower(Str::random(2));
    }
}

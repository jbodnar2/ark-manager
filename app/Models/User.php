<?php
declare(strict_types=1);

namespace App\Models;

class User
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_USER = 'user';
    public const ROLE_VIEWER = 'viewer';
    public const ROLE_INACTIVE = 'inactive';

    public int $id;
    public string $username;
    public string $first_name;
    public string $last_name;
    public string $email;
    public string $role;
    public ?string $api_token = null;

    public static function getAllowedRoles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_USER,
            self::ROLE_VIEWER,
            self::ROLE_INACTIVE,
        ];
    }

    public function getFullName(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function isViewer(): bool
    {
        return $this->role === self::ROLE_VIEWER;
    }

    public function isActive(): bool
    {
        return $this->role !== self::ROLE_INACTIVE;
    }

    public function isInactive(): bool
    {
        return $this->role === self::ROLE_INACTIVE;
    }
}

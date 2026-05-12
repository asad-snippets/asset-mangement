<?php

namespace App\Helpers;
class Role
{
    public const ADMIN = 'admin';
    public const MANAGER = 'manager';
    public const EMPLOYEE = 'employee';
    public const PERMISSION_CREATE_USER = 'create-user';
    public const PERMISSION_EDIT_USER = 'edit-user';
    public const PERMISSION_DELETE_USER = 'delete-user';
    public const PERMISSION_VIEW_REPORTS = 'view-reports';
    public const PERMISSION_MANAGE_ASSETS = 'manage-assets';

    public const ALL_PERMISSIONS = [
        self::PERMISSION_CREATE_USER,
        self::PERMISSION_EDIT_USER,
        self::PERMISSION_DELETE_USER,
        self::PERMISSION_VIEW_REPORTS,
        self::PERMISSION_MANAGE_ASSETS,
    ];

    private const ROLE_PERMISSIONS = [
        self::ADMIN => self::ALL_PERMISSIONS,
        self::MANAGER => [
            self::PERMISSION_VIEW_REPORTS,
            self::PERMISSION_EDIT_USER,
            self::PERMISSION_MANAGE_ASSETS,
        ],
        self::EMPLOYEE => [
            self::PERMISSION_VIEW_REPORTS,
        ],
    ];

    public static function permissionsFor(string $role): array
    {
        return self::ROLE_PERMISSIONS[$role] ?? [];
    }
}

<?php

declare(strict_types=1);

namespace App\Classes;

/**
 * Interface PermissionMatrixInterface
 * @package App\Classes
 */
interface PermissionMatrixInterface
{
    /**
     * @param int $schoolId
     * @param string $capability
     * @param array $roles
     * @return bool
     */
    public function hasPermission(int $schoolId, string $capability, array $roles): bool;

    /**
     * @param int $schoolId
     * @param string $capability
     * @param array $roles
     * @return mixed
     */
    public function setPermission(int $schoolId, string $capability, array $roles);

    /**
     * Returns a list of roles that have the given capability in a given school.
     * @param int $schoolId
     * @param string $capability
     * @return array
     */
    public function getPermittedRoles(int $schoolId, string $capability): array;
}

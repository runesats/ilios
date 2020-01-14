<?php

declare(strict_types=1);

namespace App\Traits;

use DateTime;

/**
 * Interface TimestampableEntityInterface
 */
interface TimestampableEntityInterface
{
    /**
     * @return DateTime
     */
    public function getCreatedAt();

    /**
     * @return DateTime
     */
    public function getUpdatedAt();

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt);

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt);

    /**
     * @return string
     */
    public function getClassName();
}

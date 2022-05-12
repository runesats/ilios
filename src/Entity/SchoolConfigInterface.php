<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\IdentifiableEntityInterface;
use App\Traits\NameableEntityInterface;
use App\Traits\SchoolEntityInterface;
use App\Traits\StringableEntityToIdInterface;

/**
 * Interface SchoolConfigInterface
 */
interface SchoolConfigInterface extends
    SchoolEntityInterface,
    NameableEntityInterface,
    IdentifiableEntityInterface,
    StringableEntityToIdInterface
{
    public function getValue(): string;

    /**
     * @param string $value
     */
    public function setValue($value);
}

<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\CategorizableEntityInterface;
use App\Traits\DescribableEntityInterface;
use App\Traits\IdentifiableEntityInterface;
use App\Traits\StringableEntityToIdInterface;
use App\Traits\TitledEntityInterface;

/**
 * Interface AamcResourceTypeInterface
 */
interface AamcResourceTypeInterface extends
    IdentifiableEntityInterface,
    StringableEntityToIdInterface,
    TitledEntityInterface,
    CategorizableEntityInterface
{
    /**
     * @param string $description
     */
    public function setDescription($description);

    public function getDescription(): string;
}

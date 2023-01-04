<?php

declare(strict_types=1);

namespace App\Traits;

trait IdentifiableEntity
{
    protected int $id;

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

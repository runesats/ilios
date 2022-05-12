<?php

declare(strict_types=1);

namespace App\Entity;

use App\Traits\IdentifiableEntityInterface;
use App\Traits\StringableEntityToIdInterface;

/**
 * Class MeshTreeInterface
 */
interface MeshTreeInterface extends
    StringableEntityToIdInterface,
    IdentifiableEntityInterface
{
    /**
     * Set treeNumber
     *
     * @param string $treeNumber
     */
    public function setTreeNumber($treeNumber);

    /**
     * Get treeNumber
     */
    public function getTreeNumber(): string;

    /**
     * Set meshDescriptor
     *
     */
    public function setDescriptor(MeshDescriptorInterface $descriptor): MeshTree;

    /**
     * Get meshDescriptor
     */
    public function getDescriptor(): MeshDescriptorInterface;
}

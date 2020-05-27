<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Service\EntityMetadata;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ReflectionClass;
use ReflectionProperty;

/**
 * Ilios Entity normalizer
 */
class EntityNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;
    /**
     * @var \HTMLPurifier
     */
    protected $purifier;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        EntityMetadata $entityMetadata,
        ManagerRegistry $managerRegistry,
        \HTMLPurifier $purifier,
        LoggerInterface $logger
    ) {
        $this->entityMetadata = $entityMetadata;
        $this->managerRegistry = $managerRegistry;
        $this->purifier = $purifier;
        $this->logger = $logger;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $reflection = new ReflectionClass($object);
        $exposedProperties = $this->entityMetadata->extractExposedProperties($reflection);
        $propertyAccessor = PropertyAccess::createPropertyAccessor();

        $rhett = [];
        /** @var ReflectionProperty $property */
        foreach ($exposedProperties as $property) {
            $name = $property->getName();
            $value = $propertyAccessor->getValue($object, $name);
            $rhett[$name] = $this->convertValueByType($property, $value);
        }

        return $rhett;
    }

    /**
     * Converts value into the type dictated by it's annotation on the entity
     *
     * @param mixed $value
     * @return mixed
     */
    protected function convertValueByType(ReflectionProperty $property, $value)
    {
        $type = $this->entityMetadata->getTypeOfProperty($property);
        if ($type === 'dateTime') {
            /** @var \DateTime $value */
            if ($value) {
                return $value->format('c');
            }
        }

        if ($type === 'boolean') {
            if ($value) {
                return boolval($value);
            }
        }
        if ($type === 'entity') {
            return $value ? (string) $value : null;
        }

        if ($type === 'entityCollection') {
            /** @var ArrayCollection $value $ids */
            $ids = $value->map(function ($entity) {
                return $entity ? (string) $entity : null;
            })->toArray();

            return array_values($ids);
        }

        return $value;
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $format === 'json' && $this->entityMetadata->isAnIliosEntity($data);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

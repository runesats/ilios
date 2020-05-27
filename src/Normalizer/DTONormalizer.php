<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Service\EntityMetadata;
use ReflectionClass;
use ReflectionProperty;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Ilios DTO normalizer
 */
class DTONormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{

    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;

    public function __construct(EntityMetadata $entityMetadata)
    {
        $this->entityMetadata = $entityMetadata;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $reflection = new ReflectionClass($object);
        $exposedProperties = $this->entityMetadata->extractExposedProperties($reflection);

        $rhett = [];
        /** @var ReflectionProperty $property */
        foreach ($exposedProperties as $property) {
            $name = $property->getName();
            $value = $this->convertValueByType($property, $object->$name);
            if (!is_null($value)) {
                $rhett[$name] = $value;
            }
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
        if ($type === 'string') {
            return is_null($value) ? null : (string) $value;
        }

        if ($type === 'dateTime') {
            return is_null($value) ? null : $value->format('c');
        }

        if ($type === 'boolean') {
            return is_null($value) ? null : (bool) $value;
        }

        if ($type === 'array<string>') {
            $stringValues = array_map('strval', $value);
            return array_values($stringValues);
        }

        return $value;
    }

    /**
     * Check to see if we can normalize the object or class
     * {@inheritdoc}
     */
    public function supportsNormalization($classNameOrObject, string $format = null)
    {
        return $format === 'json' && $this->entityMetadata->isAnIliosDto($classNameOrObject);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}

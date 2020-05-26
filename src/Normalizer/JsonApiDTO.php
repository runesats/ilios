<?php

declare(strict_types=1);

namespace App\Normalizer;

use App\Service\EntityMetadata;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use ReflectionClass;
use ReflectionProperty;
use DateTime;

class JsonApiDTO implements NormalizerInterface
{

    /**
     * @var EntityMetadata
     */
    protected $entityMetadata;

    public function __construct(
        EntityMetadata $entityMetadata
    ) {
        $this->entityMetadata = $entityMetadata;
    }

    public function normalize($object, string $format = null, array $context = [])
    {
        $reflection = new ReflectionClass($object);
        $exposedProperties = $this->entityMetadata->extractExposedProperties($reflection);
        $attributes = [];
        foreach ($exposedProperties as $property) {
            $attributes[$property->name] = $this->getPropertyValue($property, $object);
        }

        $relatedProperties = $this->entityMetadata->extractRelated($reflection);

        $type = $this->entityMetadata->extractType($reflection);

        $idProperty = $this->entityMetadata->extractId($reflection);
        $id = $attributes[$idProperty];

        $related = [];
        foreach ($relatedProperties as $attributeName => $relationshipType) {
            $value = $attributes[$attributeName];
            if ($value) {
                $related[$attributeName] = [
                    'type' => $relationshipType,
                    'value' => $value,
                ];
            }

            unset($attributes[$attributeName]);
        }
        unset($attributes[$idProperty]);

        return [
            'id' => $id,
            'type' => $type,
            'attributes' => $attributes,
            'related' => $related,
        ];
    }

    protected function getPropertyValue(ReflectionProperty $property, object $object)
    {
        $type = $this->entityMetadata->getTypeOfProperty($property);
        if ($type === 'string') {
            $value = $object->{$property->name};
            return null === $value ? null : (string) $value;
        }

        if ($type === 'dateTime') {
            /** @var DateTime $value */
            $value = $object->{$property->name};
            return null === $value ? null : $value->format('c');
        }

        if ($type === 'array<string>') {
            $values = $object->{$property->name};
            $stringValues = array_map('strval', $values);

            return array_values($stringValues);
        }

        return $object->{$property->name};
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $format === 'json-api' && $this->entityMetadata->isAnIliosDto($data);
    }
}

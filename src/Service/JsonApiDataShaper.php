<?php

declare(strict_types=1);

namespace App\Service;

use App\Classes\JsonApiData;
use App\Normalizer\JsonApiDTONormalizer;

class JsonApiDataShaper
{
    /**
     * @var EntityManagerLookup
     */
    protected $entityManagerLookup;
    /**
     * @var JsonApiDTONormalizer
     */
    protected $normalizer;

    public function __construct(EntityManagerLookup $entityManagerLookup, JsonApiDTONormalizer $normalizer)
    {
        $this->entityManagerLookup = $entityManagerLookup;
        $this->normalizer = $normalizer;
    }

    public function shapeData(array $data, array $sideLoadFields): array
    {
        $jsonApiData = new JsonApiData($this->entityManagerLookup, $this->normalizer, $data, $sideLoadFields);
        return $jsonApiData->toArray();
    }

    /**
     * Flattens well structured JSON:API data into the flat array
     * our API has always used.
     */
    public function flattenJsonApiData(object $data): array
    {
        $rhett = [];
        if (property_exists($data, 'id')) {
            $manager = $this->entityManagerLookup->getManagerForEndpoint($data->type);
            $rhett[$manager->getIdField()] = $data->id;
        }
        foreach ($data->attributes as $key => $value) {
            $rhett[$key] = $value;
        }

        if (property_exists($data, 'relationships')) {
            foreach ($data->relationships as $key => $rel) {
                if (is_array($rel->data)) {
                    $rhett[$key] = [];
                    foreach ($rel->data as $r2) {
                        $rhett[$key][] = $r2->id;
                    }
                } else {
                    $rhett[$key] = is_null($rel->data) ? null : $rel->data->id;
                }
            }
        }

        return $rhett;
    }
}

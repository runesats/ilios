<?php

declare(strict_types=1);

namespace App\Classes;

use App\Service\Config;
use Elasticsearch\Client;
use Exception;

class ElasticSearchBase
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var bool
     */
    protected $enabled = false;

    public const CURRICULUM_INDEX = 'ilios-curriculum';
    public const USER_INDEX = 'ilios-users';
    public const MESH_INDEX = 'ilios-mesh';
    public const LEARNING_MATERIAL_INDEX = 'ilios-learning-materials';
    public const SESSION_ID_PREFIX = 'session_';

    /**
     * @var int|string|null
     */
    private $uploadLimit;

    /**
     * Search constructor.
     * @param Config $config
     * @param Client $client
     */
    public function __construct(
        Config $config,
        Client $client = null
    ) {
        if ($client) {
            $this->enabled = true;
            $this->client = $client;
        }
        $limit = $config->get('elasticsearch_upload_limit');
        //10mb AWS hard limit on non-huge ES clusters and we need some overhead for control statements
        $this->uploadLimit = $limit ?? 9000000;
    }

    public function isEnabled()
    {
        return (bool) $this->enabled;
    }

    protected function doSearch(array $params): array
    {
        if (!$this->enabled) {
            throw new Exception("Search is not configured, isEnabled() should be called before calling this method");
        }
        return $this->client->search($params);
    }

    protected function doExplain(string $id, array $params): array
    {
        if (!$this->enabled) {
            throw new Exception("Search is not configured, isEnabled() should be called before calling this method");
        }
        $params['id'] = $id;
        return $this->client->explain($params);
    }

    protected function doIndex(array $params): array
    {
        if (!$this->enabled) {
            return ['errors' => false];
        }
        return $this->client->index($params);
    }

    protected function doDelete(array $params): array
    {
        if (!$this->enabled) {
            return ['result' => 'deleted'];
        }
        return $this->client->delete($params);
    }

    protected function doDeleteByQuery(array $params): array
    {
        if (!$this->enabled) {
            return ['failures' => []];
        }
        return $this->client->deleteByQuery($params);
    }

    protected function doBulk(array $params): array
    {
        if (!$this->enabled) {
            return ['errors' => false];
        }
        return $this->client->bulk($params);
    }

    /**
     * The API for bulk indexing is a little bit weird and front data has to be inserted in
     * front of every item. This allows bulk indexing on many types at the same time, and
     * this convenience method takes care of that for us.
     * @param $index
     * @param array $items
     * @return array
     */
    protected function doBulkIndex(string $index, array $items): array
    {
        if (!$this->enabled || empty($items)) {
            return ['errors' => false];
        }

        $totalItems = count($items);
        $i = 0;
        $chunks = [];
        $chunk = [];
        $chunkSize = 0;
        // Keep adding items until we run out of space and then start over
        while ($i < $totalItems) {
            $item = $items[$i];
            $itemSize = strlen(json_encode($item));
            if (($chunkSize + $itemSize) < $this->uploadLimit) {
                //add the item and move on to the next one
                $chunk[] = $item;
                $i++;
                $chunkSize += $itemSize;
            } elseif (count($chunk)) {
                //we've reached a point where adding another item is too much
                //instead we'll just save what we have and start again
                $chunks[] = $chunk;
                $chunk = [];
                $chunkSize = 0;
            } else {
                //this single item is too big so we have to skip it
                throw new Exception(
                    sprintf(
                        'Unable to index %s ID #%s as it is larger than the %s byte upload limit',
                        $index,
                        $item['id'],
                        $this->uploadLimit
                    )
                );
            }
        }
        //take care of the last iteration
        if (!empty($chunk)) {
            $chunks[] = $chunk;
        }

        $results = [
            'took' => 0,
            'errors' => false,
            'items' => []
        ];

        foreach ($chunks as $chunk) {
            $body = [];
            foreach ($chunk as $item) {
                $body[] = ['index' => [
                    '_index' => $index,
                    '_type' => '_doc',
                    '_id' => $item['id']
                ]];
                $body[] = $item;
            }
            $rhett = $this->doBulk(['body' => $body]);
            $results['took'] += $rhett['took'];
            if ($rhett['errors']) {
                $results['errors'] = true;
            }
            $results['items'] = array_merge($results['items'], $rhett['items']);
        }

        return $results;
    }
}

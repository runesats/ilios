<?php

declare(strict_types=1);

namespace App\Service;

use Exception;
use League\Csv\Writer;

/**
 * Class CsvWriter
 * @package App\Service
 */
class CsvWriter
{
    /**
     * @param array $header
     * @param array $data
     * @param string $path
     * @param string $openMode
     * @throws Exception
     */
    public function writeToFile(array $header, array $data, string $path, string $openMode = 'w+'): void
    {
        $writer = Writer::createFromPath($path, $openMode);
        $writer->insertOne($header);
        $writer->insertAll($data);
    }
}

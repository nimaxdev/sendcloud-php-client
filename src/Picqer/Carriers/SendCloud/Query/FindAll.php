<?php

namespace Picqer\Carriers\SendCloud\Query;

use Picqer\Carriers\SendCloud\Connection;
use Picqer\Carriers\SendCloud\SendCloudApiException;

/**
 * Trait FindAll
 *
 * @method Connection connection()
 *
 * @package Picqer\Carriers\SendCloud\Persistance
 */
trait FindAll
{

    /**
     * @param array $params
     * @return static[]
     * @throws SendCloudApiException
     */
    public function all($params = []): array
    {
        $result = $this->connection()->get($this->url, $params);

        return $this->collectionFromResult($result);
    }

    public function collectionFromResult($result): array
    {
        $collection = [];

        foreach ($result[$this->namespaces['plural']] as $r) {
            $collection[] = new self($this->connection(), $r);
        }

        return $collection;
    }

}

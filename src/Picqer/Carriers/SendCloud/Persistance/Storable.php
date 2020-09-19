<?php

namespace Picqer\Carriers\SendCloud\Persistance;

use Picqer\Carriers\SendCloud\Connection;

/**
 * Trait Storable
 *
 * @method Connection connection()
 *
 * @package Picqer\Carriers\SendCloud\Persistance
 */
trait Storable
{
    /**
     * @return $this
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function save(): self
    {
        if ($this->exists()) {
            $this->fill($this->update());
        } else {
            $this->fill($this->insert());
        }

        return $this;
    }

    /**
     * @return array
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function insert(): array
    {
        return $this->connection()->post($this->url, $this->json());
    }

    /**
     * @return array
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function update(): array
    {
        return $this->connection()->put($this->url . '/' . urlencode($this->id), $this->json());
    }

    /**
     * @return array
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function delete(): array
    {
        return $this->connection()->delete($this->url . '/' . urlencode($this->id));
    }
}

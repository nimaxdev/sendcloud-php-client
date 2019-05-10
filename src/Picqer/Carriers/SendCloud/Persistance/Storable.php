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
    public function save()
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
    public function insert()
    {
        return $this->connection()->post($this->url, $this->json());
    }

    /**
     * @return array
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function update()
    {
        return $this->connection()->put($this->url . '/' . urlencode($this->id), $this->json());
    }

    /**
     * @return array
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function delete()
    {
        return $this->connection()->delete($this->url . '/' . urlencode($this->id));
    }
}

<?php

namespace Picqer\Carriers\SendCloud;

/**
 * Class Label
 *
 * @property string[] $normal_printer
 * @property string $label_printer
 *
 * @package Picqer\Carriers\SendCloud
 */
class Label extends Model
{
    use Query\FindOne;

    protected $fillable = [
        'normal_printer',
        'label_printer',
    ];

    protected $url = 'labels';

    protected $namespaces = [
        'singular' => 'label',
        'plural' => 'labels'
    ];

    /**
     * @var Parcel|null parent
     */
    protected $parcel;

    /**
     * @inheritDoc
     * @param Parcel|null $parent
     */
    public function __construct(Connection $connection, array $attributes = [], ?Parcel $parent = null)
    {
        parent::__construct($connection, $attributes);
        $this->parcel = $parent;
    }

    public function bulk(array $ids = []): self
    {
        $result = $this->connection()->post($this->url, $ids);
        return new static($this->connection(), $result[$this->namespaces['singular']]);
    }

    /**
     * Returns the label content (PDF) in A6 format.
     *
     * @return string
     * @throws SendCloudApiException
     * @throws \RuntimeException if unable to read or an error occurs while reading.
     */
    public function labelPrinterContent()
    {
        if ($this->parcel) {
            $url = $this->parcel->getPrimaryLabelUrl();
        } else {
            $url = $this->label_printer;
        }

        return $this->connection->download($url);
    }

    /**
     * Returns the label content (PDF) in A4 format
     *
     * @param int $position (0: Left Top, 1: Right Top, 2: Left bottom, 3: Right bottom)
     * @return string
     * @throws SendCloudApiException
     */

    public function normalPrinterContent($position = 0)
    {
        return $this->connection->download($this->normal_printer[$position]);
    }
}

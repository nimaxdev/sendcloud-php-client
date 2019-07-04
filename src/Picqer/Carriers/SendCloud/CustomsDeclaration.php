<?php

namespace Picqer\Carriers\SendCloud;

/**
 * Class CustomsDeclaration
 *
 * @property string $normal_printer
 *
 * @package Picqer\Carriers\SendCloud
 */
class CustomsDeclaration extends Model
{

    use Query\FindOne;

    protected $fillable = [
        'normal_printer',
    ];

    protected $url = 'customs_declaration';

    protected $namespaces = [
        'singular' => 'customs_declaration',
        'plural' => 'customs_declarations'
    ];

    /**
     * Returns the label content (PDF) in A6 format.
     *
     * @return string
     * @throws SendCloudApiException
     * @throws \RuntimeException if unable to read or an error occurs while reading.
     * @throws \Picqer\Carriers\SendCloud\SendCloudApiException
     */
    public function fetchContent()
    {
        $this->connection->setHeadersPdf();
        $url = str_replace($this->connection->apiUrl(), '', $this->normal_printer);

        return $this->connection->download($url);
    }
}

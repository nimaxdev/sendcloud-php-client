<?php

namespace Picqer\Carriers\SendCloud;

/**
 * Class Parcel
 *
 * @property int id
 * @property string name
 * @property string company_name
 * @property string address
 * @property array address_divided
 * @property string city
 * @property string postal_code
 * @property string telephone
 * @property string email
 * @property string date_created
 * @property array status
 * @property array data
 * @property array country
 * @property string country_state
 * @property array shipment
 * @property array label
 * @property array documents
 * @property bool requestShipment
 * @property bool request_label
 * @property bool request_label_async
 * @property string order_number
 * @property string tracking_number
 * @property string weight
 * @property string width
 * @property string height
 * @property string depth
 * @property string sender_address
 * @property string colli_tracking_number
 * @property string colli_uuid
 * @property string collo_nr
 * @property string collo_count
 * @property int quantity
 *
 * @package Picqer\Carriers\SendCloud
 */
class Parcel extends Model
{
    use Query\Findable;
    use Persistance\Storable;
    use Persistance\Multiple;

    protected $fillable = [
        'id',
        'address',
        'house_number',
        'address_2',
        'address_divided',
        'city',
        'company_name',
        'country',
        'country_state',
        'data',
        'date_created',
        'email',
        'name',
        'postal_code',
        'reference',
        'shipment',
        'status',
        'to_service_point',
        'telephone',
        'tracking_number',
        'weight',
        'width',
        'height',
        'depth',
        'label',
        'customs_declaration',
        'order_number',
        'insured_value',
        'total_insured_value',
        'to_state',
        'customs_invoice_nr',
        'customs_shipment_type',
        'parcel_items',
        'documents',
        'type',
        'sender_address',
        'shipment_uuid',
        'shipping_method',
        'external_order_id',
        'external_shipment_id',
        'external_reference',
        'is_return',
        'note',
        'to_post_number',
        'total_order_cost',
        'currency',
        'carrier',
        'tracking_url',
        'requestShipment', // Special one to create new shipments
        'request_label',
        'request_label_async',
        'colli_tracking_number',
        'colli_uuid',
        'collo_nr',
        'collo_count',
        'quantity',
        'contract',
    ];

    protected $url = 'parcels';

    protected $namespaces = [
        'singular' => 'parcel',
        'plural'   => 'parcels'
    ];

    public function getTrackingUrl(): ?string
    {
        return $this->tracking_url;
    }

    public function getShipperName(): ?string
    {
        return $this->carrier['code'];
    }

    public function getPrimaryLabelUrl(): string
    {
        // If multiple documents are supplied, type 'label' is the primary label
        if (is_array($this->documents)) {
            foreach ($this->documents as $document) {
                if ($document['type'] === 'label') {
                    return $document['link'];
                }
            }
        }

        // If new type of documents is not declared, use old url
        return $this->label['label_printer'];
    }

    public function getStatuses(): array
    {
        return $this->connection()->get($this->url . '/statuses');
    }

    /**
     * @param int|null $parcelID
     *
     * @return array
     * @throws SendCloudApiException
     */
    public function cancel($parcelID = null): array
    {
        if ($parcelID) {
            $this->id = $parcelID;
        }
        return $this->connection()->post($this->url . '/' . urlencode($this->id) . '/cancel', '');
    }
}

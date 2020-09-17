<?php

namespace Picqer\Carriers\SendCloud;

class Documents
{

    /**
     * @var Parcel
     */
    protected $parcel;

    /**
     * @param Parcel $parcel
     */
    public function __construct(Parcel $parcel)
    {
        $this->parcel = $parcel;
    }

    public function getDocument(string $type): ?Document
    {
        if (is_array($this->parcel->documents)) {
            foreach ($this->parcel->documents as $document) {
                if ($document['type'] === $type) {
                    return new Document($this->parcel->connection(), $document);
                }
            }
        }
        switch ($type) {
            case Document::TYPE_LABEL:
                if ($label = $this->parcel->label) {
                    $link = is_array($label) ? $label['label_printer'] : $label->label_printer;
                    return new Document($this->parcel->connection(), [
                        'type' => 'label',
                        'size' => 'a6',
                        'link' => $link,
                    ]);
                }
                break;
            case Document::TYPE_COMMERCIAL_INVOICE:
            case Document::TYPE_CN23:
            case Document::TYPE_CN23_DEFAULT:
                if ($customs_declaration = $this->parcel->customs_declaration) {
                    $link = is_array($customs_declaration) ? $customs_declaration['normal_printer'] : $customs_declaration->normal_printer;
                    return new Document($this->parcel->connection(), [
                        'type' => $type,
                        'size' => 'a4',
                        'link' => $link,
                    ]);
                }
                break;
        }
    }
}

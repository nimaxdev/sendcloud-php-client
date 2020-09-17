<?php

namespace Picqer\Carriers\SendCloud;

class Document
{
    public const TYPE_LABEL = 'label';
    public const TYPE_CP71 = 'cp71';
    public const TYPE_CN23 = 'cn23';
    public const TYPE_COMMERCIAL_INVOICE = 'commercial-invoice';
    public const TYPE_CN23_DEFAULT = 'cn23-default';

    public const SIZE_A4 = 'a4';
    public const SIZE_A6 = 'a6';

    public const FILE_FORMAT_PDF = 'pdf';
    public const FILE_FORMAT_ZPL = 'zpl';
    public const FILE_FORMAT_PNG = 'png';

    public const DPI_PDF_DEFAULT = 72;
    public const DPI_PDF_72 = 72;
    public const DPI_ZPL_DEFAULT = 203;
    public const DPI_ZPL_203 = 203;
    public const DPI_ZPL_300 = 300;
    public const DPI_ZPL_600 = 600;
    public const DPI_PNG_DEFAULT = 300;
    public const DPI_PNG_150 = 150;
    public const DPI_PNG_300 = 300;
    public $formatDpi = [
        self::FILE_FORMAT_PDF => [
            self::DPI_PDF_72 => '&dpi=' . self::DPI_PDF_72,
        ],
        self::FILE_FORMAT_PNG => [
            self::DPI_PNG_150 => '&dpi=' . self::DPI_PNG_150,
            self::DPI_PNG_300 => '&dpi=' . self::DPI_PNG_300,
        ],
        self::FILE_FORMAT_ZPL => [
            self::DPI_ZPL_203 => '&dpi=' . self::DPI_ZPL_203,
            self::DPI_ZPL_300 => '&dpi=' . self::DPI_ZPL_300,
            self::DPI_ZPL_600 => '&dpi=' . self::DPI_ZPL_600,
        ],
    ];


    /**
     * @var string The type of the document. See the list below for available types.
     *             Available types. The presence of customs documents in the response depends on the carrier and destination.
     * label               To be placed on the actual parcel.
     * cp71                CP71 Dispatch Note for international shipments.
     * cn23                CN23 customs document for international shipments.
     * commercial‑invoice  Sendcloud or carrier generated commercial invoice for internation shipments.
     * cn23‑default        The Sendcloud generated CN23 document. If a CN23 document is returned, this document can is here for reference purposes.
     */
    public $type;

    /**
     * @var string The paper size of the document, you can expect: a4 and a6.
     */
    public $size;

    /**
     * @var string A link to the document, which allows downloading of the document in PDF, PNG and ZPL and various DPI. Read more HERE.
     */
    public $link;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param Connection $connection
     * @param array      $attributes
     */
    public function __construct(Connection $connection, array $attributes = [])
    {
        $this->connection = $connection;
        $this->type = $attributes['type'] ?? null;
        $this->size = $attributes['size'] ?? null;
        $this->link = $attributes['link'] ?? null;
    }

    /**
     *
     * @param string      $fileFormat By default the documents are returned in PDF format. The returned format can be changed by either providing a Accept header or a format request argument.
     *                                File format     Accept header     Format argument
     *                                pdf          application/pdf pdf
     *                                zpl          application/zpl zpl
     *                                png          image/png       png
     * @param string|null $dpi        The resolution of the returned document can be changed as well, this can be done by passing a dpi request argument.
     * @return string
     * @throws SendCloudApiException
     */
    public function download(string $fileFormat = self::FILE_FORMAT_PDF, ?string $dpi = null): string
    {
        $headers = $this->getHeadersForFileFormat($fileFormat);
        $url = $this->link;
        if (isset($this->formatDpi[$fileFormat])) {
            $url .= "?format={$fileFormat}" . ($this->formatDpi[$fileFormat][$dpi] ?? '');
        }
        return $this->connection->download($url, $headers ?? []);
    }

    private function getHeadersForFileFormat(string $fileFormat): array
    {
        if ($fileFormat === self::FILE_FORMAT_ZPL) {
            return ['Accept' => 'application/zpl'];
        }

        if ($fileFormat === self::FILE_FORMAT_PNG) {
            return ['Accept' => 'image/png'];
        }

        return ['Accept' => 'application/pdf'];
    }
}

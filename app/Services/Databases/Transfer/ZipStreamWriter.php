<?php

namespace App\Services\Databases\Transfer;

class ZipStreamWriter
{
    public const MAX_SIZE = 0xFFFFFFFF;

    private const VERSION_DEFAULT = 20;

    private const VERSION_ZIP64 = 45;

    private const FLAGS = 0x0808;

    private const METHOD_DEFLATE = 8;

    private \DeflateContext $deflate;

    private \HashContext $checksum;

    private int $size = 0;

    private int $compressedSize = 0;

    private int $headerSize;

    private string $time;

    /**
     * @param  \Closure(string): void  $output
     */
    public function __construct(private string $name, private bool $zip64, private \Closure $output)
    {
        $this->deflate = deflate_init(ZLIB_ENCODING_RAW);
        $this->checksum = hash_init('crc32b');

        $now = getdate();
        $this->time = pack(
            'vv',
            ($now['hours'] << 11) | ($now['minutes'] << 5) | ($now['seconds'] >> 1),
            (max(0, $now['year'] - 1980) << 9) | ($now['mon'] << 5) | $now['mday'],
        );

        $extra = $zip64 ? pack('vvPP', 0x0001, 16, 0, 0) : '';
        $header = pack('Vvvv', 0x04034B50, $this->version(), self::FLAGS, self::METHOD_DEFLATE)
            .$this->time
            .pack('VVVvv', 0, $zip64 ? self::MAX_SIZE : 0, $zip64 ? self::MAX_SIZE : 0, strlen($name), strlen($extra))
            .$name
            .$extra;

        $this->headerSize = strlen($header);
        ($this->output)($header);
    }

    /**
     * @throws \OverflowException
     */
    public function write(string $data): void
    {
        $this->size += strlen($data);
        hash_update($this->checksum, $data);

        $this->writeCompressed(deflate_add($this->deflate, $data, ZLIB_NO_FLUSH));
    }

    /**
     * @throws \OverflowException
     */
    public function finish(): void
    {
        $this->writeCompressed(deflate_add($this->deflate, '', ZLIB_FINISH));

        $checksum = strrev(hash_final($this->checksum, true));
        $directoryOffset = $this->headerSize + $this->compressedSize + ($this->zip64 ? 24 : 16);

        $descriptor = pack('V', 0x08074B50).$checksum.($this->zip64
            ? pack('PP', $this->compressedSize, $this->size)
            : pack('VV', $this->compressedSize, $this->size));

        $extra = $this->zip64 ? pack('vvPPP', 0x0001, 24, $this->size, $this->compressedSize, 0) : '';
        $directory = pack('Vvvvv', 0x02014B50, (3 << 8) | $this->version(), $this->version(), self::FLAGS, self::METHOD_DEFLATE)
            .$this->time
            .$checksum
            .pack(
                'VVvvvvvVV',
                $this->zip64 ? self::MAX_SIZE : $this->compressedSize,
                $this->zip64 ? self::MAX_SIZE : $this->size,
                strlen($this->name),
                strlen($extra),
                0,
                0,
                0,
                0100644 << 16,
                $this->zip64 ? self::MAX_SIZE : 0,
            )
            .$this->name
            .$extra;

        $end = '';
        if ($this->zip64) {
            $end .= pack('VPvvVVPPPP', 0x06064B50, 44, (3 << 8) | self::VERSION_ZIP64, self::VERSION_ZIP64, 0, 0, 1, 1, strlen($directory), $directoryOffset);
            $end .= pack('VVPV', 0x07064B50, 0, $directoryOffset + strlen($directory), 1);
        }

        $end .= pack('VvvvvVVv', 0x06054B50, 0, 0, 1, 1, strlen($directory), min($directoryOffset, self::MAX_SIZE), 0);

        ($this->output)($descriptor.$directory.$end);
    }

    /**
     * @throws \OverflowException
     */
    private function writeCompressed(string $data): void
    {
        $this->compressedSize += strlen($data);

        if (! $this->zip64 && max($this->size, $this->headerSize + $this->compressedSize + 16) > self::MAX_SIZE) {
            throw new \OverflowException('The file is too large for a zip archive that was not started as zip64.');
        }

        if ($data !== '') {
            ($this->output)($data);
        }
    }

    private function version(): int
    {
        return $this->zip64 ? self::VERSION_ZIP64 : self::VERSION_DEFAULT;
    }
}

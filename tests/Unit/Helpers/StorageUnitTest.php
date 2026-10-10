<?php

namespace Tests\Unit\Helpers;

use App\Helpers\StorageUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StorageUnitTest extends TestCase
{
    public function test_binary_prefixes_are_used_by_default()
    {
        $this->assertTrue(StorageUnit::isBinary());
        $this->assertSame('MiB', StorageUnit::megabyte());
        $this->assertSame('512 MiB', StorageUnit::formatMebibytes(512));
        $this->assertSame('1.5 GiB', StorageUnit::formatMebibytes(1536));
        $this->assertSame('1 GiB', StorageUnit::formatBytes(1024 ** 3));
        $this->assertSame('0 B', StorageUnit::formatBytes(0));
        $this->assertSame(1024, StorageUnit::fromMebibytes(1024));
        $this->assertSame(1024, StorageUnit::toMebibytes(1024));
    }

    #[DataProvider('disabledValueProvider')]
    public function test_decimal_prefixes_are_used_when_binary_is_disabled($value)
    {
        config()->set('panel.use_binary_prefix', $value);

        $this->assertFalse(StorageUnit::isBinary());
        $this->assertSame('MB', StorageUnit::megabyte());
        $this->assertSame('536.87 MB', StorageUnit::formatMebibytes(512));
        $this->assertSame('1.07 GB', StorageUnit::formatMebibytes(1024));
        $this->assertSame('1 GB', StorageUnit::formatBytes(1000 ** 3));
    }

    public function test_stored_mebibytes_are_converted_at_the_input_boundary_with_decimal_prefixes()
    {
        config()->set('panel.use_binary_prefix', false);

        $this->assertSame(1074, StorageUnit::fromMebibytes(1024));
        $this->assertSame(954, StorageUnit::toMebibytes(1000));
        $this->assertSame(1, StorageUnit::toMebibytes(1));

        foreach ([0, -1, null, ''] as $value) {
            $this->assertSame($value, StorageUnit::fromMebibytes($value));
            $this->assertSame($value, StorageUnit::toMebibytes($value));
        }
    }

    public function test_an_unedited_limit_keeps_its_stored_value_with_decimal_prefixes()
    {
        config()->set('panel.use_binary_prefix', false);

        foreach ([1, 100, 128, 512, 1024, 2048, 4096, 10240, 65536] as $stored) {
            $this->assertSame($stored, StorageUnit::toMebibytes(StorageUnit::fromMebibytes($stored)));
        }
    }

    public static function disabledValueProvider(): array
    {
        return [[false], ['false'], ['0'], [0]];
    }
}

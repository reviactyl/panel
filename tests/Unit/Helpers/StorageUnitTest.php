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
        $this->assertSame('512 MiB', StorageUnit::formatMegabytes(512));
        $this->assertSame('1.5 GiB', StorageUnit::formatMegabytes(1536));
        $this->assertSame('1 GiB', StorageUnit::formatBytes(1024 ** 3));
        $this->assertSame('0 B', StorageUnit::formatBytes(0));
    }

    #[DataProvider('disabledValueProvider')]
    public function test_decimal_prefixes_are_used_when_binary_is_disabled($value)
    {
        config()->set('panel.use_binary_prefix', $value);

        $this->assertFalse(StorageUnit::isBinary());
        $this->assertSame('MB', StorageUnit::megabyte());
        $this->assertSame('512 MB', StorageUnit::formatMegabytes(512));
        $this->assertSame('1.5 GB', StorageUnit::formatMegabytes(1500));
        $this->assertSame('1 GB', StorageUnit::formatBytes(1000 ** 3));
        $this->assertSame('1.07 GB', StorageUnit::formatBytes(1024 ** 3));
    }

    public function test_limits_are_sent_to_agent_unchanged_with_binary_prefixes()
    {
        $this->assertSame(1024, StorageUnit::toMebibytes(1024));
        $this->assertSame(0, StorageUnit::toMebibytes(0));
        $this->assertSame(-1, StorageUnit::toMebibytes(-1));
    }

    public function test_limits_are_converted_to_mebibytes_with_decimal_prefixes()
    {
        config()->set('panel.use_binary_prefix', false);

        $this->assertSame(954, StorageUnit::toMebibytes(1000));
        $this->assertSame(9537, StorageUnit::toMebibytes(10000));
        $this->assertSame(1, StorageUnit::toMebibytes(1));
        $this->assertSame(0, StorageUnit::toMebibytes(0));
        $this->assertSame(-1, StorageUnit::toMebibytes(-1));
    }

    public static function disabledValueProvider(): array
    {
        return [[false], ['false'], ['0'], [0]];
    }
}

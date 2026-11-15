<?php

namespace Tests\Unit\Services\Eggs;

use App\Services\Eggs\EggImageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EggImageServiceTest extends TestCase
{
    #[DataProvider('images')]
    public function test_image_bytes_and_format_are_preserved(string $name, string $contents, string $mime): void
    {
        $file = UploadedFile::fake()->createWithContent($name, $contents);
        $encoded = app(EggImageService::class)->encode($file);
        $this->assertSame('data:'.$mime.';base64,'.base64_encode($contents), $encoded);
    }

    public static function images(): array
    {
        return [
            'png' => ['image.png', file_get_contents(__DIR__.'/../../../../public/reviactyl/icon.png'), 'image/png'],
            'gif' => ['image.gif', base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'), 'image/gif'],
            'svg' => ['image.svg', '<svg xmlns="http://www.w3.org/2000/svg"><path d="M0 0h10v10H0z"/></svg>', 'image/svg+xml'],
        ];
    }

    public function test_an_image_of_exactly_ten_megabytes_is_accepted(): void
    {
        $contents = str_pad(file_get_contents(public_path('reviactyl/icon.png')), 10 * 1024 * 1024, "\0");
        $encoded = app(EggImageService::class)->encode(UploadedFile::fake()->createWithContent('large.png', $contents));
        $this->assertSame($contents, base64_decode(explode(',', $encoded, 2)[1], true));
    }

    public function test_oversized_images_are_rejected(): void
    {
        $this->expectException(ValidationException::class);
        $contents = str_pad(file_get_contents(public_path('reviactyl/icon.png')), 10 * 1024 * 1024 + 1, "\0");
        app(EggImageService::class)->encode(UploadedFile::fake()->createWithContent('large.png', $contents));
    }

    public function test_a_non_image_cannot_be_disguised_with_an_image_extension(): void
    {
        $temporary = tmpfile();
        fwrite($temporary, '<?php echo "hello";');
        $file = new UploadedFile(stream_get_meta_data($temporary)['uri'], 'image.png', 'image/png', null, true);
        $this->expectException(ValidationException::class);
        try {
            app(EggImageService::class)->encode($file);
        } finally {
            fclose($temporary);
        }
    }
}

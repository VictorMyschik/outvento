<?php

declare(strict_types=1);

namespace Tests\Unit\Services\Image;

use App\Services\Albums\AlbumRepositoryInterface;
use App\Services\Image\AlbumImageResizer;
use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncodedImageInterface;
use Intervention\Image\Interfaces\ImageInterface;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AlbumImageResizerTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testBuildVariantPathUsesSuffixAndWebpExtension(): void
    {
        $resizer = new AlbumImageResizer(
            manager: Mockery::mock(ImageManager::class),
            log: Mockery::mock(LoggerInterface::class),
            repository: Mockery::mock(AlbumRepositoryInterface::class),
            filesystem: Mockery::mock(Filesystem::class),
            config: []
        );

        self::assertSame(
            '7/2026/04/ab/cd/file_preview.webp',
            $resizer->buildVariantPath('7/2026/04/ab/cd/file.jpg', 'preview')
        );
    }

    public function testResizeByPathCreatesAllConfiguredVariantsAsWebp(): void
    {
        $manager = Mockery::mock(ImageManager::class);
        $log = Mockery::mock(LoggerInterface::class);
        $repository = Mockery::mock(AlbumRepositoryInterface::class);
        $filesystem = Mockery::mock(Filesystem::class);
        $image = Mockery::mock(ImageInterface::class);
        $encoded = Mockery::mock(EncodedImageInterface::class);

        $sourceRelative = '7/2026/04/ab/cd/file.jpg';
        $sourceAbsolute = '/tmp/file.jpg';

        $filesystem->shouldReceive('exists')->once()->with($sourceRelative)->andReturn(true);
        $filesystem->shouldReceive('path')->once()->with($sourceRelative)->andReturn($sourceAbsolute);

        $manager->shouldReceive('decodePath')->times(3)->with($sourceAbsolute)->andReturn($image);
        $image->shouldReceive('orient')->times(3)->andReturnSelf();
        $image->shouldReceive('scaleDown')->once()->with(200)->andReturnSelf();
        $image->shouldReceive('scaleDown')->once()->with(800)->andReturnSelf();
        $image->shouldReceive('scaleDown')->once()->with(1600)->andReturnSelf();
        $image->shouldReceive('encodeUsingFormat')->times(3)->with(Format::WEBP, 80)->andReturn($encoded);
        $encoded->shouldReceive('__toString')->times(3)->andReturn('webp-bytes');

        $filesystem->shouldReceive('put')->once()->with('7/2026/04/ab/cd/file_preview.webp', 'webp-bytes');
        $filesystem->shouldReceive('put')->once()->with('7/2026/04/ab/cd/file_medium.webp', 'webp-bytes');
        $filesystem->shouldReceive('put')->once()->with('7/2026/04/ab/cd/file_large.webp', 'webp-bytes');

        $resizer = new AlbumImageResizer(
            manager: $manager,
            log: $log,
            repository: $repository,
            filesystem: $filesystem,
            config: []
        );

        $resizer->resizeByPath($sourceRelative);
    }
}


<?php

declare(strict_types=1);

namespace App\Services\Image;

use App\Services\Albums\AlbumRepositoryInterface;
use Illuminate\Contracts\Filesystem\Filesystem;
use Intervention\Image\Format;
use Intervention\Image\ImageManager;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class AlbumImageResizer
{
    private const array DEFAULT_VARIANTS = [
        'preview' => 200,
        'medium'  => 800,
        'large'   => 1600,
    ];

    private const int DEFAULT_QUALITY = 80;

    public function __construct(
        protected ImageManager           $manager,
        private LoggerInterface          $log,
        private AlbumRepositoryInterface $repository,
        private Filesystem               $filesystem,
        private array                    $config = [],
    ) {}

    public function resizeAvatar(int $albumId): void
    {
        $album = $this->repository->getAlbumById($albumId);

        if (!$album?->avatar) {
            $this->log->warning('Album not found for resize.', ['album_id' => $album]);

            return;
        }

        $this->resizeByPath((string)$album->avatar);
    }

    public function resize(int $mediaId): void
    {
        $media = $this->repository->getAlbumMediaById($mediaId);

        if (!$media?->path) {
            $this->log->warning('Album media not found for resize.', ['media_id' => $mediaId]);

            return;
        }

        $this->resizeByPath((string)$media->path);
    }

    public function resizeByPath(string $relativePath): void
    {
        if (!$this->filesystem->exists($relativePath)) {
            $this->log->warning('Album image file does not exist for resize.', ['path' => $relativePath]);

            return;
        }

        $sourcePath = $this->filesystem->path($relativePath);

        foreach ($this->getVariants() as $suffix => $width) {
            $targetPath = $this->buildVariantPath($relativePath, $suffix);

            try {
                $encoded = $this->manager
                    ->decodePath($sourcePath)
                    ->orient()
                    ->scaleDown(width: $width)
                    ->encodeUsingFormat(Format::WEBP, quality: $this->getQuality());

                $this->filesystem->put($targetPath, (string)$encoded);
            } catch (Throwable $e) {
                $this->log->error('Failed to resize album image variant.', [
                    'source_path' => $relativePath,
                    'target_path' => $targetPath,
                    'variant'     => $suffix,
                    'exception'   => $e,
                ]);

                throw $e;
            }
        }
    }

    public function buildVariantPath(string $relativePath, string $suffix): string
    {
        $info = pathinfo($relativePath);

        $directory = ($info['dirname'] ?? '.') === '.' ? '' : $info['dirname'] . '/';
        $filename = $info['filename'] ?? 'image';

        return sprintf('%s%s_%s.webp', $directory, $filename, $suffix);
    }

    /**
     * @return array<string, int>
     */
    private function getVariants(): array
    {
        $variants = $this->config['resize']['variants'] ?? self::DEFAULT_VARIANTS;

        return is_array($variants) ? $variants : self::DEFAULT_VARIANTS;
    }

    private function getQuality(): int
    {
        $quality = $this->config['resize']['quality'] ?? self::DEFAULT_QUALITY;

        if (!is_int($quality) || $quality < 1 || $quality > 100) {
            return self::DEFAULT_QUALITY;
        }

        return $quality;
    }
}
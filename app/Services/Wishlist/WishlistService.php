<?php

declare(strict_types=1);

namespace App\Services\Wishlist;

final readonly class WishlistService
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
    ) {}

    public function saveWishlist(int $id, array $data): int
    {
       return $this->wishlistRepository->saveWishlist($id, $data);
    }
}
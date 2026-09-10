<?php

namespace App\Services;

/** Liste de favoris stockée en session (clé "wishlist" = tableau d'ids produits). */
class WishlistService
{
    private const KEY = 'wishlist';

    /** @return int[] */
    public function ids(): array
    {
        return array_values(array_unique(array_map('intval', (array) session(self::KEY, []))));
    }

    public function has(int $productId): bool
    {
        return in_array($productId, $this->ids(), true);
    }

    public function count(): int
    {
        return count($this->ids());
    }

    /** @return bool true si ajouté, false si retiré */
    public function toggle(int $productId): bool
    {
        $ids = $this->ids();
        if (in_array($productId, $ids, true)) {
            $ids = array_values(array_diff($ids, [$productId]));
            session([self::KEY => $ids]);

            return false;
        }
        $ids[] = $productId;
        session([self::KEY => $ids]);

        return true;
    }

    public function remove(int $productId): void
    {
        session([self::KEY => array_values(array_diff($this->ids(), [$productId]))]);
    }
}

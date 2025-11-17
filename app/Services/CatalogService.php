<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class CatalogService
{
    /**
     * Get all active products from the catalog.
     *
     * @return Collection<int, Product>
     */
    public function getAllProducts(): Collection
    {
        return Product::where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get a single product by ID.
     */
    public function getProductById(int $productId): ?Product
    {
        return Product::where('is_active', true)
            ->find($productId);
    }

    /**
     * Get a product by SKU.
     */
    public function getProductBySku(string $sku): ?Product
    {
        return Product::where('is_active', true)
            ->where('sku', $sku)
            ->first();
    }

    /**
     * Get products by IDs (for checkout validation).
     *
     * @param array<int> $productIds
     * @return Collection<int, Product>
     */
    public function getProductsByIds(array $productIds): Collection
    {
        return Product::where('is_active', true)
            ->whereIn('id', $productIds)
            ->get();
    }

    /**
     * Search products by name or description.
     *
     * @return Collection<int, Product>
     */
    public function searchProducts(string $query): Collection
    {
        return Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                    ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderBy('name')
            ->get();
    }
}

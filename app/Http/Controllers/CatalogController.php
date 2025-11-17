<?php

namespace App\Http\Controllers;

use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(
        private CatalogService $catalogService
    ) {}

    /**
     * Get all products in the catalog.
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        if ($search) {
            $products = $this->catalogService->searchProducts($search);
        } else {
            $products = $this->catalogService->getAllProducts();
        }

        return response()->json([
            'success' => true,
            'data' => $products,
        ]);
    }

    /**
     * Get a single product by ID.
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->catalogService->getProductById($id);

        if (!$product) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $product,
        ]);
    }
}

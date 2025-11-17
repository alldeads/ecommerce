<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\CatalogService;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    public function __construct(
        private CatalogService $catalogService
    ) {}

    /**
     * Display product catalog page.
     */
    public function index(): Response
    {
        $products = $this->catalogService->getAllProducts();

        return Inertia::render('Products', [
            'products' => $products,
        ]);
    }

    /**
     * Display single product details page.
     */
    public function show(int $id): Response
    {
        $product = $this->catalogService->getProductById($id);

        if (!$product) {
            abort(404, 'Product not found');
        }

        return Inertia::render('ProductDetail', [
            'product' => $product,
        ]);
    }
}

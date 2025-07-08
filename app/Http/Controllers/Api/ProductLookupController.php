<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SapProductService;
use App\Services\ProductService;

class ProductLookupController extends Controller
{
    protected SapProductService $sapService;
    protected ProductService $productService;

    public function __construct(SapProductService $sapService, ProductService $productService)
    {
        $this->sapService = $sapService;
        $this->productService = $productService;
    }

    /**
     * Fetch a product by EAN from local DB or SAP
     */
    public function fetchFromPipo(Request $request)
    {
        $request->validate([
            'ean_number' => 'required|string',
        ]);

        $ean = $request->ean_number;
        $sapProduct = $this->sapService->fetchProductByEan($ean);

        if (
            !$sapProduct ||
            !isset($sapProduct['product_id'], $sapProduct['product_name'], $sapProduct['ean_number'])
        ) {
            return response()->json(['error' => 'Invalid or missing product data from SAP'], 422);
        }
        $localProduct = $this->productService->getProductByEan($ean);
        if (!$localProduct) {
            $localProduct = $this->productService->saveProduct($sapProduct);
        }
        return response()->json([
            'product' => [
                'product_id'        => $localProduct->id,
                'sap_product_id'    => $localProduct->sap_product_id,
                'product_name'      => $localProduct->product_name,
                'ean_number'        => $localProduct->ean_number,
                'material_category' => $localProduct->material_category,
            ],
            'stock_info' => $sapProduct['stock'] ?? null, 
            'price' => $sapProduct['price'] ?? null, 
        ], 200);
    }
}

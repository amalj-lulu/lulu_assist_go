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

        // Step 1: Always call SAP API to get latest stock details
        $sapProduct = $this->sapService->fetchProductByEan($ean);

        if (
            !$sapProduct ||
            !isset($sapProduct['product_id'], $sapProduct['product_name'], $sapProduct['ean_number'])
        ) {
            return response()->json(['error' => 'Invalid or missing product data from SAP'], 422);
        }

        // Step 2: Check if the product exists locally
        $localProduct = $this->productService->getProductByEan($ean);

        // Step 3: If product doesn't exist, save to local DB
        if (!$localProduct) {
            $localProduct = $this->productService->saveProduct($sapProduct);
            $source = 'sap_saved';
        } else {
            $source = 'sap_fetched';
        }

        // Step 4: Return both local product info and latest stock info
        return response()->json([
            'product' => [
                'product_id'        => $localProduct->id,
                'sap_product_id'    => $localProduct->sap_product_id,
                'product_name'      => $localProduct->product_name,
                'ean_number'        => $localProduct->ean_number,
                'material_category' => $localProduct->material_category,
            ],
            'stock_info' => $sapProduct['stock'] ?? null,  // or whatever stock data SAP returns
            'price' => $sapProduct['price'] ?? null, 
        ], 200);
    }
}

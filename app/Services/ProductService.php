<?php

namespace App\Services;

use App\Models\Product;

class ProductService
{
    /**
     * Fetch product by EAN number from local DB
     */
    public function getProductByEan(string $ean): ?Product
    {
        return Product::where('ean_number', $ean)->first();
    }

    /**
     * Fetch product by SAP product ID
     */
    public function getProductBySapId(string $sapId): ?Product
    {
        return Product::where('sap_product_id', $sapId)->first();
    }

    /**
     * Save product to local database
     */
    public function saveProduct(array $data): Product
    {
        return Product::create([
            'sap_product_id'     => $data['product_id'],
            'product_name'       => $data['product_name'],
            'ean_number'         => $data['ean_number'],
            'material_category'  => $data['material_category'] ?? null,
        ]);
    }

    public function upsertProduct(array $data): Product
    {
        $existing = $this->getProductBySapId($data['product_id'])
            ?? $this->getProductByEan($data['ean_number']);

        if ($existing) {
            $existing->update([
                'product_name'       => $data['product_name'] ?? $existing->product_name,
                'material_category'  => $data['material_category'] ?? $existing->material_category,
            ]);
            return $existing;
        }

        return $this->saveProduct($data);
    }
}

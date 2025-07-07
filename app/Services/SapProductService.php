<?php
// app/Services/SapProductService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SapProductService
{
    public function fetchProductByEan(string $ean): ?array
    {
        // Simulate PIPO API response
        // Replace this with actual API call:
        // $response = Http::get("https://pipo.example.com/api/products/{$ean}");
        // if (!$response->successful()) return null;
        // return $response->json();

        // Test response
        return [
            'product_id' => '444',
            'product_name' => 'Samsung 15',
            'ean_number' => $ean,
            'material_category' => 'Electronics',
            'stock' => 100,
            'price' => 200
        ];
    }
}

<?php
// app/Services/SapProductService.php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

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
            'product_id' => '56337_'. Str::uuid()->toString(),
            'product_name' => 'Samsung s23',
            'product_description' => "Samsung s23",
            'ean_number' => $ean,
            'material_category' => 'Mobile',
            'stock' => 100,
            'price' => 200
        ];
    }
}

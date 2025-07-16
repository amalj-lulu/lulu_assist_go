<?php
// app/Services/SapProductService.php

namespace App\Services;

use App\Exceptions\JsonApiException;
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
            'product_id' => '56337_' . Str::uuid()->toString(),
            'product_name' => 'Samsung s23',
            'product_description' => "Samsung s23",
            'ean_number' => $ean,
            'material_category' => 'Mobile',
            'serial_numbers'    => ["S22-ABCD-001", "S22-ABCD-002","S231234531","IPAD-1234-XYZ","s2312334448"],
            'stock' => 100,
            'price' => 200
        ];
    }
    public function validateSelectedSerialsWithSAP(string $ean, array $selectedSerials)
    {
        // Fetch product + serials from SAP via ProductService
        $sapProduct = $this->fetchProductByEan($ean);

        if (!$sapProduct || empty($sapProduct['serial_numbers'])) {
            throw new JsonApiException([
                'status' => false,
                'message' => 'No serial numbers found for the product in SAP',
                'data' => null,
                'errors' => [
                    'sap_api' => ['Serial number list empty or invalid EAN']
                ]
            ], 404);
        }

        $availableSerials = $sapProduct['serial_numbers'];

        // Find serials selected by user but not available in SAP
        $invalidSerials = array_diff($selectedSerials, $availableSerials);

        if (!empty($invalidSerials)) {
            throw new JsonApiException([
                'status' => false,
                'message' => 'Some selected serial numbers are not available in SAP',
                'data' => null,
                'errors' => [
                    'serial_numbers' => [
                        'Invalid serials: ' . implode(', ', $invalidSerials)
                    ]
                ]
            ], 400);
        }

        return true;
    }
}

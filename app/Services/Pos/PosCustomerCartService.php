<?php

namespace App\Services\Pos;

use App\Models\Customer;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PosCustomerCartService
{
    /**
     * Fetch cart and item/serial data for a given customer (by ID or mobile)
     */
    public function fetchCartData(Request $request): array
    {
        // Validation inside the method (or move to a FormRequest if needed)
        $validated = $request->validate([
            'szCustomerID'   => 'nullable|string',
            'szMobileNmbr'   => 'nullable|string',
            'szTxTokenNmbr'  => 'nullable|string',
            'szWorkstation'  => 'nullable|string',
        ]);

        // Use internal method to find customer
        $customer = $this->findCustomer($validated['szCustomerID'] ?? null, $validated['szMobileNmbr'] ?? null);

        if (!$customer) {
            return [
                'status' => 'error',
                'message' => 'Customer not found',
                'payload' => null,
            ];
        }

        // Load cart and item details
        $cart = Cart::with([
            'items' => function ($q) {
                $q->where('is_deleted', false)
                    ->with(['product', 'serials' => function ($q2) {
                        $q2->where('is_deleted', false);
                    }]);
            }
        ])
            ->where('customer_id', $customer->id)
            ->where('status', 'active')
            ->latest()
            ->first();

        if (!$cart) {
            return [
                'status' => 'error',
                'message' => 'Cart not found',
                'payload' => null,
            ];
        }

        // Format items into article list
        $articleList = [];

        foreach ($cart->items as $item) {
            foreach ($item->serials as $serial) {
                $articleList[] = [
                    'szDescription'      => $item->product->product_name,
                    'dTaQty'             => $item->quantity,
                    'dTaPrice'           => $item->product->price,
                    'szItemLookupCode'   => $item->product->ean_number,
                    'lTadiscountflag'    => $item->discount ? -1 : 0,
                    'szSerialNmbr'       => $serial->serial_number,
                ];
            }
        }

        return [
            'status' => 'success',
            'message' => 'Cart loaded successfully',
            'payload' => [
                'szCustomerID'     => $customer->id,
                'szMobileNmber'    => $customer->mobile,
                'szTxTokenNmbr'    => $cart->token,
                'lstArtList'       => $articleList,
            ]
        ];
    }

    /**
     * Find a customer by ID and/or Mobile Number.
     * - If both are given: both must match (AND)
     * - If one is given: match by that (OR)
     */
    public function findCustomer(?string $customerId, ?string $mobile): ?Customer
    {
        if (empty($customerId) && empty($mobile)) {
            return null;
        }

        return Customer::where(function ($query) use ($customerId, $mobile) {
            if (!empty($customerId) && !empty($mobile)) {
                $query->where('id', $customerId)
                    ->where('mobile', $mobile);
            } elseif (!empty($customerId)) {
                $query->where('id', $customerId);
            } elseif (!empty($mobile)) {
                $query->where('mobile', $mobile);
            }
        })->first();
    }
    public function validateCustomer(array $data): array
    {
        $validator = Validator::make($data, [
            'mobile_number' => 'required|string|min:10|max:15',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $customer = Customer::where('mobile', $data['mobile_number'])->first();

        if (!$customer) {
            throw ValidationException::withMessages([
                'mobile_number' => ['Customer not found'],
            ]);
        }
        return [
            'customer' => $customer,
        ];
    }
    public function transformPosOrder(Request $request): Request
    {
        $customerId = $request->input('customer_id');
        $items = $request->input('items', []);


        $transformedItems = [];

        foreach ($items as $item) {
            $ean = $item['ean_number'] ?? null;

            // Get product ID from DB using EAN
            $productId = Product::where('ean_number', $ean)->value('id');

            if (!$productId) {
                continue; // Or throw exception
            }

            $transformedItems[] = [
                'product_id' => $productId,
                'quantity' => $item['quantity'] ?? 1,
                'delivery_type' => $item['delivery_type'] ?? 0,
                'serial_numbers' => $item['serial_numbers'] ?? [],
                'created_by' =>  $request->input('workstation') ?? 'system',
            ];
        }

        // Prepare the transformed request payload
        $transformedData = [
            'customer_id' => $customerId,
            'workstation' => $request->input('workstation'),
            'items' => $transformedItems,
        ];

        // Return a new request instance with transformed structure
        return new Request($transformedData);
    }
}

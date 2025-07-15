<?php

namespace App\Services;

use App\Exceptions\JsonApiException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemSerial;
use App\Models\CartLog;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CartService
{
    public function storeCartWithItems(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'required|array|min:1',
            'items.*.serial_numbers.*' => 'required|string',
        ]);
        if ($error = $this->checkCustomerExists($request->customer_id)) {
            return $error;
        }
        DB::beginTransaction();
        try {
            $cart = Cart::where('customer_id', $request->customer_id)
                ->where('status', 'active')
                ->first();
            if (!$cart) {
                $cart = Cart::create([
                    'token' => Str::uuid()->toString(),
                    'customer_id' => $request->customer_id,
                    'created_by' => auth()->id(),
                    'status' => 'active',
                ]);
            }

            foreach ($request->items as $item) {
                $this->checkSerialNumber($cart->id, $item['serial_numbers']);
                if (!$this->checkProductExists($item['product_id'])) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Product not found',
                        'data' => null
                    ], 404);
                }
                $this->addOrUpdateItem($cart, $item, auth()->id());
            }

            DB::commit();
            $cart->load(['items.product', 'items.serials']);
            return response()->json([
                'status' => true,
                'message' => 'Cart updated',
                'data' => [
                    'cart' => $this->formatCartResponse($cart),
                ],
                'errors' => null
            ], 200);
        } catch (JsonApiException $e) {
            DB::rollBack();
            return response()->json($e->response, $e->getCode());
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to update cart',
                'data' => null,
                'errors' => [
                    'exception' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function addItemToCart(Request $request, $cartId)
    {
        $validator = Validator::make($request->all(), [
            'product_id'     => 'nullable|integer',
            'ean_number'     => 'nullable|string',
            'quantity'       => 'required|integer|min:1',
            'serial_numbers' => 'required',
            'created_by'     => 'required',
        ]);

        // Custom "either product_id or ean_number" rule
        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('product_id') && !$request->filled('ean_number')) {
                $validator->errors()->add('product', 'Either product_id or ean_number is required.');
            }
        });

        // Return if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }
        $productId = $request->input('product_id');
        if (!$productId && $request->filled('ean_number')) {
            $product = Product::where('ean_number', $request->input('ean_number'))->first();
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found with provided EAN number',
                    'data' => null,
                    'errors' => [
                        'ean_number' => 'Product not found for the given EAN number'
                    ]
                ], 404);
            }
            $productId = $product->id;
            $request->merge(['product_id' => $productId]);
        }

        $cart = Cart::find($cartId);
        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart not found',
                'data' => null,
                'errors' => [
                    'cart' => 'Cart not found'
                ]
            ], 404);
        }

        if ($cart->status === 'abandoned') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot add items to an abandoned cart',
                'data' => null,
                'errors' => [
                    'cart' => 'Cannot add items to an abandoned cart'
                ]
            ], 400);
        }

        $createdBy = $request->input('created_by', auth()->id()); // Set default to 0 if not passed

        DB::beginTransaction();
        try {
            $this->addOrUpdateItem($cart, $request->all(), $createdBy);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Item added',
                'data' => [
                    'cart_id' => $this->formatCartResponse($cart)
                ],
                'errors' => null
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to add item',
                'data' => null,
                'errors' => [
                    'exception' => $e->getMessage()
                ]
            ], 500);
        }
    }

    public function removeItemFromCart(Request $request, $cartId)
    {
        $validator = Validator::make($request->all(), [
            'product_id'     => 'nullable|integer',
            'ean_number'     => 'nullable|string',
            'quantity'       => 'required|integer|min:1',
            'serial_number'  => 'required|string',
            'created_by'     => 'nullable',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (!$request->filled('product_id') && !$request->filled('ean_number')) {
                $validator->errors()->add('product', 'Either product_id or ean_number is required.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $validator->errors()
            ], 422);
        }

        // Resolve product_id from ean_number if needed
        $productId = $request->input('product_id');
        if (!$productId && $request->filled('ean_number')) {
            $product = Product::where('ean_number', $request->ean_number)->first();
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'Product not found for the given EAN number',
                    'data' => null,
                    'errors' => ['ean_number' => 'No product found']
                ], 404);
            }
            $productId = $product->id;
        }
        $cart = Cart::find($cartId);
        if (!$cart) {
            return response()->json([
                'status' => false,
                'message' => 'Cart not found',
                'data' => null,
                'errors' => null,
            ], 404);
        }

        if ($cart->status === 'abandoned') {
            return response()->json([
                'status' => false,
                'message' => 'Cannot remove items from an abandoned cart',
                'data' => null,
                'errors' => null,
            ], 400);
        }

        DB::beginTransaction();
        try {
            $cartItem = CartItem::where('cart_id', $cartId)
                ->where('product_id', $productId)
                ->where('is_deleted', false)
                ->first();

            if (!$cartItem) {
                return response()->json([
                    'status' => false,
                    'message' => 'Item not found or already deleted',
                    'data' => null,
                    'errors' => null,
                ], 404);
            }

            $serial = CartItemSerial::where('cart_item_id', $cartItem->id)
                ->where('serial_number', $request->serial_number)
                ->where('is_deleted', false)
                ->first();

            if (!$serial) {
                return response()->json([
                    'status' => false,
                    'message' => 'Serial number does not match or is already removed',
                    'data' => null,
                    'errors' => null,
                ], 422);
            }

            if ($request->quantity > $cartItem->quantity) {
                return response()->json([
                    'status' => false,
                    'message' => 'Quantity exceeds the current item quantity',
                    'data' => null,
                    'errors' => null,
                ], 422);
            }

            // Perform removal
            if ($request->quantity == $cartItem->quantity) {
                $cartItem->update(['is_deleted' => true]);
                $action = 'remove_item';
            } else {
                $cartItem->decrement('quantity', $request->quantity);
                $action = 'reduce_quantity';
            }

            // Soft delete the serial number
            $serial->update(['is_deleted' => true]);

            // Log the action
            CartLog::create([
                'cart_id' => $cartId,
                'cart_item_id' => $cartItem->id,
                'action' => $action,
                'details' => json_encode([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $request->quantity,
                    'serial_number' => $request->serial_number,
                ]),
                'performed_by' => ($request->created_by) ? $request->created_by : auth()->id(),
            ]);

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Item removed',
                'data' => [
                    'cart_id' => $this->formatCartResponse($cart)
                ],
                'errors' => null
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to remove item',
                'data' => null,
                'errors' => [
                    'exception' => $e->getMessage()
                ]
            ], 500);
        }
    }


    public function getCartDetails($cartId)
    {
        $cart = Cart::with([
            'items' => function ($q) {
                $q->where('is_deleted', false)
                    ->with([
                        'product', // Load related product
                        'serials' => fn($query) => $query->where('is_deleted', false)
                    ]);
            }
        ])->find($cartId);

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        if ($cart->status === 'abandoned') {
            return response()->json(['error' => 'Cart has been abandoned'], 400);
        }

        return response()->json($this->formatCartResponse($cart), 200);
    }

    public function getCartDetailsByMobile($customer_id)
    {
        $cart = Cart::with([
            'items' => function ($q) {
                $q->where('is_deleted', false)->with([
                    'product',
                    'serials' => fn($query) => $query->where('is_deleted', false)
                ]);
            }
        ])
            ->where('customer_id', $customer_id)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return [
                'status' => false,
                'message' => 'Cart not found',
                'data' => [
                    'cart_id' => null,
                    'items' => [],
                ],
                'errors' => null
            ];
        }
        if ($cart->status === 'abandoned') {

            return [
                'status' => false,
                'message' => 'Cart has been abandoned',
                'data' => [
                    'cart_id' => null,
                    'items' => [],
                ],
                'errors' => null
            ];
        }

        return $this->formatCartResponse($cart);
    }

    public function abandonCart($cartId)
    {
        $cart = Cart::with('items.serials')->find($cartId);

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
        if ($cart->status === 'abandoned') {
            return response()->json(['error' => 'Cart already abandoned'], 400);
        }

        DB::beginTransaction();
        try {
            $cart->update(['status' => 'abandoned']);

            foreach ($cart->items as $item) {
                CartItemSerial::where('cart_item_id', $item->id)
                    ->update(['is_deleted' => true]);
                $item->update(['is_deleted' => true]);

                CartLog::create([
                    'cart_id' => $cart->id,
                    'cart_item_id' => $item->id,
                    'action' => 'remove_item',
                    'details' => json_encode([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'serial_numbers' => $item->serials->pluck('serial_number'),
                    ]),
                    'performed_by' => $item->created_by,
                ]);
            }

            CartLog::create([
                'cart_id' => $cart->id,
                'action' => 'abandon_cart',
                'details' => json_encode(['message' => 'Cart abandoned']),
                'performed_by' => 1,
            ]);

            DB::commit();
            return response()->json(['message' => 'Cart abandoned'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to abandon cart', 'details' => $e->getMessage()], 500);
        }
    }
    public function checkSerialNumber($cartId, $serialNumbers)
    {
        // Check if the serial numbers already exist in the cart via the CartItemSerial and CartItem models
        $existingSerials = CartItemSerial::whereIn('serial_number', $serialNumbers)
            ->where('is_deleted', false)
            ->whereHas('cartItem', function ($query) use ($cartId) {
                $query->where('cart_id', $cartId)
                    ->where('is_deleted', false);
            })
            ->exists();
        if ($existingSerials) {
            throw new JsonApiException([
                'status' => false,
                'message' => 'Some serial numbers already exist in this cart',
                'data' => null,
                'errors' => [
                    'serial_numbers' => ['Duplicate serial numbers found in cart']
                ]
            ], 400);
        }
        return null;
    }
    public function checkProductExists(int $productId): bool
    {
        return Product::find($productId) !== null;
    }

    private function addOrUpdateItem(Cart $cart, array $item, $createdBy): void
    {
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $item['product_id'])
            ->where('is_deleted', false)
            ->first();
        if ($cartItem) {
            $cartItem->increment('quantity', $item['quantity']);
        } else {
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'delivery_type' => $item['delivery_type'] ?? 0,
                'created_by' => $createdBy,
                'customer_id' => $cart->customer_id,
            ]);
        }

        foreach ($item['serial_numbers'] as $serial) {
            CartItemSerial::create([
                'cart_item_id' => $cartItem->id,
                'serial_number' => $serial,
                'created_by' => $createdBy,
            ]);
        }

        CartLog::create([
            'cart_id' => $cart->id,
            'cart_item_id' => $cartItem->id,
            'action' => 'add_item',
            'details' => json_encode([
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'serial_numbers' => $item['serial_numbers']
            ]),
            'performed_by' => $createdBy,
        ]);
    }
    private function checkCustomerExists($customerId)
    {
        if (! Customer::where('id', $customerId)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }
        return null; // Means customer exists
    }
    public function formatCartResponse($cart)
    {
        $cart->load([
            'items' => fn($q) => $q->where('is_deleted', false),
            'items.serials' => fn($q) => $q->where('is_deleted', false),
        ]);

        return [
            'cart_id' => $cart->id,
            'token' => $cart->token,
            'customer_id' => $cart->customer_id,
            'status' => $cart->status,
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->product_name,
                    'quantity' => $item->quantity,
                    'ean_number' => $item->product->ean_number,
                    'created_by' => $item->created_by,
                    'serial_numbers' => $item->serials->pluck('serial_number')->values(),
                ];
            })->values(),
        ];
    }
}

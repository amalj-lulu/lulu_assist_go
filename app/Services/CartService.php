<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemSerial;
use App\Models\CartLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'created_by' => 'required|integer',
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
                    'customer_id' => $request->customer_id,
                    'created_by' => $request->created_by,
                    'status' => 'active',
                ]);
            }

            foreach ($request->items as $item) {
                $this->checkSerialNumberExists($cart->id, $item['serial_numbers']);
                $this->addOrUpdateItem($cart, $item, $request->created_by);
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
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string',
            'created_by' => 'required|integer',
        ]);

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
            return response()->json(['error' => 'Cannot add items to an abandoned cart'], 400);
            return response()->json([
                'status' => false,
                'message' => 'Cannot add items to an abandoned cart',
                'data' => null,
                'errors' => [
                    'cart' => 'Cannot add items to an abandoned cart'
                ]
            ], 400);
        }
        DB::beginTransaction();
        try {
            $this->addOrUpdateItem($cart, $request->all(), $request->created_by);
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
        $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string',
            'created_by' => 'required|integer',
        ]);

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
                ->where('product_id', $request->product_id)
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

            $serials = CartItemSerial::where('cart_item_id', $cartItem->id)
                ->whereIn('serial_number', $request->serial_numbers)
                ->where('is_deleted', false)
                ->get();

            if ($serials->count() !== count($request->serial_numbers)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Some serial numbers do not match or are already removed',
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

            // Soft delete the serials
            CartItemSerial::where('cart_item_id', $cartItem->id)
                ->whereIn('serial_number', $request->serial_numbers)
                ->update(['is_deleted' => true]);

            CartLog::create([
                'cart_id' => $cartId,
                'cart_item_id' => $cartItem->id,
                'action' => $action,
                'details' => json_encode([
                    'product_id' => $cartItem->product_id,
                    'quantity' => $request->quantity,
                    'serial_numbers' => $request->serial_numbers,
                ]),
                'performed_by' => $request->created_by,
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
    public function checkSerialNumberExists($cartId, $serialNumbers)
    {
        // Check if the serial numbers already exist in the cart via the CartItemSerial and CartItem models
        $existingSerials = CartItemSerial::whereIn('serial_number', $serialNumbers)
            ->whereHas('cartItem', function ($query) use ($cartId) {
                $query->where('cart_id', $cartId);  // Check cart_id from the CartItem model
            })
            ->exists();

        if ($existingSerials) {
            // Instead of returning a response, throw a custom exception
            throw new \Exception('Some serial numbers already exist in this cart', 400);
        }

        // No conflict, return null indicating everything is fine
        return null;
    }

    private function addOrUpdateItem(Cart $cart, array $item, int $createdBy): void
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
                'created_by' => $createdBy,
                'customer_id' => $cart->customer_id,
            ]);
        }

        foreach ($item['serial_numbers'] as $serial) {
            CartItemSerial::create([
                'cart_item_id' => $cartItem->id,
                'serial_number' => $serial,
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

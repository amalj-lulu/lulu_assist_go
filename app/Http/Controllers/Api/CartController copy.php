<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemSerial;
use App\Models\CartLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    public function store(Request $request)
    {
        // Validate input parameters
        $request->validate([
            'customer_id' => 'required|uuid',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.ean_number' => 'required|string',
            'items.*.product_name' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'required|array|min:1',
            'items.*.serial_numbers.*' => 'required|string', // Array of serial numbers
            'created_by' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            // Find or create the cart for the customer
            $cart = $this->storeCart($request->customer_id, $request->created_by);

            // Add items to the cart using the reusable addItemToCart function
            foreach ($request->items as $item) {
                $this->addItemToCart($cart, $item, $request->created_by);
            }

            DB::commit();

            return response()->json(['message' => 'Cart updated', 'cart_id' => $cart->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to update cart', 'details' => $e->getMessage()], 500);
        }
    }


    public function addItem(Request $request, $cartId)
    {
        // Validate input parameters
        $request->validate([
            'product_id' => 'required|integer',
            'ean_number' => 'required|string',
            'product_name' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string', // Array of serial numbers
            'created_by' => 'required|integer',  // Assuming this is the ID of the user adding the item
        ]);

        DB::beginTransaction();

        try {
            // Find the cart by ID
            $cart = Cart::find($cartId);

            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }

            // Add the item to the cart using the reusable addItemToCart function
            $this->addItemToCart($cart, $request->all(), $request->created_by);

            DB::commit();

            return response()->json(['message' => 'Item added to cart', 'cart_id' => $cart->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to add item to cart', 'details' => $e->getMessage()], 500);
        }
    }
    public function storeCart($customerId, $createdBy)
    {
        // Find or create the cart based on customer_id and status 'active'
        $cart = Cart::where('customer_id', $customerId)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            // If no active cart exists, create a new one
            $cart = Cart::create([
                'customer_id' => $customerId,
                'created_by' => $createdBy,
                'status' => 'active',
            ]);
        }

        return $cart; // Return the found or newly created cart
    }

    private function addItemToCart($cart, $item, $createdBy)
    {
        // Check if CartItem already exists based on product_id and ean_number
        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $item['product_id'])
            ->where('ean_number', $item['ean_number'])
            ->first();

        if ($cartItem) {
            // Item exists, update quantity
            $cartItem->quantity += $item['quantity'];
            $cartItem->save();

            // Add serial numbers to the item
            foreach ($item['serial_numbers'] as $serial) {
                CartItemSerial::create([
                    'cart_item_id' => $cartItem->id,
                    'serial_number' => $serial,
                ]);
            }
        } else {
            // Item doesn't exist, create a new CartItem
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $item['product_id'],
                'ean_number' => $item['ean_number'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'created_by' => $createdBy,
            ]);

            // Add the serial numbers for the new item
            foreach ($item['serial_numbers'] as $serial) {
                CartItemSerial::create([
                    'cart_item_id' => $cartItem->id,
                    'serial_number' => $serial,
                ]);
            }
        }

        // Log the action of adding/updating the item
        CartLog::create([
            'cart_id' => $cart->id,
            'cart_item_id' => $cartItem->id,
            'action' => 'add_item',
            'details' => json_encode([
                'product_id' => $item['product_id'],
                'product_name' => $item['product_name'],
                'quantity' => $item['quantity'],
                'serial_numbers' => $item['serial_numbers']
            ]),
            'performed_by' => $createdBy,
        ]);
    }


    public function getCartDetails($cartId)
    {
        $cart = Cart::with('items.serials') // Load items and their serials
            ->where('id', $cartId)
            ->where('status', 'active') // Optional: ensure it's only an active cart
            ->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found or inactive'], 404);
        }

        // Return the cart with its items and serial numbers
        return response()->json([
            'cart_id' => $cart->id,
            'customer_id' => $cart->customer_id,
            'status' => $cart->status,
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'ean_number' => $item->ean_number,
                    'serial_numbers' => $item->serials->pluck('serial_number'), // Get all serial numbers for the item
                ];
            }),
        ]);
    }

    public function removeItem(Request $request, $cartId)
    {
        // Validate input parameters
        $request->validate([
            'product_id' => 'required|integer',
            'ean_number' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'serial_numbers' => 'required|array|min:1',
            'serial_numbers.*' => 'required|string', // Array of serial numbers
            'created_by' => 'required|integer',  // Assuming this is the ID of the user performing the removal
        ]);

        DB::beginTransaction();

        try {
            // Find the cart by ID
            $cart = Cart::find($cartId);

            if (!$cart) {
                return response()->json(['error' => 'Cart not found'], 404);
            }

            // Find the cart item based on product_id, ean_number, and is_deleted = false
            $cartItem = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $request->product_id)
                ->where('ean_number', $request->ean_number)
                ->where('is_deleted', false) // Ensure the item is not deleted
                ->first();

            if (!$cartItem) {
                return response()->json(['error' => 'Item not found or already deleted'], 404);
            }

            // If quantity to remove is greater than or equal to the current quantity, soft delete the cart item
            if ($request->quantity >= $cartItem->quantity) {
                // Create a log entry for item removal
                CartLog::create([
                    'cart_id' => $cart->id,
                    'cart_item_id' => $cartItem->id,
                    'action' => 'remove_item',
                    'details' => json_encode([
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product_name,
                        'quantity' => $cartItem->quantity,
                        'serial_numbers' => $request->serial_numbers
                    ]),
                    'performed_by' => $request->created_by,
                ]);

                // Soft delete the serial numbers that were specified (set is_deleted = true)
                CartItemSerial::where('cart_item_id', $cartItem->id)
                    ->whereIn('serial_number', $request->serial_numbers)
                    ->update(['is_deleted' => true]); // Soft delete the serial numbers

                // Soft delete the cart item (set is_deleted = true)
                $cartItem->is_deleted = true;
                $cartItem->save();
            } else {
                // If only quantity needs to be updated, reduce the quantity
                $cartItem->quantity -= $request->quantity;
                $cartItem->save();

                // Soft delete the corresponding serial numbers
                CartItemSerial::where('cart_item_id', $cartItem->id)
                    ->whereIn('serial_number', $request->serial_numbers)
                    ->update(['is_deleted' => true]); // Soft delete the serial numbers

                // Create a log entry for item quantity reduction
                CartLog::create([
                    'cart_id' => $cart->id,
                    'cart_item_id' => $cartItem->id,
                    'action' => 'reduce_quantity',
                    'details' => json_encode([
                        'product_id' => $cartItem->product_id,
                        'product_name' => $cartItem->product_name,
                        'quantity' => $cartItem->quantity,
                        'serial_numbers' => $request->serial_numbers
                    ]),
                    'performed_by' => $request->created_by,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Item removed from cart', 'cart_item_id' => $cartItem->id], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Failed to remove item from cart', 'details' => $e->getMessage()], 500);
        }
    }



    public function destroy($cartId)
    {
        $cart = Cart::with('items.serials')->find($cartId);

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        foreach ($cart->items as $item) {
            CartLog::create([
                'cart_id' => $cart->id,
                'cart_item_id' => $item->id,
                'action' => 'remove_item',
                'details' => json_encode([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'serial_numbers' => $item->serials->pluck('serial_number')->toArray()
                ]),
                'performed_by' => $item->created_by,
            ]);

            $item->serials()->delete();
        }

        $cart->items()->delete();
        $cart->delete();

        return response()->json(['message' => 'Cart deleted']);
    }
    public function destroy1($cartId)
    {
        // Find the cart with its items and serials
        $cart = Cart::with('items.serials')->find($cartId);

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }
        // Check if the cart is already abandoned
        if ($cart->status === 'abandoned') {
            return response()->json(['message' => 'Cart is already abandoned'], 400);
        }
        // Create a log entry for cart abandonment
        CartLog::create([
            'cart_id' => $cart->id,
            'action' => 'abandon_cart',
            'details' => json_encode([
                'cart_id' => $cart->id,
                'message' => 'Cart has been abandoned'
            ]),
            'performed_by' => 1,  // Assuming you have user info (like admin id)
        ]);

        // Set cart status to 'abandoned'
        $cart->status = 'abandoned';
        $cart->save();

        // Loop through cart items to perform soft delete
        foreach ($cart->items as $item) {
            // Create a log entry for the soft deletion of each item
            CartLog::create([
                'cart_id' => $cart->id,
                'cart_item_id' => $item->id,
                'action' => 'remove_item',
                'details' => json_encode([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'serial_numbers' => $item->serials->pluck('serial_number')->toArray()
                ]),
                'performed_by' => $item->created_by,  // Assuming the admin is performing this action
            ]);

            // Soft delete serial numbers related to this item
            foreach ($item->serials as $serial) {
                $serial->update(['is_deleted' => true]);  // Mark serial number as deleted
            }

            // Soft delete the item
            $item->update(['is_deleted' => true]);  // Mark the cart item as deleted
        }

        // Return success response
        return response()->json(['message' => 'Cart status set to abandoned and items soft-deleted']);
    }
}

<?php

namespace App\Services;

use App\Models\{Cart, CartLog, Product, Order, OrderItem, OrderItemSerial, OrderLog};
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OrderService
{
    public function checkout(Request $request, Cart $cart): array
    {
        $cart->load('items.serials');

        $this->log($cart, null, 'Start checkout', 'Cart loaded');

        if ($cart->items->isEmpty()) {
            $this->log($cart, null, 'Checkout failed', 'Cart is empty', 'failed');
            return [
                'status' => false,
                'message' => 'Cart is empty',
                'data' => null,
                'errors' => [
                    'cart' => ['No items found in cart']
                ],
            ];
        }
        DB::beginTransaction();
        try {
            $priceMapper = $this->priceMapper($request);
            $order = Order::create([
                'order_number' => Str::uuid(),
                'customer_id' => $cart->customer_id,
                'total_amount' => 0,
            ]);
            $this->log($cart, $order, 'Order created', 'Order initialized', 'success');

            $totalAmount = 0;

            foreach ($cart->items as $item) {
                $product = Product::find($item->product_id);

                if (!$product) {
                    DB::rollBack();
                    $this->log($cart, $order, 'Checkout failed', "Product ID {$item->product_id} not found", 'failed');
                    return [
                        'status' => false,
                        'message' => "Product ID {$item->product_id} not found",
                        'data' => null,
                        'errors' => [
                            'product' => ["Product ID {$item->product_id} not found"]
                        ],
                    ];
                }

                if ($item->serials->count() != $item->quantity) {
                    DB::rollBack();
                    $this->log($cart, $order, 'Serial mismatch', [
                        'product_id' => $item->product_id,
                        'expected' => $item->quantity,
                        'actual' => $item->serials->count(),
                    ], 'failed');
                    return ['success' => false, 'message' => "Serial number count mismatch for product ID {$item->product_id}"];
                }

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => 0,
                    'total_price' => 0,
                    'created_by' => $item->created_by,
                ]);

                $itemTotal = 0;

                foreach ($item->serials as $serial) {
                    $serialPrice = $priceMapper[$product->ean_number] ?? $product->price;

                    OrderItemSerial::create([
                        'order_item_id' => $orderItem->id,
                        'serial_number' => $serial->serial_number,
                        'price' => $serialPrice,
                    ]);

                    $itemTotal += $serialPrice;

                    $this->log($cart, $order, 'Serial assigned', [
                        'serial' => $serial->serial_number,
                        'price' => $serialPrice,
                    ]);
                }

                $orderItem->update([
                    'unit_price' => $itemTotal / $item->quantity,
                    'total_price' => $itemTotal,
                ]);

                $totalAmount += $itemTotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            $cart->update(['status' => 'checked_out']);
            CartLog::create([
                'cart_id' => $cart->id,
                'action' => 'checked_out',
                'details' => json_encode([
                    'order_number' => $order->order_number,
                    'total_amount' => $totalAmount
                ]),
                'performed_by' => ($request->workstation) ?? auth()->id() ?? null,
            ]);


            DB::commit();

            $this->log($cart, $order, 'Checkout complete', [
                'total_amount' => $totalAmount
            ], 'success');

            return [
                'status' => true,
                'message' => 'Checkout successful',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ],
                'errors' => null,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->log($cart, null, 'Checkout failed', $e->getMessage(), 'failed');
            return [
                'status' => false,
                'message' => 'Checkout failed',
                'data' => null,
                'errors' => [
                    'exception' => [$e->getMessage()]
                ],
            ];
        }
    }

    protected function log($cart, $order, $action, $details, $status = 'info')
    {
        OrderLog::create([
            'cart_id' => $cart->id ?? null,
            'order_id' => $order->id ?? null,
            'order_number' => $order->order_number ?? null,
            'action' => $action,
            'details' => is_array($details) ? json_encode($details) : $details,
            'status' => $status,
            'action' => $action,
        ]);
    }
    protected function priceMapper(Request $request)
    {
        $items = $request->input('items', []);

        // Create single-dimensional array: ean_number => price
        $eanPriceMap = [];

        foreach ($items as $item) {
            $ean = $item['ean_number'] ?? null;
            $price = $item['price'] ?? null;

            if ($ean !== null && $price !== null) {
                $eanPriceMap[$ean] = $price;
            }
        }

        return $eanPriceMap;
    }
}

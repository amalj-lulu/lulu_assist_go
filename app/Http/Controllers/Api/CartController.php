<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    private CartService $service;

    public function __construct(CartService $service)
    {
        $this->service = $service;
    }

    public function store(Request $request)
    {
        return $this->service->storeCartWithItems($request);
    }

    public function addItem(Request $request, $cartId)
    {
        try {
            $this->service->checkSerialNumberExists($cartId, $request->serial_numbers);
            return $this->service->addItemToCart($request, $cartId);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }


    public function removeItem(Request $request, $cartId)
    {
        return $this->service->removeItemFromCart($request, $cartId);
    }

    public function getCartDetails($cartId)
    {
        return $this->service->getCartDetails($cartId);
    }

    public function destroy($cartId)
    {
        return $this->service->abandonCart($cartId);
    }
    public function details(Request $request)
    {
        $cart =  $this->service->getCartDetailsByMobile($request->customer_id);

        return response()->json($cart, 200);
    }
}

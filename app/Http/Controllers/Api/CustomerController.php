<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Services\CartService;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    public function register(Request $request, CartService $cartService)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'mobile' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();

        // Check if customer exists
        $customer = Customer::where('mobile', $validated['mobile'])->first();

        if (!$customer) {
            $customer = Customer::create($validated);
            $message = 'Customer registered successfully.';
            $statusCode = 201;
        } else {
            $message = 'Customer already exists.';
            $statusCode = 200;
        }

        $cart = $cartService->getCartDetailsByMobile($customer->id);

        return response()->json([
            'message' => $message,
            'customer' => $customer,
            'cart' => $cart
        ], $statusCode);
    }
}

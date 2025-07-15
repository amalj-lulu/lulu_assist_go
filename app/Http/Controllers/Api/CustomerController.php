<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\CustomerAttempt;
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
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $performedBy = auth()->id() ?? $validated['performed_by'] ?? $validated['workstation'] ?? null;


        // Check if customer exists
        $customer = Customer::where('mobile', $validated['mobile'])->first();

        if (!$customer) {
            $customer = Customer::create($validated);
            $message = 'Customer registered successfully.';
            $statusCode = 201;
            $action = 'created';
        } else {
            $action = 'existing';
            $message = 'Customer already exists.';
            $statusCode = 200;
        }

        CustomerAttempt::create([
            'customer_id' => $customer->id ?? null,
            'mobile' => $validated['mobile'],
            'action' => $action,
            'performed_by' => $performedBy,
        ]);

        $cart = $cartService->getCartDetailsByMobile($customer->id);

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => [
                'customer' => $customer,
                'cart'     => $cart
            ],
        ], $statusCode);
    }

    public function getActiveCustomers(Request $request)
    {
        try {
            $performedBy = auth()->id();

            if (!$performedBy) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized',
                    'data' => null,
                    'errors' => ['auth' => ['User not authenticated']]
                ], 401);
            }

            $customers = Customer::whereIn('id', function ($query) use ($performedBy) {
                $query->select('customer_id')
                    ->from('customer_attempts')
                    ->where('performed_by', $performedBy);
            })
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Active customers fetched successfully',
                'data' => $customers,
                'errors' => null,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error fetching active customers',
                'data' => null,
                'errors' => ['exception' => [$e->getMessage()]],
            ], 500);
        }
    }
}

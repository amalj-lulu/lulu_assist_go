<?php

namespace App\Http\Controllers\Api\Pos;

use App\Exceptions\JsonApiException;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Customer;
use App\Models\CustomerAttempt;
use App\Models\Product;
use App\Services\CartService;
use App\Services\Pos\PosCustomerCartService;
use App\Services\ProductService;
use App\Services\SapProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PosCustomerCartController extends Controller
{
    protected $posCartService;
    private CartService $cartService;
    protected SapProductService $sapService;
    protected ProductService $productService;

    public function __construct(PosCustomerCartService $posCartService, CartService $cartService, SapProductService $sapService, ProductService $productService)
    {
        $this->posCartService = $posCartService;
        $this->cartService = $cartService;
        $this->sapService = $sapService;
        $this->productService = $productService;
    }

    public function getCartDetails(Request $request)
    {
        $data = $this->posCartService->fetchCartData($request);

        if ($data['status'] === 'error') {
            return response()->json(['message' => $data['message']], 404);
        }

        return response()->json($data['payload']);
    }
    public function addItem(Request $request)
    {
        try {
            $request->validate([
                'mobile_number' => 'required|string|min:10|max:15'
            ]);

            $result = $this->posCartService->validateCustomer($request->only('mobile_number'));
            $customer = $result['customer'];
            if (!$request->has('customer_id') || empty($request->customer_id)) {
                $request->merge([
                    'customer_id' => $customer->id ?? 0
                ]);
            }
            // if (!$request->has('created_by') || empty($request->created_by)) {
            //     $request->merge([
            //         'created_by' => $request->workstation ?? 0
            //     ]);
            // }
            // $productId = Product::where('ean_number', $request->ean_number)->value('id');
            // if (!$request->has('product_id') || empty($request->product_id)) {
            //     $request->merge([
            //         'product_id' => $productId ?? 0
            //     ]);
            // }

            $request = $this->transformPosOrder($request);
            return $this->cartService->storeCartWithItems($request);
        } catch (JsonApiException $e) {
            return response()->json($e->response, $e->getCode());
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
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

    public function removeItem(Request $request)
    {
        try {
            $request->validate([
                'mobile_number' => 'required|string|min:10|max:15'
            ]);
            $result = $this->posCartService->validateCustomer($request->only('mobile_number'));
            $customer = $result['customer'];
            $cart = Cart::where('customer_id', $customer->id)
                ->where('status', 'active')
                ->latest()
                ->first();

            if (!$request->has('created_by') || empty($request->created_by)) {
                $request->merge([
                    'created_by' => $request->workstation ?? 0
                ]);
            }
            return $this->cartService->removeItemFromCart($request, $cart->id);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors()
            ], 422);
        } catch (JsonApiException $e) {
            return response()->json($e->response, $e->getCode());
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unexpected error',
                'data' => null,
                'errors' => [
                    'exception' => [$e->getMessage()]
                ]
            ], 500);
        }
    }
    public function checkSerialNumber(Request $request)
    {
        try {
            $request->validate([
                'mobile_number' => 'required|string|min:10|max:15'
            ]);

            $cart = Cart::where('token', $request->token)
                ->where('status', 'active')
                ->first();

            if (!$cart) {
                return response()->json([
                    'status' => false,
                    'message' => 'Cart not found or inactive',
                    'data' => null,
                    'errors' => ['token' => ['Invalid or inactive cart']],
                ], 404);
            }

            $customer = Customer::where('mobile', $request->mobile_number)->first();


            if (!$customer) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer not found',
                    'data' => null,
                    'errors' => ['mobile_number' => ['Customer not found']],
                ], 404);
            }

            if ($cart->customer_id !== $customer->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Customer does not match the cart',
                    'data' => null,
                    'errors' => ['mobile_number' => ['This customer does not own the cart']],
                ], 403);
            }
            if (!is_array($request->serial_numbers)) {
                $request->merge([
                    'serial_numbers' => [(string) $request->serial_numbers]
                ]);
            }

            $this->cartService->checkSerialNumber($cart->id, null, $request->serial_numbers, $request->ean_number);
        } catch (JsonApiException $e) {
            return response()->json($e->response, $e->getCode());
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
    public function fetchFromPipo(Request $request, CartService $cartService)
    {
        try {
            // Step 1: Validate EAN only here
            $request->validate([
                'ean_number' => 'required|string',
            ]);
            // Step 3: Fetch product from SAP
            $ean = $request->ean_number;
            $sapProduct = $this->sapService->fetchProductByEan($ean);

            if (
                !$sapProduct ||
                !isset($sapProduct['product_id'], $sapProduct['product_name'], $sapProduct['ean_number'])
            ) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or missing product data from SAP',
                    'data' => null,
                    'errors' => ['ean_number' => ['Invalid or missing product data from SAP']]
                ], 422);
            }

            // Step 4: Ensure local product exists
            $localProduct = $this->productService->getProductByEan($ean);
            if (!$localProduct) {
                $localProduct = $this->productService->saveProduct($sapProduct);
            }

            // Step 5: Success response
            return response()->json([
                'status' => true,
                'message' => 'Product fetched successfully',
                'data' => [
                    'product' => [
                        'product_id'          => $localProduct->id,
                        'sap_product_id'      => $localProduct->sap_product_id,
                        'product_name'        => $localProduct->product_name,
                        'product_description' => $localProduct->product_description,
                        'ean_number'          => $localProduct->ean_number,
                        'material_category'   => $localProduct->material_category,
                        "serial_numbers"     => ["S22-ABCD-001", "S22-ABCD-002"]
                    ],
                    'stock_info' => $sapProduct['stock'] ?? null,
                    'price'      => $sapProduct['price'] ?? null,
                ],
                'errors' => null,
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Validation failed',
                'data' => null,
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Unexpected error',
                'data' => null,
                'errors' => [
                    'exception' => [$e->getMessage()]
                ]
            ], 500);
        }
    }
    public function customerRegistration(Request $request, CartService $cartService)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255',
            'mobile' => 'required|string',
            'workstation' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validation failed.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        $performedBy = $validated['workstation'] ;


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
}

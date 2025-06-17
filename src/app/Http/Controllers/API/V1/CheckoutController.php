<?php

namespace App\Http\Controllers\API\V1;


use App\DTOs\CheckoutRequestData;
use App\Http\Controllers\Controller;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService)
    {
    }

    /**
     * To generate the checkout / payment session
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function checkout(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'email' => 'required|email',
                'amount' => 'required|numeric|min:1',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or transaction amount',
                'errors' => $e->errors(),
            ], 422);
        }

        $dto = CheckoutRequestData::fromArray($validated);

        $checkoutResponse = $this->checkoutService->createCheckout($dto);

        return response()->json([
            'success' => true,
            'data' => $checkoutResponse->toArray(),
        ]);
    }
}

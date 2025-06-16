<?php

namespace App\Http\Controllers\API\V1;


use App\Enums\TransactionStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Services\CheckoutService;
use App\DTOs\CheckoutRequestData;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Enum;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function __construct(protected CheckoutService $checkoutService) {}

    /**
     * To generate the checkout / payment session
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
        $checkoutUrl = $this->checkoutService->createCheckout($dto);

        return response()->json([
            'success' => true,
            'checkout_url' => $checkoutUrl,
        ]);
    }


    /**
     * It will update the status of the transactions over time.
     * @param Request $request
     * @return JsonResponse
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $data = $request->validate([
                'transaction_id' => 'required|uuid',
                'status' => ['required', new Enum(TransactionStatus::class)],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid input data',
                'errors' => $e->errors(),
            ], 422);
        }

        $updated = $this->checkoutService->updateStatus($data['transaction_id'], $data['status']);

        if (! $updated) {
            return response()->json(['error' => 'Invalid transaction ID or Status Text.'], 404);
        }

        return response()->json(['success' => true]);
    }

}

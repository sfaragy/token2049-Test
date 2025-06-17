<?php

namespace Tests\Unit;

use App\Contracts\PaymentProviderInterface;
use App\DTOs\CheckoutRequestData;
use App\DTOs\CheckoutResponseData;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\CheckoutService;
use App\Services\Providers\PaymentProviderManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class CheckoutServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_checkout_successfully()
    {
        $requestData = new CheckoutRequestData(
            email: 'test@example.com',
            amount: 99.99
        );

        $fakeProvider = Mockery::mock(PaymentProviderInterface::class);
        $fakeProvider->shouldReceive('createPaymentSession')
            ->andReturn('https://checkout.url');

        $providerManager = Mockery::mock(PaymentProviderManager::class);
        $providerManager->shouldReceive('getProvider')
            ->with('coinbase')
            ->andReturn($fakeProvider);

        $service = new CheckoutService($providerManager);

        $response = $service->createCheckout($requestData);

        $this->assertInstanceOf(CheckoutResponseData::class, $response);
        $this->assertEquals('https://checkout.url', $response->checkout_url);

        $this->assertDatabaseHas('transactions', [
            'email' => 'test@example.com',
            'amount' => 99.99,
            'status' => TransactionStatus::PENDING->value,
        ]);
    }
}

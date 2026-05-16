<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receive_wallet(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated()
            ->assertJsonStructure(['user', 'wallet', 'token']);

        $this->assertDatabaseHas('wallets', [
            'user_id' => User::query()->where('email', 'test@example.com')->value('id'),
            'balance_cents' => 0,
        ]);
    }

    public function test_deposit_and_transfer_flow(): void
    {
        $alice = $this->registerUser('alice@test.com');
        $bob = $this->registerUser('bob@test.com');

        Sanctum::actingAs($alice);

        $this->postJson('/api/wallet/deposit', ['amount' => 100.00])
            ->assertCreated()
            ->assertJsonPath('balance_cents', 10000);

        $this->postJson('/api/wallet/transfer', [
            'to_user_id' => $bob->id,
            'amount' => 25.50,
        ])->assertCreated();

        $this->getJson('/api/wallet/balance')
            ->assertOk()
            ->assertJsonPath('balance_cents', 7450);

        Sanctum::actingAs($bob);

        $this->getJson('/api/wallet/balance')
            ->assertOk()
            ->assertJsonPath('balance_cents', 2550);
    }

    public function test_transfer_fails_with_insufficient_balance(): void
    {
        $alice = $this->registerUser('poor@test.com');
        $bob = $this->registerUser('rich@test.com');

        Sanctum::actingAs($alice);

        $this->postJson('/api/wallet/transfer', [
            'to_user_id' => $bob->id,
            'amount' => 10.00,
        ])->assertStatus(422)
            ->assertJsonPath('error', 'insufficient_balance');
    }

    public function test_transaction_can_be_reversed(): void
    {
        $alice = $this->registerUser('rev-alice@test.com');
        $bob = $this->registerUser('rev-bob@test.com');

        Sanctum::actingAs($alice);

        $this->postJson('/api/wallet/deposit', ['amount' => 50.00])->assertCreated();

        $transfer = $this->postJson('/api/wallet/transfer', [
            'to_user_id' => $bob->id,
            'amount' => 20.00,
        ])->assertCreated();

        $transactionId = $transfer->json('transaction_id');

        $this->postJson("/api/transactions/{$transactionId}/reverse")
            ->assertOk();

        $this->getJson('/api/wallet/balance')
            ->assertJsonPath('balance_cents', 5000);
    }

    public function test_idempotency_key_prevents_duplicate_deposit(): void
    {
        $user = $this->registerUser('idem@test.com');
        Sanctum::actingAs($user);

        $headers = ['Idempotency-Key' => 'deposit-unique-1'];

        $this->postJson('/api/wallet/deposit', ['amount' => 10.00], $headers)
            ->assertCreated();

        $this->postJson('/api/wallet/deposit', ['amount' => 10.00], $headers)
            ->assertCreated()
            ->assertJsonPath('balance_cents', 1000);
    }

    private function registerUser(string $email): User
    {
        $this->postJson('/api/register', [
            'name' => 'User',
            'email' => $email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        return User::query()->where('email', $email)->firstOrFail();
    }
}

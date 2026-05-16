<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_and_see_dashboard(): void
    {
        $user = User::factory()->create([
            'email' => 'web@test.com',
            'password' => bcrypt('password123'),
        ]);

        $user->wallet()->create(['balance_cents' => 5000, 'version' => 0]);

        $this->post('/login', [
            'email' => 'web@test.com',
            'password' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('R$ 50,00');
    }

    public function test_user_can_deposit_via_web(): void
    {
        $user = User::factory()->create();
        $user->wallet()->create(['balance_cents' => 0, 'version' => 0]);

        $this->actingAs($user)
            ->post('/wallet/deposit', ['amount' => 25.00])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(2500, $user->wallet->fresh()->balance_cents);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testDepositIncreasesBalance(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $response = $this->postJson(route('transaction.deposit'), [
            'user_id' => $user->id,
            'amount' => 50,
            'comment' => 'Test deposit'
        ]);

        $response->assertCreated()
            ->assertJson([
                'user_id' => $user->id,
                'amount' => 50,
                'comment' => 'Test deposit'
            ]);

        $this->assertEquals(150, $user->fresh()->balance);
    }

    public function testWithdrawDecreasesBalance(): void
    {
        $user = User::factory()->create(['balance' => 100]);

        $response = $this->postJson(route('transaction.withdraw'), [
            'user_id' => $user->id,
            'amount' => 30,
            'comment' => 'Test withdrawal'
        ]);

        $response->assertOk();
        $this->assertEquals(70, $user->fresh()->balance);
    }

    public function testWithdrawFailsWithInsufficientFunds(): void
    {
        $user = User::factory()->create(['balance' => 50]);

        $response = $this->postJson(route('transaction.withdraw'), [
            'user_id' => $user->id,
            'amount' => 100,
        ]);

        $response->assertConflict();
        $this->assertEquals(50, $user->fresh()->balance);
    }

    public function testTransferBetweenUsers(): void
    {
        $fromUser = User::factory()->create(['balance' => 100]);
        $toUser = User::factory()->create(['balance' => 50]);

        $response = $this->postJson(route('transaction.transfer'), [
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'amount' => 40,
            'comment' => 'Test transfer'
        ]);

        $response->assertOk();
        $this->assertEquals(60, $fromUser->fresh()->balance);
        $this->assertEquals(90, $toUser->fresh()->balance);
    }
}

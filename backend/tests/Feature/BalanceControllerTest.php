<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BalanceControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testBalanceShow()
    {
        $user = User::factory()->create();

        $response = $this->getJson(route('balance.show', ['user' => $user]))
            ->assertOk()
            ->assertJsonStructure([
                'user_id',
                'balance'
            ]);
    }
}

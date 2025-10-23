<?php

namespace App\Http\Controllers;

use App\Http\Resources\BalanceResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function show(User $user): BalanceResource
    {
        return new BalanceResource($user);
    }
}

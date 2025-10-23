<?php

namespace App\Http\Controllers;

use App\Http\Requests\FinanceRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Resources\AmountResource;
use App\Services\FinanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct(private readonly FinanceService $financeService) {}

    public function deposit(FinanceRequest $request): AmountResource
    {
        $data = $request->validated();

        $transaction = $this->financeService->deposit(
            $data['user_id'],
            $data['amount'],
            $data['comment']
        );

        return new AmountResource($transaction);
    }

    public function withdraw(FinanceRequest $request): JsonResponse
    {
        $data = $request->validated();
        try {
            $transaction = $this->financeService->withdraw(
                $data['user_id'],
                $data['amount'],
                $data['comment'] ?? null
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return response()->json(new AmountResource($transaction));
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $transfer = $this->financeService->transfer(
                $data['from_user_id'],
                $data['to_user_id'],
                $data['amount'],
                $data['comment']
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }

        return response()->json([
            'transfer_id' => $transfer->id,
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'amount' => $request->amount,
            'comment' => $request->comment
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TransactionTypes;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class FinanceService
{
    public function deposit(int $userId, float $amount, string $comment = null): Transaction
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            $user->balance += $amount;
            $user->save();

            return Transaction::query()->create([
                'user_id' => $userId,
                'type' => TransactionTypes::DEPOSIT->value,
                'amount' => $amount,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Списание средств
     */
    public function withdraw(int $userId, float $amount, string $comment = null): Transaction
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::query()->lockForUpdate()->findOrFail($userId);

            if (!$user->hasSufficientBalance($amount)) {
                throw new \Exception(
                    'Недостаточно средств',
                    Response::HTTP_CONFLICT
                );
            }

            $user->balance -= $amount;
            $user->save();

            return Transaction::query()->create([
                'user_id' => $userId,
                'type' => TransactionTypes::WITHDRAW,
                'amount' => $amount,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Перевод между пользователями
     */
    public function transfer(int $fromUserId, int $toUserId, float $amount, string $comment = null): Transfer
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            $users = User::query()->whereIn('id', [$fromUserId, $toUserId])
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $fromUser = $users->firstWhere('id', $fromUserId);
            $toUser = $users->firstWhere('id', $toUserId);

            if (!$fromUser->hasSufficientBalance($amount)) {
                throw ValidationException::withMessages([
                    'amount' => 'Недостаточно средств'
                ])->status(Response::HTTP_CONFLICT);
            }

            $fromUser->balance -= $amount;
            $toUser->balance += $amount;

            $fromUser->save();
            $toUser->save();

            $transfer = Transfer::query()->create([
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'amount' => $amount,
                'comment' => $comment,
            ]);

            Transaction::query()->create([
                'user_id' => $fromUserId,
                'type' => TransactionTypes::TRANSFER_OUT->value,
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $toUserId,
            ]);

            Transaction::query()->create([
                'user_id' => $toUserId,
                'type' => TransactionTypes::TRANSFER_IN->value,
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $fromUserId,
            ]);

            return $transfer;
        });
    }
}

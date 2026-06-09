<?php

namespace App\Services;

use App\Enums\AiContentType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AiCreditsService
{
    public function balance(User $user): int
    {
        return (int) $user->ai_credits;
    }

    public function canAfford(User $user, AiContentType $type): bool
    {
        return $this->balance($user) >= $type->creditCost();
    }

    public function deduct(User $user, AiContentType $type): void
    {
        $cost = $type->creditCost();

        if ($cost === 0) {
            return;
        }

        DB::transaction(function () use ($user, $cost) {
            $locked = User::where('id', $user->id)->lockForUpdate()->first();

            if ($locked->ai_credits < $cost) {
                throw new RuntimeException(__('ai_studio.insufficient_credits'));
            }

            $locked->decrement('ai_credits', $cost);
            $user->ai_credits = $locked->ai_credits;
        });
    }

    public function refund(User $user, AiContentType $type): void
    {
        $cost = $type->creditCost();

        if ($cost > 0) {
            $user->increment('ai_credits', $cost);
        }
    }

    public function topUp(User $user, int $amount): void
    {
        if ($amount < 1) {
            throw new RuntimeException(__('ai_studio.invalid_credit_amount'));
        }

        $user->increment('ai_credits', $amount);
    }
}

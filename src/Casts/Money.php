<?php

namespace Laraditz\Wallet\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Laraditz\Wallet\DTO\Money as MoneyDTO;
use Laraditz\Wallet\Models\Transaction;
use Laraditz\Wallet\Models\Wallet;
use Laraditz\Wallet\Models\WalletType;

class Money implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): MoneyDTO
    {
        return new MoneyDTO(
            $value,
            $this->getWalletType($model)
        );
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        if ($value instanceof MoneyDTO) {
            return $value->value;
        }

        return $value;
    }

    private function getWalletType(mixed $model)
    {
        $walletType = $model;

        if ($model instanceof Wallet) {
            $walletType = $model->walletType;
        } elseif ($model instanceof Transaction) {
            $walletType = $model->walletType;
        } elseif ($model instanceof WalletType) {
            $walletType = $model;
        }

        return $walletType;
    }
}

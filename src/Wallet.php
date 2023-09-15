<?php

namespace Laraditz\Wallet;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;
use Laraditz\Wallet\Models\TransactionBatch;
use Laraditz\Wallet\Models\Wallet as WalletModel;
use Laraditz\Wallet\Models\WalletType;
use LogicException;

class Wallet
{
    private array $txs = [];

    private ?string $type = null;

    private bool $immediate = false;

    private array $defaultTypes = [
        'deposit', 'withdraw', 'transfer',
    ];

    public function getTypes(): array
    {
        return config('wallet.tx_types') ? array_unique(array_merge($this->defaultTypes, explode(',', config('wallet.tx_types')))) : $this->defaultTypes;
    }

    public function createWalletType(array $data): WalletType
    {
        return WalletType::create($data);
    }

    public function type(string $type): self
    {
        throw_if(!in_array($type, $this->getTypes()), LogicException::class, __('Type :type is not supported.', ['type' => $type]));

        $this->type = $type;

        return $this;
    }

    public function immediate(bool $immediate): self
    {
        $this->immediate = $immediate;

        return $this;
    }

    public function in(WalletModel $wallet, int $amount, ?string $description = null)
    {
        $this->inOut(Direction::In, $wallet, $amount, $description);

        return $this;
    }

    public function out(WalletModel $wallet, int $amount, ?string $description = null)
    {
        $this->inOut(Direction::Out, $wallet, $amount, $description);

        return $this;
    }

    public function inOut(Direction $direction, WalletModel $wallet, int $amount, ?string $description = null)
    {
        throw_if(!$wallet->active()->count(), LogicException::class, __('Inactive wallet.'));
        throw_if(
            !$wallet->walletType?->active()->count(),
            LogicException::class,
            __('Wallet :name does not exist or cannot be used anymore.', ['name' => $wallet->walletType?->name ?? 'type'])
        );

        $this->txs[] = [
            'modelWallet' => $wallet,
            'direction' => $direction,
            'type' => $this->type,
            'amount' => $amount,
            'description' => $description ?? ucfirst($this->type),
        ];

        return $this;
    }

    public function makeTransaction(): TransactionBatch
    {
        throw_if(count($this->txs) <= 0, LogicException::class, __('No transactions to process.'));

        $tx = DB::transaction(function () {
            $tx = $this->addTxBatch();

            if ($tx) {
                $this->addTxs($tx);
            }

            return $tx;
        });

        if ($this->immediate === true && $tx) {
            $tx->markAsCompleted();
        }

        // reset
        $this->txs = [];

        return $tx;
    }

    private function addTxBatch(): TransactionBatch
    {
        throw_if(!$this->type, LogicException::class, __('No type was set.'));

        $amounts = collect($this->txs)->map(function ($item, $key) {
            return [
                'direction' => data_get($item, 'direction'),
                'currency_code' => data_get($item, 'modelWallet.walletType.currency_code'),
                'amount' => data_get($item, 'amount'),
            ];
        });

        return TransactionBatch::create([
            'type' => $this->type,
            'amounts' => $amounts->toArray(),
            'status' => TxStatus::Processing,

        ]);
    }

    private function addTxs(TransactionBatch $tx): void
    {
        foreach ($this->txs as $item) {
            throw_if(bccomp(data_get($item, 'amount'), '0') === 0 || bccomp(data_get($item, 'amount'), '0') === -1, LogicException::class, __('Amount must be larger than 0.'));

            if (data_get($item, 'direction') === Direction::Out) {
                $balance_after = bcsub($item['modelWallet']->available_amount?->value, data_get($item, 'amount'));

                throw_if(bccomp($item['modelWallet']->available_amount?->value, '0') === 0 || bccomp($item['modelWallet']->available_amount?->value, '0') === -1, LogicException::class, __('Not enough balance.'));
                throw_if(bccomp($balance_after, '0') === -1, LogicException::class, __('Not enough balance.'));
            }

            $lock_key = 'lock.wallet.' . data_get($item, 'modelWallet.id');
            $lock = Cache::lock($lock_key, 1);

            if ($lock->get()) {
                $item['modelWallet']->model->transactions()->create([
                    'batch_id' => $tx->id,
                    'wallet_id' => data_get($item, 'modelWallet.id'),
                    'wallet_type_id' => data_get($item, 'modelWallet.wallet_type_id'),
                    'type' => $tx->type,
                    'direction' => data_get($item, 'direction'),
                    'currency_code' => data_get($item, 'modelWallet.walletType.currency_code'),
                    'amount' => data_get($item, 'amount'),
                    'description' => data_get($item, 'description'),
                    'status' => TxStatus::Processing,
                ]);
            }

            $lock?->release();
        }
    }

    public function deposit(WalletModel $wallet, int $amount, ?string $description = null): TransactionBatch
    {
        return $this->in(wallet: $wallet, amount: $amount, description: $description)
            ->makeTransaction();
    }

    public function withdraw(WalletModel $wallet, int $amount, ?string $description = null): TransactionBatch
    {
        return $this->out(wallet: $wallet, amount: $amount, description: $description)
            ->makeTransaction();
    }

    public function transfer(WalletModel $fromWallet, WalletModel $toWallet, int $amount, ?string $description = null): TransactionBatch
    {
        return $this
            ->out(wallet: $fromWallet, amount: $amount, description: $description)
            ->in(wallet: $toWallet, amount: $amount, description: $description)
            ->makeTransaction();
    }

    public function generateRefNo(int $length = 16): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

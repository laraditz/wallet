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
    private $txs = [];

    private $type = null;

    private $immediate = false;

    private $default_types = [
        'deposit', 'withdraw', 'transfer',
    ];

    public function getTypes()
    {
        return config('wallet.tx_types') ? array_unique(array_merge($this->default_types, explode(',', config('wallet.tx_types')))) : $this->default_types;
    }

    public function createWalletType(array $data)
    {
        return WalletType::create($data);
    }

    public function type($type): self
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

    public function in($modelWallet, $amount, $description = null)
    {
        $this->inOut('In', $modelWallet, $amount, $description);

        return $this;
    }

    public function out($modelWallet, $amount, $description = null)
    {
        $this->inOut('Out', $modelWallet, $amount, $description);

        return $this;
    }

    public function inOut($direction, $modelWallet, $amount, $description = null)
    {
        $this->txs[] = [
            'modelWallet' => $modelWallet,
            'direction' => $direction,
            'type' => $this->type,
            'amount' => $amount,
            'description' => $description ?? ucfirst($this->type),
        ];

        return $this;
    }

    public function makeTransaction()
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

        return $tx;
    }

    private function addTxBatch()
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

    private function addTxs(TransactionBatch $tx)
    {
        foreach ($this->txs as $item) {
            throw_if(bccomp(data_get($item, 'amount'), '0') === 0 || bccomp(data_get($item, 'amount'), '0') === -1, LogicException::class, __('Amount must be larger than 0.'));

            if (data_get($item, 'direction') === 'Out') {
                $balance_after = bcsub($item['modelWallet']->available_amount, data_get($item, 'amount'));

                throw_if(bccomp($item['modelWallet']->available_amount, '0') === 0 || bccomp($item['modelWallet']->available_amount, '0') === -1, LogicException::class, __('Not enough balance.'));
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
                    'direction' => Direction::getValue(data_get($item, 'direction')),
                    'currency_code' => data_get($item, 'modelWallet.walletType.currency_code'),
                    'amount' => data_get($item, 'amount'),
                    'description' => data_get($item, 'description'),
                    'status' => TxStatus::Processing,
                ]);
            }

            optional($lock)->release();
        }
    }

    public function deposit(WalletModel $wallet, $amount)
    {
        return $this->in($wallet, $amount)
            ->makeTransaction();
    }

    public function withdraw(WalletModel $wallet, $amount)
    {
        return $this->out($wallet, $amount)
            ->makeTransaction();
    }

    public function transfer(WalletModel $fromWallet, WalletModel $toWallet, $amount)
    {
        return $this
            ->out($fromWallet, $amount)
            ->in($toWallet, $amount)
            ->makeTransaction();
    }

    public function generateRefNo(int $length = 16): string
    {
        return bin2hex(openssl_random_pseudo_bytes($length));
    }
}

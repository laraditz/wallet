<?php

namespace Laraditz\Wallet\Traits;

trait Transactable
{
    public function deposit($amount, string $type = 'deposit')
    {
        return app('wallet')->type($type)->deposit(wallet: $this, amount: $amount);
    }

    public function depositNow($amount, string $type = 'deposit')
    {
        return app('wallet')->type($type)->immediate(true)->deposit($this, $amount);
    }

    public function withdraw($amount, string $type = 'withdraw')
    {
        return app('wallet')->type($type)->withdraw($this, $amount);
    }

    public function withdrawNow($amount, string $type = 'withdraw')
    {
        return app('wallet')->type($type)->immediate(true)->withdraw($this, $amount);
    }

    public function transfer($toWallet, $amount, string $type = 'transfer')
    {
        return app('wallet')->type($type)->transfer($this, $toWallet, $amount);
    }

    public function transferNow($toWallet, $amount, string $type = 'transfer')
    {
        return app('wallet')->type($type)->immediate(true)->transfer($this, $toWallet, $amount);
    }
}

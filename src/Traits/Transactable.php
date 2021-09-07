<?php

namespace Laraditz\Wallet\Traits;

trait Transactable
{
    public function deposit($amount, $type = 'deposit')
    {
        return app('wallet')->type($type)->deposit($this, $amount);
    }

    public function depositNow($amount, $type = 'deposit')
    {
        return app('wallet')->type($type)->immediate(true)->deposit($this, $amount);
    }

    public function withdraw($amount, $type = 'withdraw')
    {
        return app('wallet')->type($type)->withdraw($this, $amount);
    }

    public function withdrawNow($amount, $type = 'withdraw')
    {
        return app('wallet')->type($type)->immediate(true)->withdraw($this, $amount);
    }

    public function transfer($toWallet, $amount, $type = 'transfer')
    {
        return app('wallet')->type($type)->transfer($this, $toWallet, $amount);
    }

    public function transferNow($toWallet, $amount, $type = 'transfer')
    {
        return app('wallet')->type($type)->immediate(true)->transfer($this, $toWallet, $amount);
    }
}

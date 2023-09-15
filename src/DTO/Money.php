<?php

namespace Laraditz\Wallet\DTO;

use Laraditz\Wallet\Models\WalletType;

class Money
{
    private string $code;

    private string $symbol;

    public function __construct(
        public int $value,
        private WalletType $walletType
    ) {
        $this->setCode($this->walletType->currency_code);
        $this->setSymbol($this->walletType->currency_symbol);
    }

    public function display(): string
    {
        return $this->getCode() . ' ' . $this->formattedAmount();
    }

    public function formattedAmount(): string
    {
        return (string) number_format($this->getAmount(), $this->getDefaultFractionDigits());
    }

    public function getAmount()
    {
        return $this->value / (10 ** $this->getDefaultFractionDigits());
    }

    public function getMinorAmount(): int
    {
        return $this->value;
    }

    public function getDefaultFractionDigits(): int
    {
        return $this->walletType->default_scale;
    }

    private function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    private function setSymbol(string $symbol): void
    {
        $this->symbol = $symbol;
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }
}

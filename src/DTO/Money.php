<?php

namespace Laraditz\Wallet\DTO;

use Laraditz\Wallet\Enums\Placement;
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

    public function display(bool $useSymbol = false): string
    {
        $code = $this->getCode();

        if ($useSymbol === true) {
            $code = $this->getSymbol();
        }

        if ($this->getSymbolPlacement() === Placement::Left) {
            return $code . ' ' . $this->formattedAmount();
        }

        return $this->formattedAmount() . ' ' . $code;
    }

    public function formattedAmount(): string
    {
        return (string) number_format($this->getAmount(), $this->getDefaultFractionDigits(), $this->getDecimalSeparator(), $this->getThousandSeparator());
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

    public function getDecimalSeparator(): string
    {
        return $this->walletType->decimal_separator;
    }

    public function getThousandSeparator(): string
    {
        return $this->walletType->thousand_separator;
    }

    public function getCodePlacement(): Placement
    {
        return $this->walletType->code_placement;
    }

    public function getSymbolPlacement(): Placement
    {
        return $this->walletType->symbol_placement;
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

<?php

namespace Laraditz\Wallet\Traits;

use Laraditz\Wallet\Models\Wallet;
use Laraditz\Wallet\Models\WalletType;
use Laraditz\Wallet\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Builder;

trait HasWallets
{
    private $_wallets = [];

    /**WalletType
     * @var bool
     */
    private $_loadedWallets;

    public $wallet = null;

    private $tx_id = null;

    private $txs = [];

    /**
     * method of obtaining all wallets.
     *
     * @return MorphMany
     */
    public function wallets()
    {
        return $this->morphMany(Wallet::class, 'model');
    }

    /**
     * method of obtaining all wallets.
     *
     * @return MorphMany
     */
    public function transactions()
    {
        return $this->morphMany(Transaction::class, 'model');
    }

    public function getWallet(string $slug = null): ?Wallet
    {
        $slug = $slug ?? config('wallet.wallet_type.slug');

        try {
            return $this->getWalletOrFail($slug);
        } catch (ModelNotFoundException $modelNotFoundException) {

            $this->_wallets[$slug] = $this->createWallet($slug);

            return $this->_wallets[$slug];
        }
    }

    public function getWalletOrFail(string $slug): Wallet
    {
        if (!$this->_loadedWallets && $this->relationLoaded('wallets')) {
            $this->_loadedWallets = true;
            $wallets = $this->getRelation('wallets');
            foreach ($wallets as $wallet) {
                $this->_wallets[$wallet->slug] = $wallet;
            }
        }

        if (!array_key_exists($slug, $this->_wallets)) {
            $this->_wallets[$slug] = $this->wallets()
                ->whereHas('walletType', function (Builder $query) use ($slug) {
                    $query->where('slug', 'like', $slug);
                })->firstOrFail();
        }

        return $this->_wallets[$slug];
    }

    public function createWallet($slug = 'default')
    {
        $walletType = WalletType::where('slug', $slug)->firstOrFail();

        return $this->wallets()->updateOrCreate([
            'wallet_type_id' => $walletType->id,
        ], []);
    }
}

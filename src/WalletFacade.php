<?php

namespace Laraditz\Wallet;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Laraditz\Wallet\Skeleton\SkeletonClass
 */
class WalletFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'wallet';
    }
}

<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Laraditz\Wallet\Casts\Money;
use Laraditz\Wallet\Enums\ActiveStatus;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;
use Laraditz\Wallet\Traits\Transactable;

class Wallet extends Model
{
    use Transactable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'address',
        'wallet_type_id',
        'model_type',
        'model_id',
        'status',
        'description',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'json',
        'status' => ActiveStatus::class,
        'available_amount' => Money::class,
        'balance_amount' => Money::class,
        'accumulated_amount' => Money::class,
        'used_amount' => Money::class,
    ];

    public function getTable()
    {
        return config('wallet.table_names.wallets', parent::getTable());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->address ??= (string) Str::ulid();
            $model->status ??= ActiveStatus::Active;
        });
    }

    /**
     * Get the parent owner.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function walletType(): BelongsTo
    {
        return $this->belongsTo(WalletType::class);
    }

    public function scopeBySlug($query, $slug): void
    {
        $query->whereHas('walletType', function (Builder $query) use ($slug) {
            $query->where('slug', 'like', $slug);
        });
    }

    public function scopeActive($query): void
    {
        $query->where('status', ActiveStatus::Active);
    }

    public function updateBalance($transaction, $field = 'balance_amount')
    {
        $new_balance = 0;
        $accumulated_amount = $this->accumulated_amount?->value;
        $used_amount = $this->used_amount?->value;

        if ($transaction->direction === Direction::In) {
            $new_balance = bcadd($this->$field?->value, $transaction->amount?->value);
            if ($field === 'balance_amount') {
                $this->available_amount = bcadd($this->available_amount?->value, $transaction->amount?->value);
                $this->accumulated_amount = bcadd($accumulated_amount, $transaction->amount?->value);
            }
        } elseif ($transaction->direction === Direction::Out) {
            if ($field === 'available_amount') {
                if ($transaction->status === TxStatus::Processing) {
                    $new_balance = bcsub($this->$field?->value, $transaction->amount?->value);
                } elseif ($transaction->status === TxStatus::Failed) {
                    $new_balance = bcadd($this->$field?->value, $transaction->amount?->value);
                }
            } else {
                $new_balance = bcsub($this->$field?->value, $transaction->amount?->value);
                $this->used_amount = bcadd($used_amount, $transaction->amount?->value);
            }
        }

        $this->$field = $new_balance;

        return $this->save() ? $this->refresh() : null;
    }
}

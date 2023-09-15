<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;
use Laraditz\Wallet\Casts\Money;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;

class Transaction extends Model
{
    protected $fillable = [
        'batch_id', 'ref_no', 'wallet_id', 'wallet_type_id', 'type',
        'model_type', 'model_id', 'direction', 'currency_code', 'amount',
        'amount_before', 'amount_after', 'description', 'status', 'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'direction' => Direction::class,
        'status' => TxStatus::class,
        'metadata' => 'json',
        'amount' => Money::class,
        'amount_before' => Money::class,
        'amount_after' => Money::class,
    ];

    public function getTable()
    {
        return config('wallet.table_names.transactions', parent::getTable());
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->batch_id = $model->batch_id ?? (string) Str::ulid();
            $model->ref_no = $model->ref_no ?? app('wallet')->generateRefNo();
        });

        static::created(function ($model) {
            if (
                data_get($model, 'batch.status') === TxStatus::Processing
                && $model->direction === Direction::Out
            ) {
                $model->wallet?->updateBalance($model, 'available_amount');
            }
        });
    }

    /**
     * Get the parent model.
     */
    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function walletType(): BelongsTo
    {
        return $this->belongsTo(WalletType::class);
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }

    public function batch(): BelongsTo
    {
        return $this->belongsTo(TransactionBatch::class, 'batch_id');
    }

    public function scopeIn($query): Builder
    {
        return $query->where('direction', Direction::In);
    }

    public function scopeOut($query): Builder
    {
        return $query->where('direction', Direction::Out);
    }

    public function scopeByType($query, $type): Builder
    {
        return $query->where('type', $type);
    }

    public function scopeByTypes($query, array $types): Builder
    {
        return $query->whereIn('type', $types);
    }

    public function updateBeforeAfterAmount()
    {
        if ($this->wallet) {
            if ($this->direction === Direction::In) {
                $amounts = [
                    'amount_before' => $this->wallet->balance_amount?->value,
                    'amount_after' => bcadd($this->wallet->balance_amount?->value, $this->amount?->value),
                ];
            } elseif ($this->direction === Direction::Out) {
                $amounts = [
                    'amount_before' => $this->wallet->balance_amount?->value,
                    'amount_after' => bcsub($this->wallet->balance_amount?->value, $this->amount?->value),
                ];
            }

            $this->amount_before = $amounts['amount_before'];
            $this->amount_after = $amounts['amount_after'];

            return $this->save() ? $this->refresh() : null;
        }

        return null;
    }

    public function markAsCompleted()
    {
        $this->batch->markAsCompleted();
    }

    public function markAsFailed()
    {
        $this->batch->markAsFailed();
    }
}

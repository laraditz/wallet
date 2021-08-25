<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id', 'ref_no', 'wallet_id', 'wallet_type_id', 'type',
        'model_type', 'model_id', 'direction', 'currency_code', 'amount',
        'amount_before', 'amount_after', 'description', 'status', 'data'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'data' => 'json',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->batch_id = $model->batch_id ?? (string) Str::orderedUuid();
            $model->ref_no = $model->ref_no ?? app('wallet')->generateRefNo();
        });

        static::created(function ($model) {
            if (
                object_get($model, 'batch.status') === TxStatus::Processing
                && $model->direction === Direction::Out
            ) {
                $model->wallet->updateBalance($model, 'available_amount');
            }
        });
    }

    /**
     * Get the parent model.
     */
    public function model()
    {
        return $this->morphTo();
    }

    public function walletType()
    {
        return $this->belongsTo(WalletType::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function batch()
    {
        return $this->belongsTo(TransactionBatch::class, 'batch_id');
    }

    public function scopeOwner($query)
    {
        return $query->whereUserId(auth()->id());
    }

    public function scopeIn($query)
    {
        return $query->where('direction', Direction::In);
    }

    public function scopeOut($query)
    {
        return $query->where('direction', Direction::Out);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByTypes($query, array $types)
    {
        return $query->whereIn('type', $types);
    }

    public function updateBeforeAfterAmount()
    {
        if ($this->wallet) {
            if ($this->direction === Direction::In) {
                $amounts = [
                    'amount_before' => $this->wallet->balance_amount,
                    'amount_after' => bcadd($this->wallet->balance_amount, $this->amount),
                ];
            } elseif ($this->direction === Direction::Out) {
                $amounts = [
                    'amount_before' => $this->wallet->balance_amount,
                    'amount_after' => bcsub($this->wallet->balance_amount, $this->amount),
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

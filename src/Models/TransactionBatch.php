<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;

class TransactionBatch extends Model
{
    protected $fillable = ['id', 'type', 'amounts', 'status', 'status_description', 'remark', 'data'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'amounts' => 'json',
        'data' => 'json',
    ];

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'batch_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getKeyName()} = $model->id ?? (string) Str::orderedUuid();
        });

        static::updated(function ($model) {
            // update transactions status
            if (
                $model->getOriginal('status') !== $model->status
            ) {
                if ($model->transactions) {
                    foreach ($model->transactions as $transaction) {
                        $transaction->status = $model->status;
                        $transaction->save();
                    }
                }
            }

            if (
                $model->getOriginal('status') == TxStatus::Processing &&
                $model->status === TxStatus::Completed
            ) {
                if ($model->transactions) {
                    foreach ($model->transactions as $transaction) {
                        DB::transaction(function () use ($transaction) {
                            if ($transaction->updateBeforeAfterAmount()) {
                                $transaction->wallet->updateBalance($transaction);
                            }
                        });
                    }
                }
            } elseif (
                $model->getOriginal('status') == TxStatus::Processing &&
                $model->status === TxStatus::Failed
            ) {
                // Refund deducted available balance
                if ($model->transactions) {
                    foreach ($model->transactions as $transaction) {
                        if ($transaction->direction === Direction::Out) {
                            $transaction->wallet->updateBalance($transaction, 'available_amount');
                        }
                    }
                }
            }
        });
    }

    public function markAsCompleted()
    {
        DB::transaction(function () {
            $this->status = TxStatus::Completed;
            $this->save();
        });
    }

    public function markAsFailed()
    {
        DB::transaction(function () {
            $this->status = TxStatus::Failed;
            $this->save();
        });
    }
}

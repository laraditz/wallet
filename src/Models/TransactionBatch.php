<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use Laraditz\Wallet\Enums\Direction;
use Laraditz\Wallet\Enums\TxStatus;

class TransactionBatch extends Model
{
    use HasUlids;

    protected $fillable = ['id', 'type', 'amounts', 'status', 'status_description', 'remark', 'metadata'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => TxStatus::class,
        'amounts' => 'json',
        'metadata' => 'json',
    ];

    public function getTable()
    {
        return config('wallet.table_names.transaction_batches', parent::getTable());
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'batch_id');
    }

    protected static function boot()
    {
        parent::boot();

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

<?php

namespace Laraditz\Wallet\Models;

use Laraditz\Wallet\Enums\TxStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laraditz\Wallet\Traits\Transactable;
use Laraditz\Wallet\Enums\Direction;

class Wallet extends Model
{
    use HasFactory, Transactable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'wallet_type_id',
        'model_type',
        'model_id',
    ];

    /**
     * Get the parent owner.
     */
    public function model()
    {
        return $this->morphTo();
    }

    public function walletType()
    {
        return $this->belongsTo(WalletType::class);
    }

    public function scopeOwner($query)
    {
        return $query->whereUserId(auth()->id());
    }

    public function scopeBySlug($query, $slug)
    {
        return $query->whereHas('walletType', function (Builder $query) use ($slug) {
            $query->where('slug', 'like', $slug);
        });
    }

    public function updateBalance($transaction, $field = 'balance_amount')
    {
        $new_balance = 0;
        $accumulated_amount = $this->accumulated_amount;
        $used_amount = $this->used_amount;

        if ($transaction->direction === Direction::In) {
            $new_balance = bcadd($this->$field, $transaction->amount);
            if ($field === 'balance_amount') {
                $this->available_amount = bcadd($this->available_amount, $transaction->amount);
                $this->accumulated_amount = bcadd($accumulated_amount, $transaction->amount);
            }
        } elseif ($transaction->direction === Direction::Out) {
            if ($field === 'available_amount') {

                if ($transaction->status === TxStatus::Processing) {
                    $new_balance = bcsub($this->$field, $transaction->amount);
                } elseif ($transaction->status === TxStatus::Failed) {
                    $new_balance = bcadd($this->$field, $transaction->amount);
                }
            } else {
                $new_balance = bcsub($this->$field, $transaction->amount);
                $this->used_amount = bcadd($used_amount, $transaction->amount);
            }
        }

        $this->$field = $new_balance;

        return $this->save() ? $this->refresh() : null;
    }
}

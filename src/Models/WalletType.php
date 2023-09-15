<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laraditz\Wallet\Enums\ActiveStatus;

class WalletType extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'currency_code',
        'currency_symbol',
        'default_scale',
        'placement',
        'status',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'status' => ActiveStatus::class,
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('wallet.table_names.wallet_types', parent::getTable());
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (self $model) {
            $model->slug = Str::of($model->name)->slug('-');
            $model->status ??= ActiveStatus::Active;
        });
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }

    public function scopeActive($query): void
    {
        $query->where('status', ActiveStatus::Active)
            ->canStartUse()
            ->hasNotExpired();
    }

    public function scopeCanStartUse($query): void
    {
        if ($this->start_at) {
            $query->where('start_at', '<=', now());
        }
    }

    public function scopeHasNotExpired($query): void
    {
        if ($this->end_at) {
            $query->where('end_at', '>', now());
        }
    }
}

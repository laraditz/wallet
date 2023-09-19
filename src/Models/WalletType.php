<?php

namespace Laraditz\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laraditz\Wallet\Enums\ActiveStatus;
use Laraditz\Wallet\Enums\Placement;

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
        'code_placement',
        'symbol_placement',
        'default_scale',
        'decimal_separator',
        'thousand_separator',
        'status',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'code_placement' => Placement::class,
        'symbol_placement' => Placement::class,
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
            $model->code_placement ??= Placement::Left;
            $model->symbol_placement ??= Placement::Left;
            $model->decimal_separator ??= config('wallet.decimal_separator');
            $model->thousand_separator ??= config('wallet.thousand_separator');
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

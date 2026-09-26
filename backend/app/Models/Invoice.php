<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = ['invoice_number', 'customer_id', 'user_id', 'status', 'issued_at'];

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $user->isAdmin()
            ? $query
            : $query->where($this->qualifyColumn('user_id'), $user->getKey());
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'total' => 'decimal:2',
            'issued_at' => 'datetime',
        ];
    }


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }


    public function returns(): HasMany
    {
        return $this->hasMany(SalesReturn::class, 'invoice_id');
    }
}

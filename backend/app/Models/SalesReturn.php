<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class SalesReturn extends Model
{
    protected $fillable = ['return_number', 'invoice_id', 'returned_at'];
    protected $table = 'returns';

    protected function casts(): array
    {
        return [
            'returned_at' => 'datetime',
        ];
    }


    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }


    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }
}

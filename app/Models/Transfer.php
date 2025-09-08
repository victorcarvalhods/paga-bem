<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transfer extends Model
{
    /** @use HasFactory<\Database\Factories\TransferFactory> */
    use HasFactory;

    protected $fillable = [
        'value',
        'payer_id',
        'payee_id',
    ];

    /**
     * Get the payer wallet.
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function payer(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payer_id');
    }

    /**
     * Get the payee wallet.
     *
     * @return BelongsTo<Wallet, $this>
     */
    public function payee(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'payee_id');
    }
}
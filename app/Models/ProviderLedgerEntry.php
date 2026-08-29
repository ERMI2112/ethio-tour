<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Immutable provider financial ledger entry.
 *
 * Accounting convention (the only one in the system):
 * - `earning`    → amount is +gross payment value
 * - `commission` → amount is −platform commission (negative)
 *
 * A payable's net earnings are ALWAYS derivable as SUM(amount) grouped by
 * payable. No current_balance column exists anywhere, so balances can never
 * drift from the underlying financial events.
 *
 * Append-only: no updated_at, no edit/delete paths in the application.
 */
class ProviderLedgerEntry extends Model
{
    public const TYPE_EARNING = 'earning';

    public const TYPE_COMMISSION = 'commission';

    public const TYPES = [self::TYPE_EARNING, self::TYPE_COMMISSION];

    protected $primaryKey = 'ledger_entry_id';

    public $timestamps = false;

    protected $fillable = [
        'payable_type', 'payable_id', 'booking_id', 'payment_id',
        'entry_type', 'amount', 'currency', 'description', 'created_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2', // string cast — never float
            'created_at' => 'datetime',
        ];
    }

    public function payable()
    {
        return $this->morphTo();
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id', 'booking_id');
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'payment_id', 'payment_id');
    }
}

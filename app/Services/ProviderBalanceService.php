<?php

namespace App\Services;

use App\Models\ProviderLedgerEntry;
use App\Support\Money;
use Illuminate\Database\Eloquent\Model;

/**
 * The single reader of provider/guide financial state.
 *
 * All totals are derived from persisted ledger entries — never recomputed
 * from bookings or payments in controllers — so the financial story a
 * provider sees is exactly the financial history recorded.
 *
 * These are deliberately NOT settlement figures: nothing here says what has
 * been paid out, is refundable, or is available for withdrawal. Those
 * concepts belong to future settlement tasks built on top of this ledger.
 */
class ProviderBalanceService
{
    /**
     * @return array{gross_earnings: string, commission_deductions: string, net_earnings: string, currency: string}
     */
    public function totalsFor(Model $payable): array
    {
        $entries = ProviderLedgerEntry::query()
            ->where('payable_type', $payable->getMorphClass())
            ->where('payable_id', $payable->getKey())
            ->get(['entry_type', 'amount', 'currency']);

        $grossMinor = 0;
        $commissionMinor = 0;

        foreach ($entries as $entry) {
            if ($entry->entry_type === ProviderLedgerEntry::TYPE_EARNING) {
                $grossMinor += Money::toMinor((string) $entry->amount);
            } elseif ($entry->entry_type === ProviderLedgerEntry::TYPE_COMMISSION) {
                $commissionMinor += Money::toMinor((string) $entry->amount); // stored negative
            }
        }

        return [
            'gross_earnings' => Money::fromMinor($grossMinor),
            'commission_deductions' => Money::fromMinor(abs($commissionMinor)),
            'net_earnings' => Money::fromMinor($grossMinor + $commissionMinor),
            'currency' => $entries->first()?->currency ?? 'ETB',
        ];
    }
}

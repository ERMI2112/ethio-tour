<?php

namespace App\Http\Controllers;

use App\Models\ProviderLedgerEntry;
use App\Services\ProviderBalanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only financial summary for the authenticated provider or guide.
 *
 * The payable is resolved from the session user — never from request input —
 * so IDs cannot be manipulated to view another provider's ledger.
 */
class ProviderEarningsController extends Controller
{
    public function __invoke(Request $request, ProviderBalanceService $balances): View
    {
        $payable = $request->user()->serviceProvider ?? $request->user()->tourGuide;

        abort_if(! $payable, 403);

        $entries = ProviderLedgerEntry::query()
            ->where('payable_type', $payable->getMorphClass())
            ->where('payable_id', $payable->getKey())
            ->latest('created_at')
            ->paginate(15);

        return view('provider.earnings', [
            'payable' => $payable,
            'totals' => $balances->totalsFor($payable),
            'entries' => $entries,
        ]);
    }
}

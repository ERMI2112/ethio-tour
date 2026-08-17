<?php

namespace App\Http\Controllers;

use App\Http\Requests\BureauVerificationDecisionRequest;
use App\Models\TourGuide;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BureauGuideVerificationController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $guides = TourGuide::with('user')
            ->when(in_array($status, ['pending', 'verified', 'rejected'], true), fn ($q) => $q->where('verification_status', $status))
            ->orderBy('verification_status')->orderBy('guide_id')->get();

        return view('bureau.guides.index', compact('guides', 'status'));
    }

    public function show(TourGuide $tourGuide): View
    {
        $tourGuide->load('user');

        return view('bureau.guides.show', ['guide' => $tourGuide]);
    }

    public function decide(BureauVerificationDecisionRequest $request, TourGuide $tourGuide, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        if ($tourGuide->verification_status !== 'pending') {
            return back()->withErrors(['decision' => 'Only pending guides can be reviewed.']);
        }
        $data = $request->validated();
        $tourGuide->forceFill([
            'verification_status' => $data['decision'] === 'approve' ? 'verified' : 'rejected',
            'verification_notes' => $data['verification_notes'] ?? null,
        ])->save();
        $audit->record($request->user(), 'guide_verification_decided', TourGuide::class, $tourGuide->guide_id, [
            'decision' => $data['decision'],
            'verification_notes' => $data['verification_notes'] ?? null,
        ]);

        $notifications->createForUser($tourGuide->user, $data['decision'] === 'approve' ? 'guide_verification' : 'guide_verification', 'Tour guide verification decision', $data['decision'] === 'approve' ? 'Your tour guide profile has been verified by the Tourism Bureau.' : 'Your tour guide profile was rejected by the Tourism Bureau.');

        return to_route('bureau.guides.show', $tourGuide)->with('success', 'Guide verification decision saved.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminGuideApprovalDecisionRequest;
use App\Models\TourGuide;
use App\Models\VerificationDocument;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminGuideApprovalController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->trim()->value();
        $status = in_array($status, ['pending', 'approved', 'rejected'], true) ? $status : 'pending';
        $guides = TourGuide::with('user')
            ->where('verification_status', 'verified')
            ->where('admin_approval_status', $status)
            ->orderBy('guide_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.guides.index', compact('guides'));
    }

    public function downloadDocument(TourGuide $tourGuide, VerificationDocument $verificationDocument): Response
    {
        abort_unless($verificationDocument->documentable_type === TourGuide::class
            && (int) $verificationDocument->documentable_id === (int) $tourGuide->guide_id, 404);

        return VerificationDocumentController::download($verificationDocument);
    }

    public function show(TourGuide $tourGuide): View
    {
        $tourGuide->load(['user', 'verificationDocuments.reviewer']);

        return view('admin.guides.show', ['guide' => $tourGuide]);
    }

    public function decide(AdminGuideApprovalDecisionRequest $request, TourGuide $tourGuide, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        abort_unless($tourGuide->verification_status === 'verified', 422, 'Only Bureau-verified guides can receive final approval.');
        abort_unless($tourGuide->admin_approval_status === 'pending', 422, 'This guide has already received a final approval decision.');

        $data = $request->validated();
        if ($data['decision'] === 'approve') {
            $required = ['license', 'identity'];
            $approved = $tourGuide->verificationDocuments()
                ->where('status', 'approved')
                ->whereIn('document_type', $required)
                ->pluck('document_type')
                ->unique()
                ->all();
            abort_unless(count(array_intersect($required, $approved)) === count($required), 422, 'License and identity documents must both be approved before final guide approval.');
        }

        $tourGuide->forceFill([
            'admin_approval_status' => $data['decision'] === 'approve' ? 'approved' : 'rejected',
            'admin_approval_notes' => $data['approval_notes'] ?? null,
            'admin_approved_at' => now(),
            'admin_approved_by' => $request->user()->user_id,
        ])->save();

        $audit->record($request->user(), 'guide_final_approval_decided', TourGuide::class, $tourGuide->guide_id, [
            'decision' => $data['decision'],
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);
        $notifications->createForUserAndAdministrators($tourGuide->user, 'guide_final_approval', 'Final guide approval decision', $data['decision'] === 'approve' ? 'Your guide profile received final Administrator approval.' : 'Your guide profile was rejected at final Administrator approval.', $request->user()->user_id);

        return to_route('admin.guides.show', $tourGuide)->with('success', 'Final guide approval decision saved.');
    }
}

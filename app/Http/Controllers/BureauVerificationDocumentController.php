<?php

namespace App\Http\Controllers;

use App\Http\Requests\BureauVerificationDocumentDecisionRequest;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\VerificationDocument;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class BureauVerificationDocumentController extends Controller
{
    public function decideForGuide(BureauVerificationDocumentDecisionRequest $request, TourGuide $tourGuide, VerificationDocument $verificationDocument, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        return $this->decide($request, $tourGuide, $verificationDocument, $audit, $notifications);
    }

    public function decideForProvider(BureauVerificationDocumentDecisionRequest $request, ServiceProvider $serviceProvider, VerificationDocument $verificationDocument, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        return $this->decide($request, $serviceProvider, $verificationDocument, $audit, $notifications);
    }

    public function downloadForGuide(TourGuide $tourGuide, VerificationDocument $verificationDocument): Response
    {
        $this->assertSubject($tourGuide, $verificationDocument);

        return VerificationDocumentController::download($verificationDocument);
    }

    public function downloadForProvider(ServiceProvider $serviceProvider, VerificationDocument $verificationDocument): Response
    {
        $this->assertSubject($serviceProvider, $verificationDocument);

        return VerificationDocumentController::download($verificationDocument);
    }

    private function decide(BureauVerificationDocumentDecisionRequest $request, TourGuide|ServiceProvider $subject, VerificationDocument $document, AuditService $audit, NotificationService $notifications): RedirectResponse
    {
        $this->assertSubject($subject, $document);
        $data = $request->validated();

        $document->forceFill([
            'status' => $data['decision'],
            'reviewed_by' => $request->user()->user_id,
            'reviewed_at' => now(),
            'review_notes' => $data['review_notes'] ?? null,
        ])->save();

        $audit->record($request->user(), 'verification_document_decided', $document->documentable_type, $document->documentable_id, [
            'document_type' => $document->document_type,
            'decision' => $data['decision'],
        ]);
        $notifications->createForUserAndAdministrators($subject->user, 'verification_document', 'Verification document reviewed', 'A Bureau officer marked your '.$document->document_type.' document as '.$data['decision'].'.', $request->user()->user_id);

        return back()->with('success', 'Verification document decision saved.');
    }

    private function assertSubject(TourGuide|ServiceProvider $subject, VerificationDocument $document): void
    {
        abort_unless($document->documentable_type === $subject::class && (int) $document->documentable_id === (int) $subject->getKey(), 404);
        abort_unless(Storage::disk('local')->exists($document->path), 404);
    }
}

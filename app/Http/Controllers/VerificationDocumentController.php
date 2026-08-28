<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationDocumentUploadRequest;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\VerificationDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class VerificationDocumentController extends Controller
{
    public function storeGuide(VerificationDocumentUploadRequest $request): RedirectResponse
    {
        abort_unless($request->user()->tourGuide, 403);

        $this->store($request, $request->user()->tourGuide, ['license', 'identity', 'other']);

        return back()->with('success', 'Verification document uploaded for Bureau review.');
    }

    public function storeProvider(VerificationDocumentUploadRequest $request): RedirectResponse
    {
        abort_unless($request->user()->serviceProvider, 403);

        $this->store($request, $request->user()->serviceProvider, ['tin', 'trade_license', 'permit', 'identity', 'other']);

        return back()->with('success', 'Verification document uploaded for Bureau review.');
    }

    public function downloadOwn(Request $request, VerificationDocument $verificationDocument): Response
    {
        abort_unless($verificationDocument->documentable?->user_id === $request->user()->user_id, 403);

        return $this->download($verificationDocument);
    }

    private function store(VerificationDocumentUploadRequest $request, TourGuide|ServiceProvider $subject, array $allowedTypes): VerificationDocument
    {
        $data = $request->validated();
        abort_unless(in_array($data['document_type'], $allowedTypes, true), 422, 'That document type is not valid for this application.');

        $file = $request->file('document');
        $path = $file->store('verification-documents', 'local');

        return $subject->verificationDocuments()->create([
            'uploaded_by' => $request->user()->user_id,
            'document_type' => $data['document_type'],
            'original_name' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getMimeType(),
            'size_bytes' => $file->getSize(),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'status' => VerificationDocument::STATUSES[0],
        ]);
    }

    public static function download(VerificationDocument $document): Response
    {
        abort_unless(Storage::disk('local')->exists($document->path), 404);
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($document->original_name)) ?: 'verification-document';

        return response(Storage::disk('local')->get($document->path), 200, [
            'Content-Type' => $document->mime_type,
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

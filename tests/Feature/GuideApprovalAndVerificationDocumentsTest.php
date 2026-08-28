<?php

namespace Tests\Feature;

use App\Models\Administrator;
use App\Models\ServiceProvider;
use App\Models\TourGuide;
use App\Models\TourismBureauOfficer;
use App\Models\User;
use App\Models\VerificationDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GuideApprovalAndVerificationDocumentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_guides_without_explicit_administrator_decision_are_not_public(): void
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'license_number' => 'LEGACY-LICENSE-001',
            'expertise' => 'Legacy guide',
            'availability_status' => 'available',
            'verification_status' => 'verified',
        ]);

        $guide->forceFill(['verification_status' => 'verified'])->save();

        $this->assertSame('pending', $guide->admin_approval_status);
        $this->assertFalse($guide->fresh()->isPubliclyApproved());
        $this->get(route('tour-guides.index'))->assertDontSee('Legacy guide');
    }

    public function test_final_approval_states_control_public_guide_visibility(): void
    {
        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Visibility Guide',
            'license_number' => 'VISIBILITY-LICENSE-001',
            'expertise' => 'Visibility tours',
            'availability_status' => 'available',
            'verification_status' => 'verified',
            'admin_approval_status' => 'pending',
        ]);
        $guide->forceFill(['verification_status' => 'verified'])->save();

        $this->get(route('tour-guides.index'))->assertDontSee('Visibility Guide');
        $guide->forceFill(['admin_approval_status' => 'rejected'])->save();
        $this->get(route('tour-guides.index'))->assertDontSee('Visibility Guide');
        $guide->forceFill(['admin_approval_status' => 'approved', 'admin_approved_at' => now(), 'admin_approved_by' => User::factory()->create(['role' => 'administrator'])->user_id])->save();
        $this->get(route('tour-guides.index'))->assertSee('Visibility Guide');
    }

    public function test_bureau_verified_guide_requires_approved_documents_before_final_admin_approval(): void
    {
        Storage::fake('local');

        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'full_name' => 'Test Heritage Guide',
            'license_number' => 'TEST-LICENSE-001',
            'expertise' => 'Gondar heritage',
            'availability_status' => 'available',
            'verification_status' => 'pending',
            'admin_approval_status' => 'pending',
        ]);
        $bureau = User::factory()->create(['role' => 'tourism_bureau_officer']);
        TourismBureauOfficer::create(['user_id' => $bureau->user_id]);
        $administrator = User::factory()->create(['role' => 'administrator']);
        Administrator::create(['user_id' => $administrator->user_id]);

        $this->get(route('tour-guides.index'))->assertDontSee('Test Heritage Guide');

        $this->actingAs($bureau)
            ->patch(route('bureau.guides.decide', $guide), ['decision' => 'approve'])
            ->assertRedirect();
        $guide->refresh();
        $this->assertSame('verified', $guide->verification_status);
        $this->assertSame('pending', $guide->admin_approval_status);

        $this->actingAs($administrator)
            ->patch(route('admin.guides.decide', $guide), ['decision' => 'approve'])
            ->assertStatus(422);

        foreach ([
            ['type' => 'license', 'name' => 'license.pdf'],
            ['type' => 'identity', 'name' => 'identity.pdf'],
        ] as $upload) {
            $this->actingAs($guideUser)
                ->post(route('tour-guide.verification-documents.store'), [
                    'document_type' => $upload['type'],
                    'document' => UploadedFile::fake()->create($upload['name'], 100, 'application/pdf'),
                ])
                ->assertRedirect();
        }

        $documents = $guide->verificationDocuments()->get();
        $this->assertCount(2, $documents);
        $documents->each(fn (VerificationDocument $document) => Storage::disk('local')->assertExists($document->path));

        foreach ($documents as $document) {
            $this->actingAs($bureau)
                ->patch(route('bureau.guides.documents.decide', [$guide, $document]), [
                    'decision' => 'approved',
                ])
                ->assertRedirect();
        }

        $this->actingAs($administrator)
            ->get(route('admin.guides.show', $guide))
            ->assertOk()
            ->assertSee('license.pdf')
            ->assertSee('identity.pdf');
        $this->actingAs($administrator)
            ->get(route('admin.guides.documents.download', [$guide, $documents->first()]))
            ->assertOk();

        $this->actingAs($administrator)
            ->patch(route('admin.guides.decide', $guide), ['decision' => 'approve'])
            ->assertRedirect();

        $this->assertDatabaseHas('tour_guides', [
            'guide_id' => $guide->guide_id,
            'verification_status' => 'verified',
            'admin_approval_status' => 'approved',
            'admin_approved_by' => $administrator->user_id,
        ]);
        $this->get(route('tour-guides.index'))->assertSee('Test Heritage Guide');
    }

    public function test_verification_documents_are_private_and_downloads_are_scoped_to_the_owner(): void
    {
        Storage::fake('local');

        $guideUser = User::factory()->create(['role' => 'tour_guide']);
        $guide = TourGuide::create([
            'user_id' => $guideUser->user_id,
            'license_number' => 'PRIVATE-LICENSE-001',
            'expertise' => 'Private document test',
            'availability_status' => 'available',
        ]);
        $document = $guide->verificationDocuments()->create([
            'uploaded_by' => $guideUser->user_id,
            'document_type' => 'license',
            'original_name' => "license\r\nunsafe.pdf",
            'path' => 'verification-documents/private-license.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 12,
            'sha256' => hash('sha256', 'private file'),
            'status' => 'pending',
        ]);
        Storage::disk('local')->put($document->path, 'private file');

        $otherUser = User::factory()->create(['role' => 'tour_guide']);
        $this->actingAs($otherUser)
            ->get(route('tour-guide.verification-documents.download', $document))
            ->assertForbidden();

        $response = $this->actingAs($guideUser)
            ->get(route('tour-guide.verification-documents.download', $document))
            ->assertOk();
        $this->assertSame('attachment; filename="license__unsafe.pdf"', $response->headers->get('Content-Disposition'));
        $this->assertSame('private file', $response->getContent());
    }

    public function test_provider_documents_can_be_uploaded_and_reviewed_by_the_bureau(): void
    {
        Storage::fake('local');

        $providerUser = User::factory()->create(['role' => 'service_provider']);
        $provider = ServiceProvider::create([
            'user_id' => $providerUser->user_id,
            'business_name' => 'Secure Provider Test',
            'provider_type' => 'hotel',
            'status' => 'pending',
            'verification_status' => 'pending',
        ]);
        $bureau = User::factory()->create(['role' => 'tourism_bureau_officer']);
        TourismBureauOfficer::create(['user_id' => $bureau->user_id]);

        $this->actingAs($providerUser)
            ->post(route('provider.verification-documents.store'), [
                'document_type' => 'trade_license',
                'document' => UploadedFile::fake()->create('trade-license.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $document = $provider->verificationDocuments()->firstOrFail();
        Storage::disk('local')->assertExists($document->path);

        $this->actingAs($bureau)
            ->get(route('bureau.providers.show', $provider))
            ->assertOk()
            ->assertSee('trade-license.pdf');

        $this->actingAs($bureau)
            ->patch(route('bureau.providers.documents.decide', [$provider, $document]), [
                'decision' => 'approved',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('verification_documents', [
            'document_id' => $document->document_id,
            'documentable_type' => ServiceProvider::class,
            'status' => 'approved',
            'reviewed_by' => $bureau->user_id,
        ]);
    }
}

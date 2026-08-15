<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;

class AuditService
{
    public function record(User $actor, string $action, ?string $subjectType = null, ?int $subjectId = null, array $metadata = []): AuditLog
    {
        return AuditLog::create([
            'actor_user_id' => $actor->user_id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata ?: null,
        ]);
    }
}

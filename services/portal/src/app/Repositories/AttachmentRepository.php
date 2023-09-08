<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Attachment;

class AttachmentRepository
{
    public function getAttachment(string $uuid): ?Attachment
    {
        return Attachment::find($uuid);
    }

    public function getAttachmentByName(string $fileName): ?Attachment
    {
        return Attachment::firstWhere('file_name', $fileName);
    }
}

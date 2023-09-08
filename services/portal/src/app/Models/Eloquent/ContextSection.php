<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $context_uuid
 * @property string $section_uuid
 *
 * @property Context $context
 * @property Section $section
 */
class ContextSection extends EloquentBaseModel
{
    protected $table = 'context_section';
    protected $primaryKey = ['context_uuid', 'section_uuid'];
    public $timestamps = false;

    public function context(): BelongsTo
    {
        return $this->belongsTo(Context::class);
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }
}

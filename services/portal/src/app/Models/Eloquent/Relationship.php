<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

/**
 * @property string $uuid Unique ID
 * @property string $label Visible label for UI
 * @property int $sort_order Position of the label in UI
 */
class Relationship extends EloquentBaseModel
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'relationship';

    protected $fillable = [
        'uuid',
        'label',
        'sort_order',
    ];

    /**
     * This table has no created_at, updated_at
     *
     * @var bool
     */
    public $timestamps = false;
}

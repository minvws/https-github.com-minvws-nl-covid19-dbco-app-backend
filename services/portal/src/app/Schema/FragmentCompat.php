<?php

declare(strict_types=1);

namespace App\Schema;

use App\Models\CovidCase\Contracts\Validatable;
use App\Schema\Traits\Compat;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use MinVWS\Codable\Decodable;

/**
 * Adds functionality to fragments that should be based on the schema version.
 *
 * Is only for compatibility during the migration process.
 *
 * @deprecated Placeholder: No description was set at the time.
 */
abstract class FragmentCompat extends Fragment implements Decodable, Validatable, ArrayAccess, Arrayable
{
    use Compat;

    /**
     * @deprecated Placeholder: No description was set at the time.
     */
    public function __construct(?SchemaVersion $schemaVersion = null)
    {
        parent::__construct($schemaVersion ?? static::getSchema()->getCurrentVersion());
    }
}

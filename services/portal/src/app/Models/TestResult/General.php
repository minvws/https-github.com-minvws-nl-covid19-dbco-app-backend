<?php

declare(strict_types=1);

namespace App\Models\TestResult;

use App\Models\Eloquent\TestResult;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\TestResult\General\GeneralCommon;
use App\Schema\FragmentModel;
use App\Schema\Schema;
use App\Schema\Types\StringType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

use function app;

/**
 * @property int $id
 * @property int $test_result_id
 * @property string $fragment_name
 * @property string $data
 * @property int $schema_version
 * @property CarbonImmutable $created_at
 * @property CarbonImmutable $updated_at
 * @property CarbonImmutable $expires_at
 */
class General extends FragmentModel implements GeneralCommon
{
    protected $table = 'test_result_fragment';

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    protected static string $encryptionReferenceDateAttribute = 'testResult.createdAt';

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setCurrentVersion(1);
        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\TestResult\\General');
        $schema->setDocumentationIdentifier('testResult.general');

        $schema->add(StringType::createField('testLocation')->setReadOnly());
        $schema->add(StringType::createField('testLocationCategory')->setReadOnly());

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }
}

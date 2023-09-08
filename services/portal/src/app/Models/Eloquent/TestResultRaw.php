<?php

declare(strict_types=1);

namespace App\Models\Eloquent;

use App\Models\Eloquent\Traits\CamelCaseAttributes;
use App\Models\Fields\PurposeSpecificationBinder;
use App\Models\Versions\TestResultRaw\TestResultRawCommon;
use App\Schema\Eloquent\Traits\HasSchema;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\Types\StringType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use MinVWS\DBCO\Encryption\Security\Sealed;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function app;

/**
 * @property int $test_result_id
 * @property int $schema_version
 * @property string $data
 *
 * @property TestResult $testResult
 */
class TestResultRaw extends Model implements SchemaObject, SchemaProvider, TestResultRawCommon
{
    use CamelCaseAttributes;
    use HasSchema;

    protected $table = 'test_result_raw';
    protected $primaryKey = 'test_result_id';
    public $timestamps = false;

    protected $casts = [
        'data' => Sealed::class . ':' . StorageTerm::VERY_SHORT . ',testResult.createdAt',
    ];

    protected static function loadSchema(): Schema
    {
        $schema = new Schema(self::class);

        $schema->setUseVersionedClasses(true);
        $schema->setVersionedNamespace('App\\Models\\Versions\\TestResultRaw');
        $schema->setCurrentVersion(1);

        $schema->add(StringType::createField('data'))->setAllowsNull(false);

        return app(PurposeSpecificationBinder::class)->bind($schema);
    }

    public function testResult(): BelongsTo
    {
        return $this->belongsTo(TestResult::class);
    }
}

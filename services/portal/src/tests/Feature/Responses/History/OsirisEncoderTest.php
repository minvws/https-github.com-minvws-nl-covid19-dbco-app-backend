<?php

declare(strict_types=1);

namespace Tests\Feature\Responses\History;

use App\Dto\OsirisHistory\OsirisHistoryValidationResponse;
use App\Http\Responses\History\OsirisEncoder;
use App\Models\Eloquent\OsirisHistory;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\Carbon;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Enum\Models\OsirisHistoryStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function json_decode;

#[Group('osiris')]
class OsirisEncoderTest extends FeatureTestCase
{
    private JSONEncoder $encoder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->encoder = new JSONEncoder();
        $this->encoder->getContext()->registerDecorator(OsirisHistory::class, new OsirisEncoder());
    }

    public function testEncodeSuccess(): void
    {
        $case = $this->createCase();
        $caseHistory = $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(),
        ]);

        $encoded = json_decode($this->encoder->encode($caseHistory));

        $this->assertEquals($caseHistory->uuid, $encoded->uuid);
        $this->assertEquals($caseHistory->status, $encoded->status);
        $this->assertEquals(Carbon::create($caseHistory->created_at), Carbon::create($encoded->time));
        $this->assertEquals((object) [
            'errors' => null,
            'warnings' => null,
        ], $encoded->osirisValidationResponse);
    }

    public function testEncodeSuccessWithWarnings(): void
    {
        $case = $this->createCase();
        $warnings = (array) $this->faker->sentences();
        $caseHistory = $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::success(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(warnings: $warnings),
        ]);

        $encoded = json_decode($this->encoder->encode($caseHistory));

        $this->assertEquals($caseHistory->uuid, $encoded->uuid);
        $this->assertEquals($caseHistory->status, $encoded->status);
        $this->assertEquals(Carbon::create($caseHistory->created_at), Carbon::create($encoded->time));
        $this->assertEquals((object) [
            'errors' => null,
            'warnings' => $warnings,
        ], $encoded->osirisValidationResponse);
    }

    public function testEncodeFailedWithErrors(): void
    {
        $case = $this->createCase();
        $errors = (array) $this->faker->sentences();
        $caseHistory = $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::failed(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(errors: $errors),
        ]);

        $encoded = json_decode($this->encoder->encode($caseHistory));

        $this->assertEquals($caseHistory->uuid, $encoded->uuid);
        $this->assertEquals($caseHistory->status, $encoded->status);
        $this->assertEquals(Carbon::create($caseHistory->created_at), Carbon::create($encoded->time));
        $this->assertTrue($encoded->caseIsReopened);
        $this->assertEquals((object) [
            'warnings' => null,
            'errors' => $errors,
        ], $encoded->osirisValidationResponse);
    }

    public function testEncodeFailedValidation(): void
    {
        $case = $this->createCase();
        $errors = (array) $this->faker->sentences();
        $warnings = (array) $this->faker->sentences();
        $caseHistory = $this->createOsirisHistoryForCase($case, [
            'status' => OsirisHistoryStatus::validation(),
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_DEFINITIVE,
            'osiris_validation_response' => new OsirisHistoryValidationResponse(errors: $errors, warnings: $warnings),
        ]);

        $encoded = json_decode($this->encoder->encode($caseHistory));

        $this->assertEquals($caseHistory->uuid, $encoded->uuid);
        $this->assertEquals($caseHistory->status, $encoded->status);
        $this->assertEquals(Carbon::create($caseHistory->created_at), Carbon::create($encoded->time));
        $this->assertTrue($encoded->caseIsReopened);
        $this->assertEquals((object) [
            'errors' => $errors,
            'warnings' => $warnings,
        ], $encoded->osirisValidationResponse);
    }
}

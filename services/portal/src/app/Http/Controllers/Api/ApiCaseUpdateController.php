<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaseUpdate\ApplyCaseUpdateRequest;
use App\Http\Responses\Api\CaseUpdate\CaseUpdateContactDiffDecorator;
use App\Http\Responses\Api\CaseUpdate\CaseUpdateDecorator;
use App\Http\Responses\Api\CaseUpdate\CaseUpdateDiffDecorator;
use App\Http\Responses\Api\CaseUpdate\CaseUpdateFragmentDiffDecorator;
use App\Http\Responses\Api\CaseUpdate\UpdateFieldDiffDecorator;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\CaseUpdate\CaseUpdateContactDiff;
use App\Models\CaseUpdate\CaseUpdateDiff;
use App\Models\CaseUpdate\CaseUpdateFragmentDiff;
use App\Models\Eloquent\CaseUpdate;
use App\Models\Eloquent\EloquentCase;
use App\Schema\Update\UpdateException;
use App\Schema\Update\UpdateFieldDiff;
use App\Services\CaseUpdate\ApplyCaseUpdateOptions;
use App\Services\CaseUpdate\CaseUpdateException;
use App\Services\CaseUpdate\CaseUpdateService;
use App\Services\CaseUpdate\CaseUpdateValidationException;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpFoundation\Response;

use function count;
use function response;

class ApiCaseUpdateController extends ApiController
{
    #[SetAuditEventDescription('Bijgewerkte cases getoond')]
    public function listCaseUpdates(
        EloquentCase $case,
        CaseUpdateService $service,
        CaseUpdateDecorator $caseUpdateDecorator,
    ): EncodableResponse {
        $items = $service->getCaseUpdatesForCase($case);

        $responseData = [
            'total' => count($items),
            'items' => $items,
        ];

        return
            EncodableResponseBuilder::create($responseData)
            ->withContext(static function (EncodingContext $context) use ($caseUpdateDecorator): void {
                    $context->registerDecorator(CaseUpdate::class, $caseUpdateDecorator);
            })
                ->build();
    }

    /**
     * @throws CaseUpdateException
     */
    #[SetAuditEventDescription('Bijgewerkte case opgehaald')]
    public function getCaseUpdate(
        EloquentCase $case,
        CaseUpdate $update,
        CaseUpdateDecorator $caseUpdateDecorator,
        CaseUpdateDiffDecorator $caseUpdateDiffDecorator,
        CaseUpdateFragmentDiffDecorator $caseUpdateFragmentDiffDecorator,
        CaseUpdateContactDiffDecorator $caseUpdateContactDiffDecorator,
        UpdateFieldDiffDecorator $updateFieldDiffDecorator,
        CaseUpdateService $service,
    ): EncodableResponse {
        try {
            $diff = $service->getCaseUpdateDiff($case, $update);
        } catch (CaseUpdateValidationException $e) {
            $responseData = ['validationResult' => $e->getValidationResults()];

            return
                EncodableResponseBuilder::create($responseData, Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->build();
        }

        return
            EncodableResponseBuilder::create($diff)
            ->withContext(static function (EncodingContext $context) use (
                $caseUpdateDecorator,
                $caseUpdateDiffDecorator,
                $caseUpdateContactDiffDecorator,
                $caseUpdateFragmentDiffDecorator,
                $updateFieldDiffDecorator,
            ): void {
                    $context->registerDecorator(CaseUpdate::class, $caseUpdateDecorator);
                    $context->registerDecorator(CaseUpdateDiff::class, $caseUpdateDiffDecorator);
                    $context->registerDecorator(CaseUpdateFragmentDiff::class, $caseUpdateFragmentDiffDecorator);
                    $context->registerDecorator(CaseUpdateContactDiff::class, $caseUpdateContactDiffDecorator);
                    $context->registerDecorator(UpdateFieldDiff::class, $updateFieldDiffDecorator);
            })
                ->build();
    }

    /**
     * @throws UpdateException
     */
    #[SetAuditEventDescription('Case wijziging toegepast')]
    public function applyCaseUpdate(
        EloquentCase $case,
        CaseUpdate $update,
        CaseUpdateService $service,
        ApplyCaseUpdateRequest $request,
    ): Response {
        $fieldIds = $request->getFieldIds();

        try {
            $service->applyCaseUpdate($case, $update, ApplyCaseUpdateOptions::forFieldIds($fieldIds));
        } catch (CaseUpdateValidationException $e) {
            $responseData = ['validationResult' => $e->getValidationResults()];

            return
                EncodableResponseBuilder::create($responseData, Response::HTTP_UNPROCESSABLE_ENTITY)
                    ->build();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}

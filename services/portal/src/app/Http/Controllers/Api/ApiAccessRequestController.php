<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Services\AccessRequestService;
use App\Services\CaseFragmentService;
use App\Services\CaseService;
use App\Services\TaskFragmentService;
use App\Services\TaskService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Codable\ValueTypeMismatchException;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

use function cookie;
use function response;
use function sprintf;

class ApiAccessRequestController extends ApiController
{
    public const DOWNLOAD_FORMAT_HTML = 'html';
    public const DOWNLOAD_FORMAT_PDF = 'pdf';

    private AccessRequestService $accessRequestService;
    private CaseFragmentService $caseFragmentService;
    private CaseService $caseService;
    private TaskFragmentService $taskFragmentService;
    private TaskService $taskService;

    public function __construct(
        AccessRequestService $accessRequestService,
        CaseFragmentService $caseFragmentService,
        CaseService $caseService,
        TaskFragmentService $taskFragmentService,
        TaskService $taskService,
    ) {
        $this->accessRequestService = $accessRequestService;
        $this->caseFragmentService = $caseFragmentService;
        $this->caseService = $caseService;
        $this->taskFragmentService = $taskFragmentService;
        $this->taskService = $taskService;
    }

    /**
     * Access request delete case
     */
    #[SetAuditEventDescription('Case verwijderd via API toegang')]
    public function deleteCase(EloquentCase $case, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request', $case->uuid);
        AuditObjectHelper::setAuditObjectCountDossierDeleteStarted($auditObject);
        $auditEvent->object($auditObject);

        $this->caseService->deleteCase($case);

        return response()->json([
            'uuid' => $case->uuid,
            'deleted_at' => CarbonImmutable::parse($case->deletedAt ?? null)->toISOString(),
        ], Response::HTTP_OK);
    }

    /**
     * Access request delete task
     */
    #[SetAuditEventDescription('Contact verwijderd via API toegang')]
    public function deleteTask(EloquentTask $task, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request-task', $task->uuid);
        AuditObjectHelper::setAuditObjectCountContactDeleteStarted($auditObject);
        $auditEvent->object($auditObject);

        $this->taskService->deleteTask($task);

        return response()->json([
            'uuid' => $task->uuid,
            'deleted_at' => CarbonImmutable::parse($task->deletedAt ?? null)->toISOString(),
        ], Response::HTTP_OK);
    }

    /**
     * Restore case
     */
    #[SetAuditEventDescription('Case hersteld via API toegang')]
    public function restoreCase(EloquentCase $case, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request', $case->uuid);
        AuditObjectHelper::setAuditObjectCountDossierDeleteRecovered($auditObject);
        $auditEvent->object($auditObject);

        AuditObjectHelper::setAuditObjectCountDossierDeleteRecovered($auditObject);

        $this->caseService->restoreCase($case);

        return response()->json([
            'uuid' => $case->uuid,
            'deleted_at' => null,
        ], Response::HTTP_OK);
    }

    /**
     * Access request restore task
     */
    #[SetAuditEventDescription('Contact hersteld via API toegang')]
    public function restoreTask(EloquentTask $task, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request-task', $task->uuid);
        AuditObjectHelper::setAuditObjectCountContactDeleteRecovered($auditObject);
        $auditEvent->object($auditObject);

        $this->taskService->restoreTask($task);

        return response()->json([
            'uuid' => $task->uuid,
            'deleted_at' => null,
        ], Response::HTTP_OK);
    }

    /**
     * Access request fragments case
     *
     * @throws ValueTypeMismatchException
     */
    #[SetAuditEventDescription('Fragments van case opgehaald via API toegang')]
    public function fragmentsCase(EloquentCase $softDeletedCase, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request', $softDeletedCase->uuid);
        AuditObjectHelper::setAuditObjectCountShow($auditObject);
        $auditEvent->object($auditObject);

        $data = $this->caseFragmentService->encodeFragments(
            $this->caseFragmentService->loadFragments($softDeletedCase->uuid, ['general', 'index', 'test'], true),
        );

        return response()->json(['data' => $data]);
    }

    /**
     * Access request fragment task
     *
     * @throws ValueTypeMismatchException
     */
    #[SetAuditEventDescription('Fragments van contact opgehaald via API toegang')]
    public function fragmentsTask(EloquentTask $softDeletedTask, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request-task', $softDeletedTask->uuid);
        AuditObjectHelper::setAuditObjectCountShow($auditObject);
        $auditEvent->object($auditObject);

        $caseData = $this->caseFragmentService->encodeFragments(
            $this->caseFragmentService->loadFragments($softDeletedTask->caseUuid, ['general'], true),
        );
        $taskData = $this->taskFragmentService->encodeFragments(
            $this->taskFragmentService->loadFragments($softDeletedTask->uuid, ['general', 'personalDetails'], true),
        );

        return response()->json([
            'data' => [
                'index' => $caseData,
                'task' => $taskData,
            ],
        ]);
    }

    /**
     * Access request download case
     */
    #[SetAuditEventDescription('Download case PDF via API toegang')]
    public function downloadCase(Request $request, EloquentCase $softDeletedCase, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request', $softDeletedCase->uuid);
        AuditObjectHelper::setAuditObjectCountExported($auditObject);
        $auditEvent->object($auditObject);

        return $this->doDownloadCase($request, $softDeletedCase, self::DOWNLOAD_FORMAT_PDF);
    }

    /**
     * Download case HTML
     */
    #[SetAuditEventDescription('Download case HTML via API toegang')]
    public function downloadCaseHtml(Request $request, EloquentCase $softDeletedTask, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request', $softDeletedTask->uuid);
        AuditObjectHelper::setAuditObjectCountExported($auditObject);
        $auditEvent->object($auditObject);

        return $this->doDownloadCase($request, $softDeletedTask, self::DOWNLOAD_FORMAT_HTML);
    }

    protected function doDownloadCase(Request $request, EloquentCase $case, string $format): SymfonyResponse
    {
        $downloadCompleteToken = $request->input('downloadCompleteToken') ?? null;
        $name = sprintf('inzageverzoek-%s.%s', CarbonImmutable::now()->format('Y-m-d-H:i:s'), $format);

        if ($format === self::DOWNLOAD_FORMAT_HTML) {
            $html = $this->accessRequestService->prepareCaseDownloadHtml($case, $name);
            $response = $this->htmlResponse($html, $name);
        } else {
            $pdf = $this->accessRequestService->prepareCaseDownloadPdf($case, $name);
            $response = $pdf->download($name);
        }

        if ($downloadCompleteToken) {
            $response->withCookie(cookie('downloadCompleteToken', $downloadCompleteToken)->withHttpOnly(false));
        }

        return $response;
    }

    /**
     * Access request download task
     */
    #[SetAuditEventDescription('Download contact PDF via API toegang')]
    public function downloadTask(Request $request, EloquentTask $task, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request-task', $task->uuid);
        AuditObjectHelper::setAuditObjectCountExported($auditObject);
        $auditEvent->object($auditObject);

        return $this->doDownloadTask($request, $task, self::DOWNLOAD_FORMAT_PDF);
    }

    /**
     * Access request download task HTML
     */
    #[SetAuditEventDescription('Download contact HTML via API toegang')]
    public function downloadTaskHtml(Request $request, EloquentTask $task, AuditEvent $auditEvent): SymfonyResponse
    {
        $auditObject = AuditObject::create('access-request-task', $task->uuid);
        AuditObjectHelper::setAuditObjectCountExported($auditObject);
        $auditEvent->object($auditObject);

        return $this->doDownloadTask($request, $task, self::DOWNLOAD_FORMAT_HTML);
    }

    protected function doDownloadTask(Request $request, EloquentTask $task, string $format): SymfonyResponse
    {
        $downloadCompleteToken = $request->input('downloadCompleteToken') ?? null;
        $name = sprintf('inzageverzoek-contact-%s.%s', CarbonImmutable::now()->format('Y-m-d-H:i:s'), $format);

        if ($format === self::DOWNLOAD_FORMAT_HTML) {
            $html = $this->accessRequestService->prepareTaskDownloadHtml($task, $name);
            $response = $this->htmlResponse($html, $name);
        } else {
            $pdf = $this->accessRequestService->prepareTaskDownloadPdf($task, $name);
            $response = $pdf->download($name);
        }

        if ($downloadCompleteToken) {
            $response->withCookie(cookie('downloadCompleteToken', $downloadCompleteToken)->withHttpOnly(false));
        }

        return $response;
    }

    private function htmlResponse(array|string $html, string $name): Response
    {
        return response($html, Response::HTTP_OK, [
            'Content-type' => 'text/html',
            'Content-Disposition' => sprintf('attachment; filename="%s"', $name),
        ]);
    }
}

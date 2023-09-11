<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Responses\EncodableResponseBuilder;
use App\Http\Responses\History\OsirisEncoder;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\OsirisHistory;
use App\Repositories\HistoryRepository;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Codable\EncodingContext;
use Symfony\Component\HttpFoundation\Response;

class ApiHistoryController extends ApiController
{
    public function __construct(private readonly HistoryRepository $historyRepository, protected OsirisEncoder $osirisEncoder)
    {
    }

    #[SetAuditEventDescription('Osiris log')]
    public function osiris(EloquentCase $case): Response
    {
        $osirisLogs = $this->historyRepository->getOsirisHistory($case);

        return EncodableResponseBuilder::create($osirisLogs)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(OsirisHistory::class, $this->osirisEncoder);
            })
            ->build();
    }
}

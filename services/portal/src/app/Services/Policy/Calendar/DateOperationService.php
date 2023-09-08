<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar;

use App\Dto\Admin\UpdateDateOperationDto;
use App\Models\Policy\DateOperation;
use App\Repositories\Policy\DateOperationRepository;

final class DateOperationService
{
    public function __construct(private DateOperationRepository $dateOperationRepository)
    {
    }

    public function updateDateOperation(DateOperation $dateOperation, UpdateDateOperationDto $dto): DateOperation
    {
        return $this->dateOperationRepository->updateDateOperation($dateOperation, $dto);
    }
}

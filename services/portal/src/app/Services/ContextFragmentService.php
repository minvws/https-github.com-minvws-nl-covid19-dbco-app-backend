<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentBaseModel;
use App\Repositories\ContextFragmentRepository;
use Webmozart\Assert\Assert;

class ContextFragmentService extends AbstractFragmentService
{
    private const FRAGMENT_NAMES = [
        'general',
        'circumstances',
        'contact',
    ];

    public function __construct(
        private readonly ContextFragmentRepository $fragmentRepository,
    ) {
    }

    protected static function fragmentNamespace(): string
    {
        return 'App\Models\Context';
    }

    /**
     * @inheritDoc
     */
    public static function fragmentNames(): array
    {
        return self::FRAGMENT_NAMES;
    }

    /**
     * @inheritDoc
     */
    public function loadFragments(string $ownerUuid, array $fragmentNames): array
    {
        return $this->fragmentRepository->loadContextFragments($ownerUuid, $fragmentNames);
    }

    /**
     * @inheritDoc
     */
    public function storeFragments(string $ownerUuid, array $fragments): void
    {
        $this->fragmentRepository->storeContextFragments($ownerUuid, $fragments);
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalValidationData(EloquentBaseModel $owner, array $fragmentData): array
    {
        Assert::isInstanceOf($owner, Context::class);

        if (!empty($this->cachedAdditionalValidationData)) {
            return $this->cachedAdditionalValidationData;
        }

        $this->cachedAdditionalValidationData['context'] = $owner;

        return $this->cachedAdditionalValidationData;
    }
}

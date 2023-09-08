<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Context;
use App\Schema\Fragment;
use Exception;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

use function preg_replace_callback;
use function strtolower;

class DbContextFragmentRepository implements ContextFragmentRepository
{
    public function __construct(
        private readonly EncryptionHelper $encryptionHelper,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function loadContextFragments(string $contextUuid, array $fragmentNames): array
    {
        $context = Context::find($contextUuid);
        if (!$context instanceof Context) {
            throw new Exception('Context not found');
        }

        $result = [];
        foreach ($fragmentNames as $fragmentName) {
            $result[$fragmentName] = $this->loadContextFragment($context, $fragmentName);
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function storeContextFragments(string $contextUuid, array $fragments): void
    {
        $context = null;
        foreach ($fragments as $fragment) {
            if ($fragment instanceof Fragment && $fragment->getOwner() !== null) {
                $context = $fragment->getOwner();
                break;
            }
        }

        if (!$context instanceof Context) {
            $context = Context::find($contextUuid);
        }

        if (!$context instanceof Context) {
            throw new Exception('Context not found');
        }

        $encoder = new JSONEncoder();
        foreach ($fragments as $fragmentName => $fragment) {
            $columnName = $this->columnNameForFragmentName((string) $fragmentName);

            if ($fragment instanceof Fragment) {
                $context->$columnName = $fragment;
            } else {
                $json = $encoder->encode($fragment);
                $context->$columnName = $this->encryptionHelper->sealStoreValue(
                    $json,
                    StorageTerm::long(),
                    $context->case->created_at,
                );
            }
        }

        if (!$context->save()) {
            throw new Exception('Unable to store context fragments');
        }
    }

    private function loadContextFragment(
        Context $context,
        string $fragmentName,
    ): object {
        $columnName = $this->columnNameForFragmentName($fragmentName);

        return $context->$columnName;
    }

    private function columnNameForFragmentName(string $fragmentName): string
    {
        /**
         * verb1Verb2 => verb1_verb2
         *
         * @var string $columnName
         */
        $columnName = preg_replace_callback('/[A-Z]/', static fn ($m) => '_' . strtolower($m[0]), $fragmentName);

        return $columnName;
    }
}

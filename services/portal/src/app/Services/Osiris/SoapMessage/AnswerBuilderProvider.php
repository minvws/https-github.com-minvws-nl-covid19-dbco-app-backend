<?php

declare(strict_types=1);

namespace App\Services\Osiris\SoapMessage;

use App\Models\Enums\Osiris\CaseExportType;
use App\Services\Osiris\Answer\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

use function collect;
use function in_array;

final readonly class AnswerBuilderProvider
{
    public function __construct(
        #[Config('osiris.questionnaire.answer_builders')]
        /** @var array<class-string<Builder>, array{"questionnaireVersion": array<QuestionnaireVersion>, "caseExportType": array<CaseExportType>}> $answerBuildersConfig */
        private array $answerBuildersConfig,
    ) {
    }

    /**
     * @return Collection<int,Builder>
     */
    public function getAll(?QuestionnaireVersion $version = null, ?CaseExportType $type = null): Collection
    {
        return collect($this->answerBuildersConfig)
            ->filter(static fn (array $config): bool =>
                ($version === null || in_array($version, $config['questionnaireVersion'], true))
                && ($type === null || in_array($type, $config['caseExportType'], true)))
            ->keys()
            ->map(static fn (string $className): Builder => App::make($className));
    }
}

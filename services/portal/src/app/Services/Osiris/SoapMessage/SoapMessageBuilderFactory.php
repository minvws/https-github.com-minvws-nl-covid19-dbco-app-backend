<?php

declare(strict_types=1);

namespace App\Services\Osiris\SoapMessage;

use App\Models\Eloquent\EloquentCase;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\App;
use MelchiorKokernoot\LaravelAutowireConfig\Config\Config;

final class SoapMessageBuilderFactory
{
    private readonly CarbonImmutable $questionnaireV10StartDate;

    public function __construct(
        #[Config('osiris.questionnaire.v10_start_date')]
        string $questionnaireV10StartDate,
    ) {
        $this->questionnaireV10StartDate = CarbonImmutable::parse($questionnaireV10StartDate);
    }

    public function build(EloquentCase $case): SoapMessageBuilder
    {
        return App::makeWith(SoapMessageBuilder::class, [
            'questionnaireVersion' => $case->created_at->gte($this->questionnaireV10StartDate)
                ? QuestionnaireVersion::V10
                : QuestionnaireVersion::V9,
            'case' => $case,
        ]);
    }
}

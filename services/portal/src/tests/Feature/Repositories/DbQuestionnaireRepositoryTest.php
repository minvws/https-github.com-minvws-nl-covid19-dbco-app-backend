<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Eloquent\EloquentQuestionnaire;
use App\Repositories\DbQuestionnaireRepository;
use Tests\Feature\FeatureTestCase;

class DbQuestionnaireRepositoryTest extends FeatureTestCase
{
    public function testGetQuestionnaire(): void
    {
        /** @var EloquentQuestionnaire $questionnaire */
        $questionnaire = EloquentQuestionnaire::factory()->create();

        $dbQuestionnaireRepository = new DbQuestionnaireRepository();
        $result = $dbQuestionnaireRepository->getQuestionnaire($questionnaire->uuid);

        $this->assertEquals($questionnaire->uuid, $result->uuid);
    }

    public function testGetQuestionnaireWithoutResult(): void
    {
        $dbQuestionnaireRepository = new DbQuestionnaireRepository();
        $result = $dbQuestionnaireRepository->getQuestionnaire($this->faker->word());

        $this->assertNull($result);
    }

    public function testGetLatestQuestionnaire(): void
    {
        EloquentQuestionnaire::factory()->create(['version' => 1]);
        /** @var EloquentQuestionnaire $questionnaire2 */
        $questionnaire2 = EloquentQuestionnaire::factory()->create(['version' => 2]);

        $dbQuestionnaireRepository = new DbQuestionnaireRepository();
        $result = $dbQuestionnaireRepository->getLatestQuestionnaire($questionnaire2->task_type);

        $this->assertEquals($questionnaire2->uuid, $result->uuid);
    }

    public function testGetLatestQuestionnaireWithoutResult(): void
    {
        $dbQuestionnaireRepository = new DbQuestionnaireRepository();
        $result = $dbQuestionnaireRepository->getLatestQuestionnaire($this->faker->word());

        $this->assertNull($result);
    }
}

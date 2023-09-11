<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use MinVWS\DBCO\Enum\Models\EmailLanguage;
use MinVWS\DBCO\Enum\Models\Language;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-fragment')]
#[Group('case-fragment-alternative-language')]
class ApiCaseAlternativeLanguageControllerTest extends FeatureTestCase
{
    /**
     * Test index fragment retrieval.
     */
    public function testGet(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternative-language');
        $response->assertStatus(200);

        $response = $this->be($user)->get('/api/cases/nonexisting/fragments/alternative-language');
        $response->assertStatus(404);
    }

    /**
     * Test index fragment retrieval.
     */
    public function testPost(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);

        // check no required fields
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternative-language');
        $response->assertStatus(200);

        // store
        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/fragments/alternative-language', [
            'useAlternativeLanguage' => YesNoUnknown::yes()->value,
            'phoneLanguages' => [Language::ara()->value, Language::tur()->value],
            'emailLanguage' => EmailLanguage::tr()->value,
        ]);
        $response->assertStatus(200);

        // check if the value is really stored
        $response = $this->be($user)->get('/api/cases/' . $case->uuid . '/fragments/alternative-language');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertEquals(YesNoUnknown::yes(), $data['data']['useAlternativeLanguage']);
        $this->assertEquals([Language::ara()->value, Language::tur()->value], $data['data']['phoneLanguages']);
        $this->assertEquals(EmailLanguage::tr()->value, $data['data']['emailLanguage']);
    }
}

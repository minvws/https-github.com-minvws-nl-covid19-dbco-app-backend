<?php
namespace App\Application\Repositories;

use App\Application\Models\CovidCase;
use App\Application\Repositories\CaseRepository;

/**
 * Used for retrieving case and its specific tasks.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
 */
class StubCaseRepository implements CaseRepository
{
    /**
     * Returns the case and its task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CovidCase
     */
    public function getCase(string $caseId): CovidCase
    {
        $body = <<<'EOD'
{
  "case": {
      "dateOfSymptomOnset": "2020-10-12",
      "tasks": [
        {
          "uuid": "123e4567-e89b-12d3-a456-426614172000",
          "taskType": "contact",
          "source": "portal",
          "label": "Lia B",
          "taskContext": "Partner",
          "category": "1",
          "communication": "index",
          "dateOfLastExposure": null
        },
        {
          "uuid": "123e4567-e89b-22d3-a456-426614172000",
          "taskType": "contact",
          "source": "portal",
          "label": "Aziz F.",
          "taskContext": "Voetbaltrainer",
          "category": "2a",
          "communication": "index",
          "dateOfLastExposure": "2020-10-13"
        },
        {
          "uuid": "123e4567-e89b-32d3-a456-426614172000",
          "taskType": "contact",
          "source": "portal",
          "label": "Job J.",
          "taskContext": "Collega",
          "category": "2b",
          "communication": "staff",
          "dateOfLastExposure": "2020-10-13"
        },
        {
          "uuid": "123e4567-e89b-42d3-a456-426614172000",
          "taskType": "contact",
          "source": "portal",
          "label": "Joris L.",
          "taskContext": "null",
          "category": "3",
          "communication": "index",
          "dateOfLastExposure": "2020-10-13"
        },
        {
          "uuid": "123e4567-e89b-52d3-a456-426614172000",
          "taskType": "contact",
          "source": "portal",
          "label": "Peter V.",
          "taskContext": "Zakenrelatie",
          "category": "3",
          "communication": "none",
          "dateOfLastExposure": "2020-10-13"
        }
      ]
  }
}
EOD;

        return new CovidCase([], $body);
    }

    /**
     * Submit case and its tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCase(string $caseId, string $body): void
    {

    }
}

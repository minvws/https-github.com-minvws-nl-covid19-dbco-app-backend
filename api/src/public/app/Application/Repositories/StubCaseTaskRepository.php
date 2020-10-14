<?php
namespace App\Application\Repositories;

use App\Application\Models\CaseTaskList;

/**
 * Used for retrieving case specific tasks.
 *
 * Stub implementation.
 *
 * @package App\Application\Repositories
 */
class StubCaseTaskRepository implements CaseTaskRepository
{
    /**
     * Returns the case task list.
     *
     * @param string $caseId Case identifier.
     *
     * @return CaseTaskList
     */
    public function getCaseTasks(string $caseId): CaseTaskList
    {
        $body = <<<'EOD'
{
  "tasks": [
    {
      "id": "123e4567-e89b-12d3-a456-426614172000",
      "taskType": "contact",
      "source": "portal",
      "label": "Lia B",
      "context": "Partner",
      "category": "1",
      "communication": "index"
    },
    {
      "id": "123e4567-e89b-22d3-a456-426614172000",
      "taskType": "contact",
      "source": "portal",
      "label": "Aziz F.",
      "context": "Voetbaltrainer",
      "category": "2a",
      "communication": "index"
    },
    {
      "id": "123e4567-e89b-32d3-a456-426614172000",
      "taskType": "contact",
      "source": "portal",
      "label": "Job J.",
      "context": "Collega",
      "category": "2b",
      "communication": "staff"
    },
    {
      "id": "123e4567-e89b-42d3-a456-426614172000",
      "taskType": "contact",
      "source": "portal",
      "label": "Joris L.",
      "context": "null",
      "category": "3",
      "communication": "index"
    },
    {
      "id": "123e4567-e89b-52d3-a456-426614172000",
      "taskType": "contact",
      "source": "portal",
      "label": "Peter V.",
      "context": "Zakenrelatie",
      "category": "3",
      "communication": "none"
    }
  ]
}
EOD;

        return new CaseTaskList([], $body);
    }

    /**
     * Submit case tasks.
     *
     * @param string $caseId
     * @param string $body
     *
     * @return void
     */
    public function submitCaseTasks(string $caseId, string $body): void
    {

    }
}

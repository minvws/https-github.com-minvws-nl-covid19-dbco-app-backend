<?php
declare(strict_types=1);

namespace Tests\Application\Repositories;

use App\Application\DTO\DbcoCase;
use App\Application\Helpers\RandomKeyGenerator;
use App\Application\Repositories\DbCaseRepository;
use Tests\TestCase;

class DbCaseRepositoryTest extends TestCase
{
    protected $repository;

    protected function setUp(): void
    {
        $randomKeyGenerator = new RandomKeyGenerator(['allowedChars' => 'abc']);
        $pdo = $this->getAppInstance()->getContainer()->get('PDO');
        $this->repository = new DbCaseRepository($pdo, $randomKeyGenerator, 3);
    }

    public function testReturnsCaseModelWhenNoExceptionOccurs(): void
    {
        $caseId = "123456";
        /** @var \App\Application\Models\DbcoCase $dbcoCase */
        $dbcoCase = $this->repository->create($caseId);

        self::assertSame($caseId, $dbcoCase->caseId);
    }
}

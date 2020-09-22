<?php
declare(strict_types=1);

namespace Tests\Application\Repositories;

use App\Application\Helpers\RandomKeyGenerator;
use App\Application\Repositories\DbCaseRepository;
use App\Domain\DomainException\KeyAlreadyExistsException;
use PDO;
use PDOException;
use PDOStatement;
use Tests\TestCase;

class DbCaseRepositoryStubTest extends TestCase
{
    protected $pdo;
    protected $stmt;
    protected $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createStub(PDO::class);
        $this->stmt = $this->createStub(PDOStatement::class);

        $this->pdo->method('prepare')
            ->willReturn($this->stmt);

        $randomKeyGenerator = new RandomKeyGenerator(['allowedChars' => 'abc']);
        $this->repository = new DbCaseRepository($this->pdo, $randomKeyGenerator, 3);
    }

    public function testReturnsLastInsertIdWhenNoExceptionOccurs(): void
    {
        $id = 10022;
        $this->stmt->method('execute')
            ->willReturn(['case_id' => "123456", 'pairing_code' => 'XHRWSF', 'pairing_code_expires_at' => '2020-09-22T14:05:15Z']);
        $this->pdo->method('lastInsertId')
            ->willReturn($id);

        $caseId = "123456";
        /** @var \App\Application\Models\DbcoCase $dbcoCase */
        $dbcoCase = $this->repository->create($caseId);

        self::assertSame($id, $dbcoCase->id);
        self::assertSame($caseId, $dbcoCase->caseId);
    }

    public function testExceptionIsThrownWhenKeyAlreadyExists(): void
    {
        $this->stmt->method('fetchColumn')->willReturn(1);

        $this->expectException(KeyAlreadyExistsException::class);

        $this->repository->create("123456");
    }
}

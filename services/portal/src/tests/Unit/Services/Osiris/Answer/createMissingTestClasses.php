<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;

require_once __DIR__ . '/../../../../../vendor/autoload.php';

foreach (glob(__DIR__ . '/../../../../../app/Services/Osiris/Answer/*Builder.php') as $file) {
    $builderClass = basename($file, '.php');
    $builderClassWithNS = 'App\Services\Osiris\Answer\\' . $builderClass;
    $testClass = $builderClass . 'Test';
    $testClassFile = $testClass . '.php';
    $testClassPath = __DIR__ . '/' . $testClassFile;

    $code = <<<END
    <?php

    declare(strict_types=1);

    namespace Tests\Unit\Services\Osiris\Answer;

    use App\Models\Eloquent\EloquentCase;
    use $builderClassWithNS;
    use Carbon\CarbonImmutable;
    use Tests\TestCase;
    use Tests\Unit\Services\Osiris\Answer\Attributes\Builder;
    use Tests\Unit\Services\Osiris\Answer\Traits\AssertAnswers;

    /**
     * @group osiris
     * @group osiris-answer
     */
    #[Builder($builderClass::class)]
    class $testClass extends TestCase
    {
        use AssertAnswers;

        public function testBuilder(): void
        {
            \$this->markTestIncomplete('Test for $builderClass has not been implemented yet');
        }

        private function createCase(): EloquentCase
        {
            \$case = EloquentCase::getSchema()->getVersion(3)->newInstance();
            assert(\$case instanceof EloquentCase);
            \$case->createdAt = CarbonImmutable::now();
            return \$case;
        }
    }

    END;

    $refClass = new ReflectionClass($builderClassWithNS);
    if (!$refClass->isInstantiable() || file_exists($testClassPath)) {
        continue;
    }

    echo "Creating $testClassFile...\n";
    file_put_contents($testClassPath, $code);
}

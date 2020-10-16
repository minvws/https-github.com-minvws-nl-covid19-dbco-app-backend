<?php
declare(strict_types=1);

namespace Tests;

use Exception;
use PHPUnit\Framework\TestCase as PHPUnit_TestCase;
use Symfony\Component\Console\Application;

class TestCase extends PHPUnit_TestCase
{
    /**
     * Create application.
     *
     * @return Application
     *
     * @throws Exception
     */
    protected function createApplication(): Application
    {
        $app = require __DIR__ . '/bootstrap/application.php';
        return $app;
    }
}

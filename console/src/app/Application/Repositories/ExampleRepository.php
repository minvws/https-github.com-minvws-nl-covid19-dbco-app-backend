<?php
namespace App\Application\Repositories;

use App\Application\Models\Example;

interface ExampleRepository
{
    /**
     * Create new example model.
     *
     * @return Example Example model.
     *
     * @throws Exception
     */
    public function createExample(): Example;

    /**
     * Mark example as prepared.
     *
     * @param Example $$example
     *
     * @throws Exception
     */
    public function markExampleAsPrepared(Example $example): void;

    /**
     * Mark example as exported.
     *
     * @param Example $$example
     *
     * @return void
     *
     * @throws Exception
     */
    public function markExampleAsExported(Example $example): void;
}

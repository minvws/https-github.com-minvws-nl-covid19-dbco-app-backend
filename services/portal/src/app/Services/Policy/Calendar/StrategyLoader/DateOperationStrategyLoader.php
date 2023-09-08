<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use App\Models\Policy\DateOperation;

/**
 * @template TData
 *
 * @extends StrategyLoader<TData,DateOperation>
 */
interface DateOperationStrategyLoader extends StrategyLoader
{
}

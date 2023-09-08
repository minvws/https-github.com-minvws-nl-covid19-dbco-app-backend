<?php

declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

use Psr\Http\Message\ResponseInterface as Response;

/**
 * Ping action. Useful to check reachability of API from another system.
 *
 * @package DBCO\Shared\Application\Actions
 */
class PingAction extends Action
{
    /**
     * @inheritdoc
     */
    protected function action(): Response
    {
        $this->auditService->setEventExpected(false);

        $this->response->getBody()->write('PONG');
        return $this->response->withStatus(200);
    }
}

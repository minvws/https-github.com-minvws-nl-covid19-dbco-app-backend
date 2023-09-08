<?php

namespace MinVWS\Audit;

use Closure;
use Exception;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Repositories\AuditRepository;
use Psr\Http\Message\ResponseInterface;

/**
 * NEN 7513 logging service.
 *
 * @package MinVWS\Audit
 */
class AuditService
{
    /**
     * @var AuditRepository
     */
    private AuditRepository $auditRepository;

    /**
     * Service name.
     *
     * @var string|null
     */
    private ?string $service = null;

    /**
     * User callback.
     *
     * @var Closure|null
     */
    private ?Closure $userCallback = null;

    /**
     * Event in progress.
     */
    private ?AuditEvent $currentEvent = null;

    /**
     * Event registered?
     *
     * @var bool
     */
    private bool $isEventRegistered = false;

    /**
     * By default we expect at least one event.
     *
     * @var bool
     */
    private bool $isEventExpected = true;

    private ?Closure $onAfterRegister;

    public function __construct(AuditRepository $auditRepository, callable $onAfterRegister = null)
    {
        $this->auditRepository = $auditRepository;
        $this->onAfterRegister = $onAfterRegister;
    }

    /**
     * Sets the service name.
     *
     * @param string|null $service
     */
    public function setService(?string $service)
    {
        $this->service = $service;
    }

    /**
     * Sets the callback for determining the current user.
     *
     * @param Closure|null $userCallback
     */
    public function setUserCallback(?Closure $userCallback)
    {
        $this->userCallback = $userCallback;
    }

    /**
     * Allows for checking if an event has been registered or not.
     *
     * @return bool
     */
    public function isEventRegistered(): bool
    {
        return $this->isEventRegistered;
    }

    /**
     * Is an event expected?
     *
     * @return bool
     */
    public function isEventExpected(): bool
    {
        return $this->isEventExpected;
    }

    /**
     * Should we expect at least one audit event?
     *
     * @param bool $eventExpected
     */
    public function setEventExpected(bool $eventExpected)
    {
        $this->isEventExpected = $eventExpected;
    }

    /**
     * Start audit event.
     *
     * The event will not be registered until finalize(Http)Event is called.
     *
     * @param AuditEvent $event
     *
     * @return AuditEvent
     */
    public function startEvent(AuditEvent $event): AuditEvent
    {
        $this->currentEvent = $event;
        return $event;
    }

    /**
     * Returns the currently active event (started but not finalized).
     *
     * @return AuditEvent
     */
    public function getCurrentEvent(): AuditEvent
    {
        return $this->currentEvent;
    }

    /**
     * Register audit event.
     *
     * The optional body closure can be used to automatically set the result to success/error.
     *
     * @param AuditEvent   $event
     * @param Closure|null $body
     *
     * @return mixed|null
     *
     * @throws Exception
     */
    public function registerEvent(AuditEvent $event, ?Closure $body = null)
    {
        try {
            $this->startEvent($event);
            return $body !== null ? $body($event) : null;
        } catch (Exception $e) {
            $event->result(AuditEvent::RESULT_ERROR);
            throw $e;
        } finally {
            $this->finalizeEvent();
        }
    }

    /**
     * Registers the given audit event and automatically sets the result based on the http response code.
     *
     * @param AuditEvent   $event
     * @param Closure|null $body
     *
     * @return ResponseInterface|mixed
     *
     * @throws Exception
     */
    public function registerHttpEvent(AuditEvent $event, ?Closure $body)
    {
        $response = null;

        try {
            $this->startEvent($event);
            $response = $body !== null ? $body($event) : null;
            return $response;
        } catch (Exception $e) {
            if (method_exists($e, 'getResponse')) {
                // HttpResponseException
                $response = $e->getResponse();
            } else {
                $event->result(AuditEvent::RESULT_ERROR);
            }

            throw $e;
        } finally {
            $this->finalizeHttpEvent($response);
        }
    }

    /**
     * Finalize registered event.
     */
    public function finalizeEvent()
    {
        $event = $this->currentEvent;
        if ($event === null) {
            return;
        }

        if ($event->getResult() === null) {
            $event->result(AuditEvent::RESULT_SUCCESS);
        }

        if ($event->getService() === null && $this->service !== null) {
            $event->service($this->service);
        }

        if (count($event->getUsers()) === 0 && $this->userCallback !== null) {
            $event->user(call_user_func($this->userCallback));
        }

        $this->auditRepository->registerEvent($event);

        if (is_callable($this->onAfterRegister)) {
            ($this->onAfterRegister)($event);
        }

        $this->isEventRegistered = true;
        $this->currentEvent = null;
    }

    /**
     * Finalize registered HTTP event.
     *
     * @param ResponseInterface|object|null $response
     */
    public function finalizeHttpEvent(?object $response)
    {
        $event = $this->currentEvent;
        if ($event === null) {
            return;
        }

        if ($event->getResult() === null && $response !== null) {
            $event->result($this->resultForHttpResponse($response));
        }

        $this->finalizeEvent();
    }

    /**
     * Result for response.
     *
     * @param ResponseInterface|object $response
     *
     * @return string
     */
    private function resultForHttpResponse(object $response): string
    {
        $statusCode = 200;
        if ($response instanceof ResponseInterface || method_exists($response, 'getStatusCode')) {
            $statusCode = $response->getStatusCode();
        } elseif (method_exists($response, 'status')) {
            $statusCode = $response->status();
        }

        return $this->resultForHttpStatusCode($statusCode);
    }

    /**
     * Determine event result based on the HTTP response status code.
     *
     * @param int $statusCode
     *
     * @return string
     */
    private function resultForHttpStatusCode(int $statusCode): string
    {
        if ($statusCode >= 200 && $statusCode < 400) {
            return AuditEvent::RESULT_SUCCESS;
        } elseif ($statusCode === 401) {
            return AuditEvent::RESULT_UNAUTHORIZED;
        } elseif ($statusCode === 403) {
            return AuditEvent::RESULT_FORBIDDEN;
        } elseif ($statusCode >= 400 && $statusCode < 500) {
            return AuditEvent::RESULT_CLIENT_ERROR;
        } elseif ($statusCode >= 500) {
            return AuditEvent::RESULT_ERROR;
        }
        return AuditEvent::RESULT_UNKNOWN;
    }
}

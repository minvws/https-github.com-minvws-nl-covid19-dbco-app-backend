<?php

namespace DBCO\Shared\Application\Bridge;

/**
 * Bridge request.
 */
class Request
{
    /**
     * @var string
     */
    private string $key;

    /**
     * @var array
     */
    private $params = [];

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string|null
     */
    private ?string $responseKey = null;

    /**
     * @var int
     */
    private int $timeout = 30;

    /**
     * Constructor.
     *
     * @param string $key Request key.
     */
    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Returns the request key.
     *
     * @param string $key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Create request.
     *
     * @param string $key
     *
     * @return self
     */
    public static function create(string $key): self
    {
        return new self($key);
    }

    /**
     * Returns the request parameters.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }


    /**
     * Set request parameter.
     *
     * @param string $name
     * @param string $value
     *
     * @return self
     */
    public function setParam(string $name, string $value): self
    {
        $this->params[$name] = $value;
        return $this;
    }

    /**
     * Set data payload.
     *
     * @param string $data
     *
     * @return self
     */
    public function setData(string $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get data payload.
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Returns the response key.
     *
     * @return string|null
     */
    public function getResponseKey(): ?string
    {
        return $this->responseKey;
    }

    /**
     * Sets the response key.
     *
     * @param string|null $responseKey
     *
     * @return self
     */
    public function setResponseKey(?string $responseKey): self
    {
        $this->responseKey = $responseKey;
        return $this;
    }

    /**
     * Get request/response timeout.
     *
     * @return int
     */
    public function getTimeout(): int
    {
        return $this->timeout;
    }

    /**
     * Sets the request/response timeout.
     *
     * @param int $timeout
     *
     * @return self
     */
    public function setTimeout(int $timeout): self
    {
        $this->timeout = $timeout;
        return $this;
    }
}

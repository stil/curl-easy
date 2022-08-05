<?php

namespace cURL;

use CurlHandle;

class Response
{
    /** @var resource|CurlHandle */
    protected $ch;

    /** @var Error|null */
    protected $error;

    /** @var string|null */
    protected $content = null;

    /**
     * Constructs response
     *
     * @param Request $request Request
     * @param string|null $content Content of reponse
     */
    public function __construct(Request $request, string $content = null)
    {
        $this->ch = $request->getHandle();

        if (is_string($content)) {
            $this->content = $content;
        }
    }

    /**
     * Get information regarding a current transfer
     * If opt is given, returns its value as a string
     * Otherwise, returns an associative array with
     * the following elements (which correspond to opt), or FALSE on failure.
     *
     * @param int|null $key One of the CURLINFO_* constants
     * @return mixed
     */
    public function getInfo(int $key = null)
    {
        return $key === null ? curl_getinfo($this->ch) : curl_getinfo($this->ch, $key);
    }

    /**
     * Returns content of request
     *
     * @return string    Content
     */
    public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * Sets error instance
     *
     * @param Error $error Error to set
     * @return void
     */
    public function setError(Error $error)
    {
        $this->error = $error;
    }

    /**
     * Returns a error instance
     *
     * @return Error|null
     */
    public function getError(): ?Error
    {
        return $this->error;
    }

    /**
     * @return bool
     */
    public function hasError(): bool
    {
        return isset($this->error);
    }
}

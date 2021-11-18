<?php

namespace App\ReCaptcha;

use ReCaptcha\Response;
use ReCaptcha\ReCaptcha;

class ReCaptchaService
{
    private ReCaptcha $reCaptcha;
    private ?Response $response = null;

    /**
     * @var float|int $threshold is the minimum score to be valid.
     */
    public const THRESHOLD = 0.5;

    public function __construct(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    /**
     * Handle the ReCaptcha Response Token from Google.
     */
    public function handleResponse(string $responseToken): void
    {
        $this->response = $this->reCaptcha->verify($responseToken);
    }

    /**
     * @param float|int $threshold is the minimum score to be valid.
     */
    public function isResponseValid($threshold = null): bool
    {
        if (null === $this->response) {
            throw new \Exception("ReCaptcha should call handleResponse() before isResponseValid()");
        }

        return $this->response->getScore() > ($threshold ?: self::THRESHOLD);
    }
}

<?php

namespace App\ReCaptcha\Test;

use App\ReCaptcha\ReCaptchaService;

/**
 * ReCaptchaService should let robots pass through validation in test environment.
 */
class ReCaptchaServiceTest extends ReCaptchaService
{
    /**
     * Always return true to pass recaptcha with robots.
     */
    public function isResponseValid($threshold = null): bool
    {
        return true;
    }
}

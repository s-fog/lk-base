<?php

namespace App\Object;

readonly class EmailVerifyToken
{
    private string $verifyCode;
    private int $canSendVerifyCodeAfterTimestamp;

    const RESEND_PERIOD = 120;

    public function __construct(string $verifyCode = null, int $canSendVerifyCodeAfterTimestamp = null)
    {
        $this->verifyCode = $verifyCode === null ? rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) : $verifyCode;
        $this->canSendVerifyCodeAfterTimestamp = $canSendVerifyCodeAfterTimestamp === null ? time() + self::RESEND_PERIOD : $canSendVerifyCodeAfterTimestamp;
    }

    public function canSendVerifyCode(): bool
    {
        return time() > $this->canSendVerifyCodeAfterTimestamp;
    }

    public function getString(): string
    {
        return $this->verifyCode . '_' . $this->canSendVerifyCodeAfterTimestamp;
    }

    public function getVerifyToken(): string
    {
        return $this->verifyCode;
    }

    public static function createFromString(string $string): EmailVerifyToken
    {
        $array = explode('_', $string);
        return new self($array[0] ,$array[1]);
    }
}
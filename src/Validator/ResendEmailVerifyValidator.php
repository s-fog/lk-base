<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;


class ResendEmailVerifyValidator
{
    public function getError(User $user): ?string
    {
        return $user->getEmailVerifyToken()->canSendVerifyCode() ? null : 'resend_email_verify_code.time';
    }
}
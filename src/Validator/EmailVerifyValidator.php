<?php

namespace App\Validator;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;


class EmailVerifyValidator
{
    public function getError(Request $request, User $user): ?string
    {
        $emailVerifyToken = $user->getEmailVerifyToken();
        $error = null;

        if ($request->getPayload()->get('email_verify_token') === '') {
            $error = 'email_verify_code.empty';
        } else if ($request->getPayload()->get('email_verify_token') !== $emailVerifyToken->getVerifyToken()) {
            $error = 'email_verify_code.not_correct';
        }

        return $error;
    }
}
<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;


class RegisterValidator
{
    public function __construct(public UserRepository $userRepository)
    {
    }

    public function getErrors(Request $request): array
    {
        $errors  = [];

        if ($request->getPayload()->get('email') === null || $request->getPayload()->get('email') === '') {
            $errors['email'] = 'email.empty';
        } else {
            if (filter_var($request->getPayload()->get('email'), FILTER_VALIDATE_EMAIL) === false) {
                $errors['email'] = 'email.format';
            }

            $user = $this->userRepository->findOneBy(['email' => $request->getPayload()->get('email')]);
            if ($user !== null) {
                $errors['email'] = 'email.user-exists';
            }
        }

        if ($request->getPayload()->get('password') === null ||
            $request->getPayload()->get('password') === '' ||
            strlen($request->getPayload()->get('password')) < 6) {
            $errors['password'] = 'password.length';
        }

        return $errors;
    }
}
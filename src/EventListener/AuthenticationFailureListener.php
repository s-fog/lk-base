<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Response\JWTAuthenticationFailureResponse;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @param AuthenticationFailureEvent $event
 */

class AuthenticationFailureListener {
    public function onAuthenticationFailureResponse(AuthenticationFailureEvent $event)
    {
        $response = new JWTAuthenticationFailureResponse('Неверные данные', JsonResponse::HTTP_UNAUTHORIZED);

        $event->setResponse($response);
    }
}
?>
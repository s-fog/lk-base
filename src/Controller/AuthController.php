<?php

namespace App\Controller;

use App\Entity\User;
use App\Object\EmailVerifyToken;
use App\Security\AccessTokenHandler;
use App\Security\EmailVerifier;
use App\Validator\EmailVerifyValidator;
use App\Validator\RegisterValidator;
use App\Validator\ResendEmailVerifyValidator;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Uid\Uuid;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

#[Route('/auth')]
class AuthController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier, private JWTTokenManagerInterface $JWTManager)
    {
    }

    #[Route('/register', name: 'app_register', methods: 'POST')]
    public function register(Request $request,
                             UserPasswordHasherInterface $userPasswordHasher,
                             RegisterValidator $registerValidator,
                             EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $user->setEmail($request->toArray()['email'] ?? '');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword(
            $userPasswordHasher->hashPassword(
                $user,
                $request->toArray()['password'] ?? ''
            )
        );

        $emailVerifyCode = new EmailVerifyToken();
        $user->setEmailVerifyToken($emailVerifyCode);

        $errors = $registerValidator->getErrors($request);

        if (count($errors) === 0) {
            $entityManager->persist($user);
            $entityManager->flush();
            $this->emailVerifier->sendEmailConfirmation($user);

            return $this->json(['token' => $this->JWTManager->create($user)]);
        }

        return $this->json([
            'status' => 'validation',
            'errors' => $errors
        ], 422);
    }

    #[Route('/user', name: 'app_user', methods: 'GET')]
    public function user(#[CurrentUser] ?User $user): Response
    {
        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'isEmailVerified' => $user->isEmailVerified(),
            ]
        ]);
    }

    #[Route('/email-confirmation', name: 'email_verification', methods: 'POST')]
    public function emailVerification(
        #[CurrentUser] ?User $user,
        Request $request,
        EmailVerifier $emailVerifier,
        EmailVerifyValidator $emailVerifyValidator
    ): Response
    {
        $error = $emailVerifyValidator->getError($request, $user);

        if ($error !== null) {
            return $this->json([
                'status' => 'validation',
                'error' => $error
            ], 422);
        }

        $emailVerifier->verifyUsersEmail($user);

        return $this->json([
            'status' => 'success',
        ]);
    }

    #[Route('/resend-email-confirmation', name: 'resend_email_verification', methods: 'POST')]
    public function resendEmailVerification(
        #[CurrentUser] ?User $user,
        Request $request,
        EmailVerifier $emailVerifier,
        ResendEmailVerifyValidator $resendEmailVerifyValidator
    ): Response
    {
        $error = $resendEmailVerifyValidator->getError($user);

        if ($error !== null) {
            return $this->json([
                'status' => 'validation',
                'error' => $error
            ], 422);
        }

        $emailVerifier->setNewEmailVerifyTokenAfterResend($user);
        $emailVerifier->sendEmailConfirmation($user);

        return $this->json([
            'status' => 'success',
        ]);
    }

    #[Route('/logout', name: 'app_logout', methods: 'POST')]
    public function logout(#[CurrentUser] ?User $user, EntityManagerInterface $entityManager): Response
    {
        foreach($user->getAccessTokens() as $accessToken) {
            $entityManager->remove($accessToken);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'success'
        ]);
    }
}

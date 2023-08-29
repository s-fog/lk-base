<?php

namespace App\Security;

use App\Entity\User;
use App\Object\EmailVerifyToken;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class EmailVerifier
{
    public function __construct(
        private readonly MailerInterface        $mailer,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    public function sendEmailConfirmation(UserInterface $user): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address('s-fog@yandex.ru', 'Some Name'))
            ->to($user->getEmail())
            ->subject('Подтвердите email')
            ->htmlTemplate('emails/confirmation_email.html.twig');
        $context = $email->getContext();
        $context['verify_token'] = $user->getEmailVerifyToken()->getVerifyToken();
        $email->context($context);
        $this->mailer->send($email);
    }

    /**
     * @throws VerifyEmailExceptionInterface
     */
    public function verifyUsersEmail(User $user): void
    {
        $user->clearEmailVerifyToken();
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    public function setNewEmailVerifyTokenAfterResend(User $user): void
    {
        $user->setEmailVerifyToken(new EmailVerifyToken);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}

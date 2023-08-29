<?php

namespace App\Controller;

use App\Security\EmailVerifier;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;

class AppController extends AbstractController
{
    public function __construct(private EmailVerifier $emailVerifier)
    {
    }
}

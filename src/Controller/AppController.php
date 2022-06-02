<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AppController extends AbstractController
{
    #[Route('/')]
    public function indexNoLocale(Request $request): Response
    {
        return $this->redirectToRoute('homepage', ['_locale' => $request->getDefaultLocale()]);
    }
}

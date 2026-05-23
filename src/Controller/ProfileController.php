<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ProfileController extends AbstractController
{
    #[Route('/api/v1/profile', name: 'profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->json([
            'user' => $this->getUser(),
        ]);
    }
}

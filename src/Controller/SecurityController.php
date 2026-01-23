<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SecurityController 
{
    /**
     * Cette route existe UNIQUEMENT pour permettre à Symfony
     * de reconnaître /api/login.
     *
     * Le login réel est géré par le firewall + LexikJWT.
     */

    #[Route('/api/login', name: 'api_login', methods:['POST'])]
    public function index(): Response
    {
        // Ce code ne sera JAMAIS exécuté
        // La sécurité intercepte avant
        return new Response('JWT Login');
    }
}

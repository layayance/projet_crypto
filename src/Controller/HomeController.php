<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\RouterInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(RouterInterface $router): Response
    {
        // Récupérer toutes les routes API
        $routes = $router->getRouteCollection();
        $apiRoutes = [];
        
        foreach ($routes as $routeName => $route) {
            $path = $route->getPath();
            if (str_starts_with($path, '/api')) {
                $apiRoutes[] = [
                    'name' => $routeName,
                    'path' => $path,
                    'methods' => $route->getMethods() ?: ['GET'],
                ];
            }
        }
        
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'api_routes' => $apiRoutes,
        ]);
    }
    
    #[Route('/api/routes', name: 'api_routes_list', methods: ['GET'])]
    public function apiRoutesList(RouterInterface $router): JsonResponse
    {
        $routes = $router->getRouteCollection();
        $apiRoutes = [];
        
        foreach ($routes as $routeName => $route) {
            $path = $route->getPath();
            if (str_starts_with($path, '/api')) {
                $apiRoutes[] = [
                    'name' => $routeName,
                    'path' => $path,
                    'methods' => $route->getMethods() ?: ['GET'],
                ];
            }
        }
        
        return new JsonResponse([
            'routes' => $apiRoutes,
            'count' => count($apiRoutes),
            'base_url' => 'http://localhost:8000',
        ]);
    }
}

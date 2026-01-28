<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CacheSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 10],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Ne pas mettre en cache les requêtes POST/PUT/DELETE
        if (!in_array($request->getMethod(), ['GET', 'HEAD'])) {
            return;
        }

        // Ne pas mettre en cache les routes d'authentification
        $path = $request->getPathInfo();
        if (str_starts_with($path, '/api/login') || str_starts_with($path, '/api/register')) {
            return;
        }

        // Ajouter des headers de cache pour les routes GET
        // Le frontend peut utiliser ces headers pour décider de mettre en cache
        $response->headers->set('Cache-Control', 'private, max-age=30, must-revalidate');
        $response->headers->set('X-Cache-TTL', '30'); // TTL en secondes pour le frontend
        
        // Ajouter un ETag basé sur le contenu (pour validation conditionnelle)
        if ($response->getContent()) {
            $etag = md5($response->getContent());
            $response->headers->set('ETag', '"' . $etag . '"');
        }

        // Gérer les requêtes If-None-Match (304 Not Modified)
        if ($request->headers->has('If-None-Match')) {
            $ifNoneMatch = $request->headers->get('If-None-Match');
            if ($ifNoneMatch === $response->headers->get('ETag')) {
                $response->setStatusCode(304);
                $response->setContent(null);
            }
        }
    }
}

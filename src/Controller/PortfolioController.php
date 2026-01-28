<?php

namespace App\Controller;

use App\Entity\CryptoAsset;
use App\Repository\CryptoAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/portfolio')]
#[IsGranted('ROLE_USER')]
class PortfolioController extends AbstractController
{
    #[Route('', name: 'api_portfolio_list', methods: ['GET'])]
    public function list(CryptoAssetRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $assets = $repository->findBy(['user' => $user], ['createdAt' => 'DESC']);

        $data = array_map(function (CryptoAsset $asset) {
            return [
                'id' => $asset->getId(),
                'symbol' => $asset->getSymbol(),
                'name' => $asset->getName(),
                'quantity' => $asset->getQuantity(),
                'purchasePrice' => $asset->getPurchasePrice(),
                'purchaseDate' => $asset->getPurchaseDate()->format('Y-m-d H:i:s'),
                'createdAt' => $asset->getCreatedAt()->format('Y-m-d H:i:s'),
                'updatedAt' => $asset->getUpdatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $assets);

        return new JsonResponse([
            'assets' => $data,
            'count' => count($data)
        ]);
    }

    #[Route('/{id}', name: 'api_portfolio_show', methods: ['GET'])]
    public function show(int $id, CryptoAssetRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $asset = $repository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$asset) {
            return new JsonResponse([
                'error' => 'Actif non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id' => $asset->getId(),
            'symbol' => $asset->getSymbol(),
            'name' => $asset->getName(),
            'quantity' => $asset->getQuantity(),
            'purchasePrice' => $asset->getPurchasePrice(),
            'purchaseDate' => $asset->getPurchaseDate()->format('Y-m-d H:i:s'),
            'createdAt' => $asset->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $asset->getUpdatedAt()->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('', name: 'api_portfolio_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['symbol']) || !isset($data['name']) || !isset($data['quantity']) || !isset($data['purchasePrice'])) {
            return new JsonResponse([
                'error' => 'Données manquantes: symbol, name, quantity et purchasePrice sont requis'
            ], Response::HTTP_BAD_REQUEST);
        }

        $asset = new CryptoAsset();
        $asset->setSymbol(strtoupper($data['symbol']));
        $asset->setName($data['name']);
        $asset->setQuantity($data['quantity']);
        $asset->setPurchasePrice($data['purchasePrice']);
        $asset->setUser($this->getUser());
        
        if (isset($data['purchaseDate'])) {
            try {
                $asset->setPurchaseDate(new \DateTime($data['purchaseDate']));
            } catch (\Exception $e) {
                return new JsonResponse([
                    'error' => 'Format de date invalide (format attendu: Y-m-d H:i:s)'
                ], Response::HTTP_BAD_REQUEST);
            }
        } else {
            $asset->setPurchaseDate(new \DateTime());
        }

        $errors = $validator->validate($asset);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse([
                'error' => 'Données invalides',
                'details' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($asset);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Actif créé avec succès',
            'asset' => [
                'id' => $asset->getId(),
                'symbol' => $asset->getSymbol(),
                'name' => $asset->getName(),
                'quantity' => $asset->getQuantity(),
                'purchasePrice' => $asset->getPurchasePrice(),
                'purchaseDate' => $asset->getPurchaseDate()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_portfolio_update', methods: ['PUT', 'PATCH'])]
    public function update(
        int $id,
        Request $request,
        CryptoAssetRepository $repository,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $user = $this->getUser();
        $asset = $repository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$asset) {
            return new JsonResponse([
                'error' => 'Actif non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['symbol'])) {
            $asset->setSymbol(strtoupper($data['symbol']));
        }
        if (isset($data['name'])) {
            $asset->setName($data['name']);
        }
        if (isset($data['quantity'])) {
            $asset->setQuantity($data['quantity']);
        }
        if (isset($data['purchasePrice'])) {
            $asset->setPurchasePrice($data['purchasePrice']);
        }
        if (isset($data['purchaseDate'])) {
            try {
                $asset->setPurchaseDate(new \DateTime($data['purchaseDate']));
            } catch (\Exception $e) {
                return new JsonResponse([
                    'error' => 'Format de date invalide (format attendu: Y-m-d H:i:s)'
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $errors = $validator->validate($asset);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return new JsonResponse([
                'error' => 'Données invalides',
                'details' => $errorMessages
            ], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Actif modifié avec succès',
            'asset' => [
                'id' => $asset->getId(),
                'symbol' => $asset->getSymbol(),
                'name' => $asset->getName(),
                'quantity' => $asset->getQuantity(),
                'purchasePrice' => $asset->getPurchasePrice(),
                'purchaseDate' => $asset->getPurchaseDate()->format('Y-m-d H:i:s'),
            ]
        ]);
    }

    #[Route('/{id}', name: 'api_portfolio_delete', methods: ['DELETE'])]
    public function delete(
        int $id,
        CryptoAssetRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $asset = $repository->findOneBy(['id' => $id, 'user' => $user]);

        if (!$asset) {
            return new JsonResponse([
                'error' => 'Actif non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($asset);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Actif supprimé avec succès'
        ]);
    }
}

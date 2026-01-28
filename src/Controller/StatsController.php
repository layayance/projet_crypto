<?php

namespace App\Controller;

use App\Repository\CryptoAssetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/stats')]
#[IsGranted('ROLE_USER')]
class StatsController extends AbstractController
{
    #[Route('/portfolio/value', name: 'api_stats_portfolio_value', methods: ['GET'])]
    public function portfolioValue(CryptoAssetRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $assets = $repository->findBy(['user' => $user]);

        $totalValue = 0;
        $totalInvested = 0;

        foreach ($assets as $asset) {
            $invested = (float) $asset->getQuantity() * (float) $asset->getPurchasePrice();
            $totalInvested += $invested;
            // Note: Pour une valeur réelle, il faudrait récupérer le prix actuel depuis une API externe
            // Ici on utilise le prix d'achat comme valeur actuelle
            $totalValue += $invested;
        }

        return new JsonResponse([
            'totalValue' => number_format($totalValue, 2, '.', ''),
            'totalInvested' => number_format($totalInvested, 2, '.', ''),
            'profitLoss' => number_format($totalValue - $totalInvested, 2, '.', ''),
            'profitLossPercentage' => $totalInvested > 0 
                ? number_format((($totalValue - $totalInvested) / $totalInvested) * 100, 2, '.', '')
                : '0.00',
            'currency' => 'USD'
        ]);
    }

    #[Route('/portfolio/summary', name: 'api_stats_portfolio_summary', methods: ['GET'])]
    public function portfolioSummary(CryptoAssetRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $assets = $repository->findBy(['user' => $user]);

        $summary = [];
        $totalValue = 0;
        $totalInvested = 0;

        foreach ($assets as $asset) {
            $symbol = $asset->getSymbol();
            $quantity = (float) $asset->getQuantity();
            $purchasePrice = (float) $asset->getPurchasePrice();
            $invested = $quantity * $purchasePrice;
            
            // Note: Pour une valeur réelle, il faudrait récupérer le prix actuel depuis une API externe
            $currentValue = $invested; // Utilisation du prix d'achat comme valeur actuelle
            
            if (!isset($summary[$symbol])) {
                $summary[$symbol] = [
                    'symbol' => $symbol,
                    'name' => $asset->getName(),
                    'totalQuantity' => 0,
                    'totalInvested' => 0,
                    'currentValue' => 0,
                    'count' => 0
                ];
            }

            $summary[$symbol]['totalQuantity'] += $quantity;
            $summary[$symbol]['totalInvested'] += $invested;
            $summary[$symbol]['currentValue'] += $currentValue;
            $summary[$symbol]['count']++;

            $totalInvested += $invested;
            $totalValue += $currentValue;
        }

        // Calculer les pourcentages
        foreach ($summary as &$item) {
            $item['profitLoss'] = $item['currentValue'] - $item['totalInvested'];
            $item['profitLossPercentage'] = $item['totalInvested'] > 0 
                ? (($item['currentValue'] - $item['totalInvested']) / $item['totalInvested']) * 100 
                : 0;
            $item['portfolioPercentage'] = $totalValue > 0 
                ? ($item['currentValue'] / $totalValue) * 100 
                : 0;
        }

        return new JsonResponse([
            'summary' => array_values($summary),
            'totalAssets' => count($assets),
            'uniqueCryptos' => count($summary),
            'totalValue' => number_format($totalValue, 2, '.', ''),
            'totalInvested' => number_format($totalInvested, 2, '.', ''),
            'totalProfitLoss' => number_format($totalValue - $totalInvested, 2, '.', ''),
            'totalProfitLossPercentage' => $totalInvested > 0 
                ? number_format((($totalValue - $totalInvested) / $totalInvested) * 100, 2, '.', '')
                : '0.00'
        ]);
    }

    #[Route('/portfolio/history', name: 'api_stats_portfolio_history', methods: ['GET'])]
    public function portfolioHistory(
        Request $request,
        CryptoAssetRepository $repository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $user = $this->getUser();
        $assets = $repository->findBy(['user' => $user], ['purchaseDate' => 'ASC']);

        $history = [];
        $cumulativeInvested = 0;
        $cumulativeValue = 0;

        foreach ($assets as $asset) {
            $invested = (float) $asset->getQuantity() * (float) $asset->getPurchasePrice();
            $cumulativeInvested += $invested;
            // Note: Pour une valeur réelle, il faudrait récupérer le prix actuel depuis une API externe
            $cumulativeValue += $invested;

            $history[] = [
                'date' => $asset->getPurchaseDate()->format('Y-m-d'),
                'symbol' => $asset->getSymbol(),
                'name' => $asset->getName(),
                'quantity' => $asset->getQuantity(),
                'purchasePrice' => $asset->getPurchasePrice(),
                'invested' => number_format($invested, 2, '.', ''),
                'cumulativeInvested' => number_format($cumulativeInvested, 2, '.', ''),
                'cumulativeValue' => number_format($cumulativeValue, 2, '.', ''),
            ];
        }

        return new JsonResponse([
            'history' => $history,
            'totalEntries' => count($history)
        ]);
    }

    #[Route('/portfolio/distribution', name: 'api_stats_portfolio_distribution', methods: ['GET'])]
    public function portfolioDistribution(CryptoAssetRepository $repository): JsonResponse
    {
        $user = $this->getUser();
        $assets = $repository->findBy(['user' => $user]);

        $distribution = [];
        $totalValue = 0;

        foreach ($assets as $asset) {
            $symbol = $asset->getSymbol();
            $quantity = (float) $asset->getQuantity();
            $purchasePrice = (float) $asset->getPurchasePrice();
            $value = $quantity * $purchasePrice;

            if (!isset($distribution[$symbol])) {
                $distribution[$symbol] = [
                    'symbol' => $symbol,
                    'name' => $asset->getName(),
                    'value' => 0
                ];
            }

            $distribution[$symbol]['value'] += $value;
            $totalValue += $value;
        }

        // Calculer les pourcentages
        foreach ($distribution as &$item) {
            $item['percentage'] = $totalValue > 0 
                ? number_format(($item['value'] / $totalValue) * 100, 2, '.', '')
                : '0.00';
            $item['value'] = number_format($item['value'], 2, '.', '');
        }

        // Trier par valeur décroissante
        usort($distribution, function($a, $b) {
            return (float) $b['value'] <=> (float) $a['value'];
        });

        return new JsonResponse([
            'distribution' => $distribution,
            'totalValue' => number_format($totalValue, 2, '.', '')
        ]);
    }
}

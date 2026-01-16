<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Ticket;
use App\Entity\LigneTicket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/Dashboard', name: 'dashboard_index')]
    public function index(): Response
    {
        // --- Today range
        $start = new \DateTime('today 00:00:00');
        $end   = new \DateTime('today 23:59:59');

        // 1) Ventes du jour (sum ticket.total)
        $salesToday = (float) $this->em->createQueryBuilder()
            ->select('COALESCE(SUM(t.total), 0)')
            ->from(Ticket::class, 't')
            ->where('t.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        // 2) Tickets émis (count today)
        $ticketsCount = (int) $this->em->createQueryBuilder()
            ->select('COUNT(t.id)')
            ->from(Ticket::class, 't')
            ->where('t.date BETWEEN :start AND :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getSingleScalarResult();

        // 3) Produits en stock (count all products)
        $productsInStock = (int) $this->em->createQueryBuilder()
            ->select('COUNT(p.id)')
            ->from(Product::class, 'p')
            ->getQuery()
            ->getSingleScalarResult();

        // 4) Stock faible (<= threshold)
        $threshold = 6; // change like you want
        $lowStockProducts = $this->em->getRepository(Product::class)->createQueryBuilder('p')
            ->where('p.quantity <= :th')
            ->setParameter('th', $threshold)
            ->orderBy('p.quantity', 'ASC')
            ->getQuery()
            ->getResult();

        $lowStockCount = count($lowStockProducts);

        // 5) Top 3 produits vendus (based on LigneTicket)
        // If LigneTicket stores nomProduit + quantite:
        $topProducts = $this->em->createQueryBuilder()
            ->select('l.nomProduit AS name, SUM(l.quantite) AS qty')
            ->from(LigneTicket::class, 'l')
            ->groupBy('l.nomProduit')
            ->orderBy('qty', 'DESC')
            ->setMaxResults(3)
            ->getQuery()
            ->getArrayResult();

        return $this->render('dashboard/index.html.twig', [
            'salesToday' => round($salesToday, 3),
            'ticketsCount' => $ticketsCount,
            'productsInStock' => $productsInStock,
            'lowStockCount' => $lowStockCount,
            'topProducts' => $topProducts,
            'lowStockProducts' => $lowStockProducts,
            'threshold' => $threshold,
        ]);
    }
}

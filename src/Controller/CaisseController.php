<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Ticket;
use App\Entity\LigneTicket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/caisse', name: 'caisse_')]
class CaisseController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->em->getRepository(Category::class)->findAll();

        return $this->render('caisse/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/GetProductsByCategory', name: 'get_products', methods: ['GET'])]
    public function getProductsByCategory(Request $request): Response
    {
        $categoryId = (int) $request->query->get('categoryId');

        $products = $this->em->getRepository(Product::class)->findBy(
            ['category' => $categoryId],
            ['id' => 'DESC']
        );

        return $this->render('caisse/_products.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/Annuler', name: 'annuler', methods: ['GET'])]
    public function annuler(): Response
    {
        return $this->render('caisse/_ticket.html.twig');
    }
    #[Route('/Encaisser', name: 'encaisser', methods: ['POST'])]
public function encaisser(Request $request): JsonResponse
{
    $payload = json_decode((string) $request->getContent(), true);

    if (!is_array($payload) || count($payload) === 0) {
        return new JsonResponse(['error' => 'Ticket vide'], 400);
    }

    $ticket = new Ticket();
    $ticket->setDate(new \DateTime());

    $total = 0.0;

    foreach ($payload as $row) {
        $productId = (int) ($row['productId'] ?? 0);
        $qte = (int) ($row['qte'] ?? 0);

        if ($productId <= 0 || $qte <= 0) {
            continue;
        }

        /** @var Product|null $product */
        $product = $this->em->getRepository(Product::class)->find($productId);
        if (!$product) {
            return new JsonResponse(['error' => "Produit introuvable (id=$productId)"], 400);
        }

        if (!$product->isAvailable()) {
            return new JsonResponse(['error' => "Produit non disponible: ".$product->getProductName()], 400);
        }

        $stock = (int) $product->getProductQuantity();
        if ($stock < $qte) {
            return new JsonResponse(['error' => "Stock insuffisant: ".$product->getProductName()], 400);
        }

        // ✅ decrement stock
        $product->setProductQuantity($stock - $qte);

        // ✅ unit price from DB (more trustworthy than JS)
        $pu = (float) $product->getProductUnitPrice();
        $pt = round($qte * $pu, 3);

        $ligne = new LigneTicket();
        $ligne->setNomProduit($product->getProductName());
        $ligne->setQuantite($qte);
        $ligne->setPrixUnitaire(number_format(round($pu, 3), 3, '.', ''));
        $ligne->setPrixTotal(number_format($pt, 3, '.', ''));
        $ligne->setTicket($ticket);

        $this->em->persist($ligne);
        $total += $pt;
    }

    $ticket->setTotal(number_format(round($total, 3), 3, '.', ''));

    $this->em->persist($ticket);
    $this->em->flush(); // ✅ flush ticket + lignes + product updates

    return new JsonResponse(['ticketId' => $ticket->getId()]);
}



}

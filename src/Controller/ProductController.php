<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/product', name: 'product_')]
class ProductController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->em->getRepository(Product::class)->findAll();
        $categories = $this->em->getRepository(Category::class)->findAll();

        return $this->render('product/index.html.twig', [
            'products' => $products,
            'categories' => $categories, // like ViewBag.Categories
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET'])]
    public function create(): Response
    {
        $categories = $this->em->getRepository(Category::class)->findAll();

        return $this->render('product/create.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $productName = trim((string) $request->request->get('productName'));
        $productQuantity = (int) $request->request->get('productQuantity');
        $productUnitPrice = (string) $request->request->get('productUnitPrice'); // decimal stored as string
        $categoryId = (int) $request->request->get('categoryId');

        $category = $this->em->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            $this->addFlash('error', 'Category not found.');
            return $this->redirectToRoute('product_create');
        }

        $product = new Product();
        $product->setProductName($productName);
        $product->setProductQuantity($productQuantity);
        $product->setProductUnitPrice($productUnitPrice);
        $product->setCategory($category);

        $this->em->persist($product);
        $this->em->flush();

        return $this->redirectToRoute('product_index');
    }

    #[Route('/{id}/edit', name: 'edit_form', methods: ['GET'])]
    public function editForm(int $id): Response
    {
        $categories = $this->em->getRepository(Category::class)->findAll();
        $product = $this->em->getRepository(Product::class)->find($id);

        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'categories' => $categories,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['POST'])]
    public function edit(int $id, Request $request): Response
    {
        $product = $this->em->getRepository(Product::class)->find($id);
        if (!$product) {
            throw $this->createNotFoundException('Product not found');
        }

        $productName = trim((string) $request->request->get('productName'));
        $productQuantity = (int) $request->request->get('productQuantity');
        $productUnitPrice = (string) $request->request->get('productUnitPrice');
        $categoryId = (int) $request->request->get('categoryId');

        $category = $this->em->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            $this->addFlash('error', 'Category not found.');
            return $this->redirectToRoute('product_edit_form', ['id' => $id]);
        }

        $product->setProductName($productName);
        $product->setProductQuantity($productQuantity);
        $product->setProductUnitPrice($productUnitPrice);
        $product->setCategory($category);

        $this->em->flush();

        return $this->redirectToRoute('product_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id): Response
    {
        $product = $this->em->getRepository(Product::class)->find($id);

        if ($product) {
            $this->em->remove($product);
            $this->em->flush();
        }

        return $this->redirectToRoute('product_index');
    }
}

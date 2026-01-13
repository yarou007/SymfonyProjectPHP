<?php

namespace App\Controller;

use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/category', name: 'category_')]
class CategoryController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em) {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $categories = $this->em->getRepository(Category::class)->findAll();

        return $this->render('category/index.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/create', name: 'create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('category/create.html.twig');
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $name = trim((string) $request->request->get('CategoryName'));

        if ($name === '') {
            $this->addFlash('error', 'Category name is required.');
            return $this->redirectToRoute('category_create');
        }

        $category = new Category();
        $category->setName($name);

        $this->em->persist($category);
        $this->em->flush();

        return $this->redirectToRoute('category_index');
    }

    #[Route('/{id}/edit', name: 'edit_form', methods: ['GET'])]
    public function editForm(int $id): Response
    {
        $category = $this->em->getRepository(Category::class)->find($id);

        if (!$category) {
            return $this->render('category/edit.html.twig', [
                'category' => null,
            ]);
        }

        return $this->render('category/edit.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['POST'])]
    public function edit(int $id, Request $request): Response
    {
        $category = $this->em->getRepository(Category::class)->find($id);

        if ($category) {
            $name = trim((string) $request->request->get('CategoryName'));
            if ($name !== '') {
                $category->setName($name);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('category_index');
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(int $id, Request $request): Response
    {
        // CSRF protection (recommended)
        if (!$this->isCsrfTokenValid('delete_category_'.$id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $category = $this->em->getRepository(Category::class)->find($id);

        if ($category) {
            $this->em->remove($category);
            $this->em->flush();
        }

        return $this->redirectToRoute('category_index');
    }
}

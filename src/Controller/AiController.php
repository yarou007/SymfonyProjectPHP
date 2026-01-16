<?php

namespace App\Controller;

use App\Service\HuggingFaceClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AiController extends AbstractController
{
    public function __construct(private HuggingFaceClient $hf) {}

    #[Route('/ai', name: 'ai_page', methods: ['GET'])]
    public function page(): Response
    {
        return $this->render('ai/index.html.twig');
    }

    #[Route('/ai/ask', name: 'ai_ask', methods: ['POST'])]
    public function ask(Request $request): JsonResponse
    {
        $prompt = (string) $request->request->get('prompt', '');

        if (trim($prompt) === '') {
            return new JsonResponse(['answer' => 'Prompt vide'], 400);
        }

        try {
            $answer = $this->hf->chat($prompt);
            return new JsonResponse(['answer' => $answer]);
        } catch (\Throwable $e) {
            return new JsonResponse(['answer' => 'HF error: '.$e->getMessage()], 500);
        }
    }
}

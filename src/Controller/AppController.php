<?php

namespace App\Controller;

use App\Repository\CategoriaRepository;
use App\Repository\ProdutoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AppController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home(): Response
    {
        return $this->redirectToRoute('app_app');
    }

    #[Route('/app', name: 'app_app')]
    public function index(CategoriaRepository $categoriaRepository, ProdutoRepository $produtoRepository): Response
    {
        return $this->render('app/index.html.twig', [
            'totalCategorias' => $categoriaRepository->count([]),
            'totalProdutos' => $produtoRepository->count([]),
            'totalUnidades' => $produtoRepository->somarEstoque(),
        ]);
    }
}

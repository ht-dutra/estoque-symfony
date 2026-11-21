<?php

namespace App\Controller;

use App\Entity\Produto;
use App\Form\ProdutoType;
use App\Repository\ProdutoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/app/produtos')]
final class ProdutoController extends AbstractController
{
    #[Route('', name: 'app_produto_index', methods: ['GET'])]
    public function index(ProdutoRepository $produtoRepository): Response
    {
        return $this->render('produto/index.html.twig', [
            'produtos' => $produtoRepository->findAll(),
        ]);
    }

    #[Route('/cadastrar', name: 'app_produto_new', methods: ['GET'])]
    public function new(): Response
    {
        $produto = new Produto();

        return $this->render('produto/new.html.twig', [
            'produto' => $produto,
            'form' => $this->createForm(ProdutoType::class, $produto),
        ]);
    }

    #[Route('', name: 'app_produto_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produto = new Produto();
        $form = $this->createForm(ProdutoType::class, $produto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produto);
            $entityManager->flush();

            $this->addFlash('success', 'Produto cadastrado com sucesso.');

            return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produto/new.html.twig', [
            'produto' => $produto,
            'form' => $form,
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}', name: 'app_produto_edit', methods: ['GET'])]
    public function edit(Produto $produto): Response
    {
        return $this->render('produto/edit.html.twig', [
            'produto' => $produto,
            'form' => $this->createForm(ProdutoType::class, $produto),
        ]);
    }

    #[Route('/{id<\d+>}', name: 'app_produto_update', methods: ['POST'])]
    public function update(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProdutoType::class, $produto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produto atualizado com sucesso.');

            return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produto/edit.html.twig', [
            'produto' => $produto,
            'form' => $form,
        ])->setStatusCode($form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK);
    }

    #[Route('/{id<\d+>}/remover', name: 'app_produto_delete', methods: ['POST'])]
    public function delete(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        $this->validarToken($request, 'delete'.$produto->getId());

        $entityManager->remove($produto);
        $entityManager->flush();

        $this->addFlash('success', 'Produto excluído com sucesso.');

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id<\d+>}/adicionar', name: 'app_produto_add', methods: ['POST'])]
    public function add(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        $this->validarToken($request, 'estoque_adicionar'.$produto->getId());

        $produto->setQuantidade(($produto->getQuantidade() ?? 0) + 1);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Uma unidade de "%s" foi adicionada ao estoque.', $produto->getNome()));

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id<\d+>}/vender', name: 'app_produto_sell', methods: ['POST'])]
    public function sell(Request $request, Produto $produto, EntityManagerInterface $entityManager): Response
    {
        $this->validarToken($request, 'estoque_vender'.$produto->getId());

        if (($produto->getQuantidade() ?? 0) <= 0) {
            $this->addFlash('warning', sprintf('O estoque de "%s" já está zerado.', $produto->getNome()));

            return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
        }

        $produto->setQuantidade($produto->getQuantidade() - 1);
        $entityManager->flush();

        $this->addFlash('success', sprintf('Uma unidade de "%s" foi removida do estoque.', $produto->getNome()));

        return $this->redirectToRoute('app_produto_index', [], Response::HTTP_SEE_OTHER);
    }

    private function validarToken(Request $request, string $tokenId): void
    {
        if (!$this->isCsrfTokenValid($tokenId, $request->getPayload()->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF inválido.');
        }
    }
}

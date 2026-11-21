<?php

namespace App\Tests\Controller;

use App\Entity\Categoria;
use App\Entity\Produto;
use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EstoqueTest extends WebTestCase
{
    public function testLoginPageAndAccessControl(): void
    {
        $client = static::createClient();

        $client->request('GET', '/app');
        $this->assertResponseRedirects('/login');

        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Controle de Estoque');
    }

    public function testStockActionsNeverLeaveNegativeQuantity(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $sufixo = uniqid('', true);
        $usuario = (new Usuario())
            ->setEmail('teste-'.$sufixo.'@example.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('senha-de-teste');
        $categoria = (new Categoria())->setNome('Categoria '.$sufixo);
        $produto = (new Produto())
            ->setNome('Produto '.$sufixo)
            ->setDescricao('Produto criado pelo teste funcional')
            ->setValor('10.00')
            ->setQuantidade(0)
            ->setCategoria($categoria);

        $entityManager->persist($usuario);
        $entityManager->persist($categoria);
        $entityManager->persist($produto);
        $entityManager->flush();

        try {
            $client->loginUser($usuario);

            $crawler = $client->request('GET', '/app/produtos');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('body', $produto->getNome());
            $this->assertSelectorTextContains('body', '+1');

            $tokenAdicionar = $crawler
                ->filter('form[action="/app/produtos/'.$produto->getId().'/adicionar"] input[name="_token"]')
                ->attr('value');

            $client->request('POST', '/app/produtos/'.$produto->getId().'/adicionar', [
                '_token' => $tokenAdicionar,
            ]);
            $this->assertResponseRedirects('/app/produtos');
            $crawler = $client->followRedirect();
            $this->assertSelectorTextContains('.alert-success', 'adicionada ao estoque');

            $entityManager->clear();
            $this->assertSame(1, $entityManager->find(Produto::class, $produto->getId())->getQuantidade());

            $tokenVender = $crawler
                ->filter('form[action="/app/produtos/'.$produto->getId().'/vender"] input[name="_token"]')
                ->attr('value');

            $client->request('POST', '/app/produtos/'.$produto->getId().'/vender', [
                '_token' => $tokenVender,
            ]);
            $this->assertResponseRedirects('/app/produtos');
            $crawler = $client->followRedirect();
            $this->assertSelectorTextContains('.alert-success', 'removida do estoque');

            $entityManager->clear();
            $this->assertSame(0, $entityManager->find(Produto::class, $produto->getId())->getQuantidade());

            $tokenVender = $crawler
                ->filter('form[action="/app/produtos/'.$produto->getId().'/vender"] input[name="_token"]')
                ->attr('value');

            $client->request('POST', '/app/produtos/'.$produto->getId().'/vender', [
                '_token' => $tokenVender,
            ]);
            $this->assertResponseRedirects('/app/produtos');
            $client->followRedirect();
            $this->assertSelectorTextContains('.alert-warning', 'já está zerado');

            $entityManager->clear();
            $this->assertSame(0, $entityManager->find(Produto::class, $produto->getId())->getQuantidade());

            $client->request('POST', '/app/produtos/'.$produto->getId().'/adicionar', [
                '_token' => 'token-invalido',
            ]);
            $this->assertResponseStatusCodeSame(403);
        } finally {
            $entityManager->clear();
            foreach ([Produto::class => $produto->getId(), Categoria::class => $categoria->getId(), Usuario::class => $usuario->getId()] as $classe => $id) {
                if ($entidade = $entityManager->find($classe, $id)) {
                    $entityManager->remove($entidade);
                }
            }
            $entityManager->flush();
        }
    }

    public function testCategoryPagesAndDelete(): void
    {
        $client = static::createClient();
        $entityManager = static::getContainer()->get(EntityManagerInterface::class);

        $sufixo = uniqid('', true);
        $usuario = (new Usuario())
            ->setEmail('teste-categoria-'.$sufixo.'@example.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword('senha-de-teste');
        $categoria = (new Categoria())->setNome('Categoria '.$sufixo);

        $entityManager->persist($usuario);
        $entityManager->persist($categoria);
        $entityManager->flush();

        try {
            $client->loginUser($usuario);

            $client->request('GET', '/app/categorias');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('body', $categoria->getNome());

            $client->request('GET', '/app/categorias/new');
            $this->assertResponseIsSuccessful();
            $this->assertSelectorTextContains('h1', 'Nova categoria');

            $crawler = $client->request('GET', '/app/categorias/'.$categoria->getId());
            $tokenExcluir = $crawler
                ->filter('form[action="/app/categorias/'.$categoria->getId().'"] input[name="_token"]')
                ->attr('value');

            $client->request('POST', '/app/categorias/'.$categoria->getId(), [
                '_token' => $tokenExcluir,
            ]);
            $this->assertResponseRedirects('/app/categorias');

            $entityManager->clear();
            $this->assertNull($entityManager->find(Categoria::class, $categoria->getId()));
        } finally {
            $entityManager->clear();
            if ($entidade = $entityManager->find(Usuario::class, $usuario->getId())) {
                $entityManager->remove($entidade);
            }
            if ($entidade = $entityManager->find(Categoria::class, $categoria->getId())) {
                $entityManager->remove($entidade);
            }
            $entityManager->flush();
        }
    }
}

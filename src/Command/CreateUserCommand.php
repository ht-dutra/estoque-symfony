<?php

namespace App\Command;

use App\Entity\Usuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Cria um usuário para acessar o controle de estoque',
)]
final class CreateUserCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'E-mail do usuário')
            ->addArgument('senha', InputArgument::OPTIONAL, 'Senha do usuário (será solicitada se não for informada)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = trim((string) $input->getArgument('email'));
        $senha = $input->getArgument('senha') ?? $io->askHidden('Senha (mínimo 6 caracteres)');

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            $io->error('Informe um e-mail válido.');

            return Command::FAILURE;
        }

        if (!is_string($senha) || mb_strlen($senha) < 6) {
            $io->error('A senha deve ter pelo menos 6 caracteres.');

            return Command::FAILURE;
        }

        $repositorio = $this->entityManager->getRepository(Usuario::class);

        if ($repositorio->findOneBy(['email' => $email])) {
            $io->error(sprintf('Já existe um usuário com o e-mail "%s".', $email));

            return Command::FAILURE;
        }

        $usuario = new Usuario();
        $usuario->setEmail($email);
        $usuario->setRoles(['ROLE_USER']);
        $usuario->setPassword($this->passwordHasher->hashPassword($usuario, $senha));

        $this->entityManager->persist($usuario);
        $this->entityManager->flush();

        $io->success(sprintf('Usuário "%s" criado com sucesso.', $email));

        return Command::SUCCESS;
    }
}

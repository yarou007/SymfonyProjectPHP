<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a user and attach a DB Role entity (ROLE_USER or ROLE_ADMIN)'
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED)
            ->addArgument('password', InputArgument::REQUIRED)
            ->addArgument('role', InputArgument::OPTIONAL, 'ROLE_USER or ROLE_ADMIN', 'ROLE_USER');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (string) $input->getArgument('email');
        $plain = (string) $input->getArgument('password');
        $roleName = strtoupper((string) $input->getArgument('role'));

        if (!in_array($roleName, ['ROLE_USER', 'ROLE_ADMIN'], true)) {
            $output->writeln('<error>Role must be ROLE_USER or ROLE_ADMIN</error>');
            return Command::FAILURE;
        }

        $existingUser = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $output->writeln('<error>Email already exists</error>');
            return Command::FAILURE;
        }

        $role = $this->em->getRepository(Role::class)->findOneBy(['name' => $roleName]);
        if (!$role) {
            $output->writeln("<error>Role $roleName not found. Run: php bin/console app:seed-roles</error>");
            return Command::FAILURE;
        }

        $u = new User();
        $u->setEmail($email);
        $u->addRoleEntity($role);
        $u->setPassword($this->hasher->hashPassword($u, $plain));

        $this->em->persist($u);
        $this->em->flush();

        $output->writeln("<info>User created: $email with $roleName</info>");
        return Command::SUCCESS;
    }
}

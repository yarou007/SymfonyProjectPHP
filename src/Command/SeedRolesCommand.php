<?php

namespace App\Command;

use App\Entity\Role;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed-roles',
    description: 'Insert default roles into database'
)]
class SeedRolesCommand extends Command
{
    public function __construct(private EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(Role::class);

        foreach (['ROLE_USER', 'ROLE_ADMIN'] as $name) {
            $existing = $repo->findOneBy(['name' => $name]);
            if ($existing) {
                $output->writeln("<comment>$name already exists</comment>");
                continue;
            }

            $r = (new Role())->setName($name);
            $this->em->persist($r);
            $output->writeln("<info>Inserted $name</info>");
        }

        $this->em->flush();
        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:create-admin', description: 'Create the first administrator account.')]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email')
            ->addArgument('password', InputArgument::OPTIONAL, 'Password (omit it for a hidden prompt)')
            ->addArgument('name', InputArgument::OPTIONAL, 'Display name', 'Administrator');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = strtolower(trim((string) $input->getArgument('email')));
        $password = (string) ($input->getArgument('password') ?: $io->askHidden('Password'));
        $name = trim((string) $input->getArgument('name'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Enter a valid email address.');
            return Command::INVALID;
        }

        if (strlen($password) < 10) {
            $io->error('Use a password with at least 10 characters.');
            return Command::INVALID;
        }

        if ($this->users->findOneBy(['email' => $email])) {
            $io->error('A user with that email already exists.');
            return Command::FAILURE;
        }

        $user = (new User())
            ->setEmail($email)
            ->setName($name !== '' ? $name : 'Administrator')
            ->setRoles(['ROLE_SUPER_ADMIN']);

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $this->users->save($user, true);

        $io->success(sprintf('Admin %s created. Sign in at /login and open /admin.', $email));

        return Command::SUCCESS;
    }
}

<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserNotActivatedDeleteCommand extends Command
{
    protected static $defaultName = 'app:user-not-activated-delete';
    private $userRepository;
    private $manager;

    public function __construct(UserRepository $userRepository, ObjectManager $manager)
    {
        $this->userRepository = $userRepository;
        $this->manager = $manager;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Delete not activated users.')
            ->addArgument('email', InputArgument::OPTIONAL, 'Delete one user if an email is add')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Delete all not activated account.')
            ->setHelp('This command delete not activated users created one month ago. To delete all not activated users, add the option --all. Finally, you can also delete one user with argument email.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = strtolower($input->getArgument('email'));



        $users = $this->userRepository->findAllNotActivated();
        $onlyRealUsers = [];
        $tableBody = [];
        foreach($users as $user) {
            if(is_int(strpos($user->getEmail(), '@'))) {
                $onlyRealUsers[] = $user;
                $tableBody[] = [$user->getUsername(), $user->getEmail(), $user->getCreatedAt()->format('Y/m/d \\a\\t h:i:s')];
            }
        }
        //---------------- WTF Part -----------------------------------
        if($email) 
        {
            $user = $this->userRepository->findOneBy(['email' => $email]);
            if($user instanceof User) {
                $this->removeAndFlush($user);
                $io->success('The user has been delete with success');
            } else {
                $io->caution('No not activated account with email '.$email .' was found.');
            }
            return 0;
        }
        //---------------- End WTF Part ---------------------------------
        $io->table(
            ['Username', 'Email', 'Created At'],
            $tableBody
        );

        $usersCount = count($onlyRealUsers);
        $io->text('There are '.$usersCount.' users accounts which have not been activated.');

        if($usersCount === 0) {
            $io->caution('No users have been deleted.');
            return 0;
        }

        if ($input->getOption('all')) {
            foreach($onlyRealUsers as $user) {
                $this->removeAndFlush($user);
            }
            
            $io->success('All this users have been deleted with success.');
        
        } else {
            $oldUsers = [];
            foreach($onlyRealUsers as $user) {
                if(is_int(strpos($user->getEmail(), '@')) && $user->getCreatedAt() < new \DateTime('- 1 month')) {
                    $oldUsers[] = $user;
                }
            }
            $usersCount = count($oldUsers);
            $io->text('And belong them, '.$usersCount.' are created at least one month ago.');
    
            if($usersCount === 0) {
                $io->caution('No users have been deleted.');
                return 0;
            }

            foreach($oldUsers as $user) {
                $this->removeAndFlush($user);
            }
            
            $io->success($usersCount. ' users have been deleted with success.');
        }
        
        return 0;
    }

    private function removeAndFlush($user) {
        $this->manager->remove($user);
        $this->manager->flush();
    }
}

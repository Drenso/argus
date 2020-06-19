<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AddUserCommand extends Command
{
  protected static $defaultName = 'argus:add-user';
  /**
   * @var EntityManagerInterface
   */
  private $entityManager;
  /**
   * @var UserPasswordEncoderInterface
   */
  private $passwordEncoder;
  /**
   * @var UserProviderInterface
   */
  private $userProvider;
  /**
   * @var ValidatorInterface
   */
  private $validator;

  public function __construct(
      UserProviderInterface $userProvider, EntityManagerInterface $entityManager,
      ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
  {
    parent::__construct();

    $this->userProvider    = $userProvider;
    $this->entityManager   = $entityManager;
    $this->validator       = $validator;
    $this->passwordEncoder = $passwordEncoder;
  }

  protected function configure()
  {
    $this->setDescription('Add a new user to the syste');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $io->title('Add user');

    $email = $io->ask('What is the e-mail address of the user?', NULL, function ($user) {
      $constraints = [new NotBlank(), new Email()];
      if ($this->validator->validate($user, $constraints)->count() !== 0) {
        throw new RuntimeException('The supplied value is not a valid e-mail address');
      }

      try {
        $this->userProvider->loadUserByUsername($user);
        throw new RuntimeException('The supplied e-mail address is already in use');
      } catch (UsernameNotFoundException $e) {
        // This is actually okay
      }

      return $user;
    });

    $password = $io->askHidden('What will be the user password?', function ($value) {
      if ($this->validator->validate($value, new Length(['min' => 8]))->count() !== 0) {
        throw new RuntimeException('The supplied password is too short, it should at least contain 8 characters');
      }

      return $value;
    });

    $user = (new User())
        ->setEmail($email);
    $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
    $this->entityManager->persist($user);
    $this->entityManager->flush();

    $io->success(sprintf('Successfully added user "%s"', $email));

    return 0;
  }
}

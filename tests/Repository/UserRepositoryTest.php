<?php

namespace App\Tests\Repository;

use App\Entity\User;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserRepositoryTest extends KernelTestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

    /**
     * @var EntityManager
     */
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

//        $this->entityManager->run
//
//        self::runCommand('doctrine:database:drop --force');
//        self::runCommand('doctrine:database:create');
//        self::runCommand('doctrine:schema:create');
//        self::runCommand('doctrine:fixtures:load --append --no-interaction --fixtures=tests/AppBundle/DataFixtures/ORM');
//
//        self::runCommand('doctrine:database:drop --force');
    }

    public function testSave()
    {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        $this->entityManager
            ->getRepository(User::class)
            ->save($user, true)
        ;

        $createdUser = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => self::USER_EMAIL_TEST])
        ;

        $this->assertIsObject($createdUser);
        $this->assertSame(self::USER_NAME_TEST, $createdUser->getName());
        $this->assertSame(self::USER_EMAIL_TEST, $createdUser->getEmail());
        $this->assertSame(self::USER_PASSWORD_TEST, $createdUser->getPassword());
        $this->assertSame(self::USER_PHONE_TEST, $createdUser->getPhone());

        $this->entityManager->remove($user);
    }

//    public function testSearchByName()
//    {
//        $user = $this->entityManager
//            ->getRepository(User::class)
//            ->findOneBy(['name' => 'user name'])
//        ;
//
//        $this->assertSame(self::USER_NAME_TEST, $user->getName());
//        $this->assertSame(self::USER_EMAIL_TEST, $user->getEmail());
//        $this->assertSame(self::USER_PASSWORD_TEST, $user->getPassword());
//        $this->assertSame(self::USER_PHONE_TEST, $user->getPhone());
//    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}

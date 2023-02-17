<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Roles;
use App\Entity\User;
use App\Tests\Utils\ConstHelper;
use App\Tests\Utils\FixtureHelper;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserRepositoryTest extends KernelTestCase
{
    /**
     * @var EntityManager
     */
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $application = new Application($kernel);

        $command = $application->find('doctrine:migrations:migrate');
        $commandTester = new CommandTester($command);
        $commandTester->execute(['n']);

        $commandTester->assertCommandIsSuccessful();
    }

    public function testFind() {
        $user = FixtureHelper::addUser($this->entityManager);

        $this->assertIsObject($user);

        $response = $this->entityManager
            ->getRepository(User::class)
            ->find($user->getId())
        ;

        $this->assertSame($user->getName(), $response->getName());
        $this->assertSame($user->getEmail(), $response->getEmail());
        $this->assertSame($user->getPassword(), $response->getPassword());
        $this->assertSame($user->getPhone(), $response->getPhone());
        $this->assertSame($user->getRoles(), $response->getRoles());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testFindOneByName()
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $response = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['name' => ConstHelper::USER_NAME_TEST])
        ;

        $this->assertSame($user->getName(), $response->getName());
        $this->assertSame($user->getEmail(), $response->getEmail());
        $this->assertSame($user->getPassword(), $response->getPassword());
        $this->assertSame($user->getPhone(), $response->getPhone());
        $this->assertSame($user->getRoles(), $response->getRoles());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testFindOneByEmail()
    {
        $user = FixtureHelper::addUser($this->entityManager);

        $response = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => ConstHelper::USER_EMAIL_TEST]);

        $this->assertSame($user->getName(), $response->getName());
        $this->assertSame($user->getEmail(), $response->getEmail());
        $this->assertSame($user->getPassword(), $response->getPassword());
        $this->assertSame($user->getPhone(), $response->getPhone());
        $this->assertSame($user->getRoles(), $response->getRoles());

        FixtureHelper::removeUser($this->entityManager, $user);
    }

    public function testSaveUser() {
        $expectedRoles = [Roles::ROLE_ADMIN, Roles::ROLE_USER];
        $user = new User(
            ConstHelper::USER_NAME_TEST,
            ConstHelper::USER_EMAIL_TEST,
            ConstHelper::USER_PASSWORD_TEST,
            ConstHelper::USER_PHONE_TEST,
            $expectedRoles,
        );
        $timestamp = new \DateTime();
        $user->setCreatedAt($timestamp);
        $user->setUpdatedAt($timestamp);

        $createdUser = $this->entityManager
            ->getRepository(User::class)
            ->save($user, true);

        $this->assertSame($user->getName(), $createdUser->getName());
        $this->assertSame($user->getEmail(), $createdUser->getEmail());
        $this->assertSame($user->getPassword(), $createdUser->getPassword());
        $this->assertSame($user->getPhone(), $createdUser->getPhone());
        $this->assertSame($timestamp, $createdUser->getCreatedAt());
        $this->assertSame($timestamp, $createdUser->getUpdatedAt());
        $this->assertEquals($expectedRoles, $createdUser->getRoles());

        FixtureHelper::removeUser($this->entityManager, $createdUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }
}

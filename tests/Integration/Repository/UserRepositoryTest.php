<?php

namespace App\Tests\Integration\Repository;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class UserRepositoryTest extends KernelTestCase
{
    const USER_NAME_TEST = "name";
    const USER_EMAIL_TEST = "email@domain.com";
    const USER_PASSWORD_TEST = "password";
    const USER_PHONE_TEST = "910123123";

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
        $user = $this->addUser();

        $this->assertIsObject($user);

        $user = $this->entityManager
            ->getRepository(User::class)
            ->find($user->getId())
        ;

        $this->assertSame(self::USER_NAME_TEST, $user->getName());
        $this->assertSame(self::USER_EMAIL_TEST, $user->getEmail());
        $this->assertSame(self::USER_PASSWORD_TEST, $user->getPassword());
        $this->assertSame(self::USER_PHONE_TEST, $user->getPhone());

        $this->removeUser($user);
    }

    public function testFindOneByName()
    {
        $user = $this->addUser();

        $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['name' => self::USER_NAME_TEST])
        ;

        $this->assertSame(self::USER_NAME_TEST, $user->getName());
        $this->assertSame(self::USER_EMAIL_TEST, $user->getEmail());
        $this->assertSame(self::USER_PASSWORD_TEST, $user->getPassword());
        $this->assertSame(self::USER_PHONE_TEST, $user->getPhone());

        $this->removeUser($user);
    }

    public function testFindOneByEmail()
    {
        $user = $this->addUser();

        $response = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => self::USER_EMAIL_TEST])
        ;

        $this->assertSame($user->getName(), $response->getName());
        $this->assertSame($user->getEmail(), $response->getEmail());
        $this->assertSame($user->getPassword(), $response->getPassword());
        $this->assertSame($user->getPhone(), $response->getPhone());

        $this->removeUser($user);
    }

    public function testSaveUser() {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $timestamp = new \DateTime();
        $user->setCreatedAt($timestamp);
        $user->setUpdatedAt($timestamp);

        $createdUser = $this->entityManager
            ->getRepository(User::class)
            ->save($user, true)
        ;

        $this->assertSame(self::USER_NAME_TEST, $createdUser->getName());
        $this->assertSame(self::USER_EMAIL_TEST, $createdUser->getEmail());
        $this->assertSame(self::USER_PASSWORD_TEST, $createdUser->getPassword());
        $this->assertSame(self::USER_PHONE_TEST, $createdUser->getPhone());
        $this->assertSame($timestamp, $createdUser->getCreatedAt());
        $this->assertSame($timestamp, $createdUser->getUpdatedAt());

        $this->removeUser($createdUser);
    }

    public function testSaveUserFailWithDuplicatedEmail() {
        $user = $this->addUser();

        $conflictingUser = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $conflictingUser->setCreatedAt(new \DateTime());
        $conflictingUser->setUpdatedAt(new \DateTime());

        try {
            $this->expectException(UniqueConstraintViolationException::class);

            $this->entityManager
                ->getRepository(User::class)
                ->save($conflictingUser, true)
            ;
        } catch(UniqueConstraintViolationException $e) {

        } finally {
            $user = $this->entityManager
                ->getRepository(User::class)
                ->find($user->getId(), true)
            ;

            $this->removeUser($user);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // doing this is recommended to avoid memory leaks
        $this->entityManager->close();
        $this->entityManager = null;
    }

    protected function addUser(): ?User {
        $user = new User(
            self::USER_NAME_TEST,
            self::USER_EMAIL_TEST,
            self::USER_PASSWORD_TEST,
            self::USER_PHONE_TEST,
        );
        $user->setCreatedAt(new \DateTime());
        $user->setUpdatedAt(new \DateTime());

        return $this->entityManager
            ->getRepository(User::class)
            ->save($user, true)
        ;
    }

    protected function removeUser(User $user): void {
        $this->entityManager
            ->getRepository(User::class)
            ->remove($user, true)
        ;
    }
}

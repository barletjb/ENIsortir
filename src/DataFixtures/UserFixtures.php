<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');


        $admin = new User();
        $admin->setEmail("admin@campus-eni.fr");
        $admin->setPseudo("adminet");
        $admin->setRoles(["ROLE_ADMIN"]);
        $admin->setPrenom("Admin");
        $admin->setNom("Grand");
        $admin->setIsActif(true);
        $admin->setIsActive(true);
        $admin->setSite($this->getReference("site_0", Site::class));
        $admin->setPassword($this->hasher->hashPassword($admin, "123456"));
        $manager->persist($admin);


        for ($i = 0; $i <= 10; $i++) {
            $user = new User();
            $user->setEmail("user{$i}@campus-eni.fr");
            $user->setPseudo("user{$i}");
            $user->setPrenom($faker->firstName);
            $user->setNom($faker->lastName);
            $user->setRoles(["ROLE_USER"]);
            $user->setIsActif($faker->boolean(25));
            $user->setIsActive(true);

            $siteIndex = $faker->numberBetween(0, 2);
            $user->setSite($this->getReference("site_{$siteIndex}", Site::class));

            $user->setPassword($this->hasher->hashPassword($user, "123456"));
            $manager->persist($user);

            $this->addReference("user_{$i}", $user);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixtures::class,
        ];
    }
}
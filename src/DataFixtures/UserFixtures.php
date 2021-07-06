<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encode;
 
    public function __construct(UserPasswordHasherInterface $encoder) {
        $this->encode = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $user = new User();
        $user->setEmail('user@user.fr');
        
        // Encodage du mot de passe
        $password = $this->encode->hashPassword($user, "user");

        $user->setPassword($password);
        $user->setRoles(['ROLE_USER']);
        
        $manager->persist($user);
        

        // #######################
        $admin = new User();
        $admin->setEmail('admin@admin.fr');
        
        // Encodage du mot de passe
        $password = $this->encode->hashPassword($admin, "admin");

        $admin->setPassword($password);
        $admin->setRoles(['ROLE_ADMIN']);
        
        $manager->persist($admin);
        $manager->flush();
    }
}

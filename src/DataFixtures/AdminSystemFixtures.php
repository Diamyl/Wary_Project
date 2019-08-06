<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\UserSystem;

class AdminSystemFixtures extends Fixture
{

    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder2)
    {
        $this->encoder = $encoder2;
    }



    public function load(ObjectManager $manager)
    {
        $user = new UserSystem;
        $user->setEmail("admin2@gmail.com");
        $user->setPrenom("Admin2");
        $user->setNom("Admin2");
        $user->setCNI(18701992);
        $user->setTel("775556677");
        $user->setPassword($this->encoder->encodePassword($user, "admin2"));
        $user->setRoles(['ROLE_SUPERADMIN']);


        $manager->persist($user);
        $manager->flush();
    }
}
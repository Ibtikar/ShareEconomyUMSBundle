<?php

namespace Ibtikar\ShareEconomyUMSBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Ibtikar\ShareEconomyUMSBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $admin = new User();
        $admin->setEmail('admin@sofra.com');
        $admin->setEmailVerified(true);
        $admin->setFirstName('Sofra');
        $admin->setLastName('Admin');
        $admin->setSystemUser(true);
        $admin->setUserPassword('test1234');

        $manager->persist($admin);
        $manager->flush();
    }
}
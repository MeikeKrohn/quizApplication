<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 21.09.2018
 * Time: 18:51
 */

namespace App\DataFixtures;


use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class quizAppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $dummyUser = new User();
        $dummyUser->setEmail('meike.krohn@dummy.com');
        $dummyUser->setFirstname('Meike');
        $dummyUser->setLastname('Krohn');
        $dummyUser->setRole('ROLE_STUDENT');
        $dummyUser->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyUser->setUsername('meike1401');

        $manager->persist($dummyUser);
        $manager->flush();
    }
}
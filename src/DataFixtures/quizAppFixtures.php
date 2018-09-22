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
        $dummyStudent = new User();
        $dummyStudent->setEmail('student@dummy.com');
        $dummyStudent->setFirstname('Meike');
        $dummyStudent->setLastname('Krohn');
        $dummyStudent->setRole('ROLE_STUDENT');
        $dummyStudent->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyStudent->setUsername('meike1401');
        $manager->persist($dummyStudent);

        $dummyTeacher = new User();
        $dummyTeacher->setEmail('teacher@dummy.com');
        $dummyTeacher->setFirstname('Hans');
        $dummyTeacher->setLastname('Günther');
        $dummyTeacher->setRole('ROLE_TEACHER');
        $dummyTeacher->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyTeacher->setUsername('hans1001');
        $manager->persist($dummyTeacher);

        $manager->flush();
    }
}
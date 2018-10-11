<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 21.09.2018
 * Time: 18:51
 */

namespace App\DataFixtures;


use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserExam;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class quizAppFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        $dummyStudent1 = new User();
        $dummyStudent1->setEmail('meike@dummy.com');
        $dummyStudent1->setFirstname('Meike');
        $dummyStudent1->setLastname('Krohn');
        $dummyStudent1->setRole('ROLE_STUDENT');
        $dummyStudent1->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyStudent1->setUsername('meike1401');
        $manager->persist($dummyStudent1);

        $dummyStudent2 = new User();
        $dummyStudent2->setEmail('lisa@dummy.com');
        $dummyStudent2->setFirstname('Lisa');
        $dummyStudent2->setLastname('Simpson');
        $dummyStudent2->setRole('ROLE_STUDENT');
        $dummyStudent2->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyStudent2->setUsername('lisa2000');
        $manager->persist($dummyStudent2);

        $dummyStudent3 = new User();
        $dummyStudent3->setEmail('tom@dummy.com');
        $dummyStudent3->setFirstname('Tom');
        $dummyStudent3->setLastname('Tiger');
        $dummyStudent3->setRole('ROLE_STUDENT');
        $dummyStudent3->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyStudent3->setUsername('tom0007');
        $manager->persist($dummyStudent3);

        $dummyStudent4 = new User();
        $dummyStudent4->setEmail('frank@dummy.com');
        $dummyStudent4->setFirstname('Frank');
        $dummyStudent4->setLastname('Flowers');
        $dummyStudent4->setRole('ROLE_STUDENT');
        $dummyStudent4->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyStudent4->setUsername('frank3333');
        $manager->persist($dummyStudent4);

        $dummyTeacher1 = new User();
        $dummyTeacher1->setEmail('hans@dummy.com');
        $dummyTeacher1->setFirstname('Hans');
        $dummyTeacher1->setLastname('Hansson');
        $dummyTeacher1->setRole('ROLE_TEACHER');
        $dummyTeacher1->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyTeacher1->setUsername('hans1001');
        $manager->persist($dummyTeacher1);

        $dummyTeacher2 = new User();
        $dummyTeacher2->setEmail('claudia@dummy.com');
        $dummyTeacher2->setFirstname('Claudia');
        $dummyTeacher2->setLastname('Carlson');
        $dummyTeacher2->setRole('ROLE_TEACHER');
        $dummyTeacher2->setPassword(password_hash('test', PASSWORD_BCRYPT));
        $dummyTeacher2->setUsername('claudia5050');
        $manager->persist($dummyTeacher2);

        $category1 = new Category();
        $category1->setName("History");
        $manager->persist($category1);

        $category2 = new Category();
        $category2->setName("Biology");
        $manager->persist($category2);

        $category3 = new Category();
        $category3->setName("Geology");
        $manager->persist($category3);

        $manager->flush();
    }
}
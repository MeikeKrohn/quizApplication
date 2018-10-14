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
        $firstnames = ['James', 'John', 'Michael', 'David', 'Thomas', 'Matthew', 'Mary', 'Patricia', 'Jennifer', 'Elizabeth', 'Susan', 'Jessica'];

        $lastnames = ['Smith', 'Young', 'Stewart', 'Sanchez', 'Morris', 'Rogers', 'Miller', 'Davis', 'Taylor', 'Anderson', 'Thomas', 'Adams'];

        $usernameEndings = ['0001', '0002', '0003', '0004', '0005', '0006', '0007', '0008', '0009', '0010', '0011', '0012'];

        for ($i = 0; $i < 3; $i++) {
            $dummyTeacher = new User();
            $dummyTeacher->setFirstname($firstnames[$i]);
            $dummyTeacher->setLastname($lastnames[$i]);
            $dummyTeacher->setUsername($firstnames[$i] . $usernameEndings[$i]);
            $dummyTeacher->setEmail($firstnames[$i] . $usernameEndings[$i] . '@examit.com');
            $dummyTeacher->setRole('ROLE_TEACHER');
            $dummyTeacher->setPassword(password_hash('test', PASSWORD_BCRYPT));
            $manager->persist($dummyTeacher);
        }

        for ($i = 4; $i < 12; $i++) {
            $dummyStudent = new User();
            $dummyStudent->setFirstname($firstnames[$i]);
            $dummyStudent->setLastname($lastnames[$i]);
            $dummyStudent->setUsername($firstnames[$i] . $usernameEndings[$i]);
            $dummyStudent->setEmail($firstnames[$i] . $usernameEndings[$i] . '@examit.com');
            $dummyStudent->setRole('ROLE_STUDENT');
            $dummyStudent->setPassword(password_hash('test', PASSWORD_BCRYPT));
            $manager->persist($dummyStudent);
        }


        $categoryNames = ['History', 'Biology', 'Music', 'Chemistry', 'Religion'];

        for($i = 0; $i < sizeOf($categoryNames); $i++) {
            $dummyCategory = new Category();
            $dummyCategory->setName($categoryNames[$i]);
            $manager->persist($dummyCategory);
        }


        $manager->flush();
    }
}
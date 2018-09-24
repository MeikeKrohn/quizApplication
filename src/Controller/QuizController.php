<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 20.09.2018
 * Time: 18:41
 */

namespace App\Controller;


use App\Entity\Question;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizController extends AbstractController
{
    public function home()
    {
        $activeUser = $this->getUser();

        return $this->render('home.html.twig', array('activeUser' => $activeUser));
    }

    public function listQuestions()
    {
        $activeUser = $this->getUser();

        $questions = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array('owner' => $activeUser));


        return $this->render('teacher/listQuestions.html.twig', array('activeUser' => $activeUser, 'questions' => $questions));
    }
}
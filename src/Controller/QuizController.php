<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 20.09.2018
 * Time: 18:41
 */

namespace App\Controller;


use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizController extends AbstractController
{
    public function home()
    {
        $activeUser = $this->getUser();

        return $this->render('home.html.twig', array('activeUser' => $activeUser));
    }

    public function createQuestion()
    {
        $activeUser = $this->getUser();

        return $this->render('teacher/createQuestion.html.twig', array('activeUser' => $activeUser));
    }
}
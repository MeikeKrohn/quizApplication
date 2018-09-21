<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 20.09.2018
 * Time: 18:41
 */

namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class QuizController extends AbstractController
{
    public function home()
    {
        return $this->render('test.html.twig');
    }
}
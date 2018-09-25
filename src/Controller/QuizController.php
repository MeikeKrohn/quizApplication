<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 20.09.2018
 * Time: 18:41
 */

namespace App\Controller;


use App\Entity\Category;
use App\Entity\Question;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        $questions = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array('owner' => $activeUser));


        return $this->render('teacher/listQuestions.html.twig',
            array('activeUser' => $activeUser, 'categories' => $categories, 'questions' => $questions));
    }

    public function createQuestionForm(Request $request)
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        $newQuestion = new Question();
        $form = $this->createFormBuilder($newQuestion)
            ->add('category',EntityType::class, array(
                'class' => Category::class,
                'choice_label' => 'name'))
            ->add('questionText', TextType::class)
            ->add('save', SubmitType::class, array(
                'label' => 'Save Question'))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $newQuestion = $form->getData();
            $newQuestion->setOwner($activeUser);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($newQuestion);
            $entityManager->flush();

            return $this->redirectToRoute('listQuestions');
        }

        $questions = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array('owner' => $activeUser));

        return $this->render('teacher/createQuestion.html.twig',
            array('activeUser' => $activeUser,
                'questions' => $questions,
                'addNewQuestionForm' => $form->createView()));

    }

    public function deleteQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush();

        return new Response();
    }
}
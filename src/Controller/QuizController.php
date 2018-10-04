<?php
/**
 * Created by PhpStorm.
 * User: meike
 * Date: 20.09.2018
 * Time: 18:41
 */

namespace App\Controller;


use App\Entity\Answer;
use App\Entity\Category;
use App\Entity\Exam;
use App\Entity\Question;
use App\Entity\User;
use App\Entity\UserExam;
use App\Form\Type\QuestionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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

    public function deleteQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush();

        return new Response();
    }

    public function createQuestion(Request $request)
    {
        $activeUser = $this->getUser();
        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $question = new Question();
        $question->setOwner($activeUser);

        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        error_log($data['questionText']);
        error_log($data['category']);
        error_log($data['answers'][0]['answerText']);


        if($data != null) {
            $entityManager = $this->getDoctrine()->getManager();

            $categoryId = intval($data['category']);
            $questionText = (string) $data['questionText'];

            $assignedCategory = $this->getDoctrine()->getRepository(Category::class)->find($categoryId);

            $question->setQuestionText($questionText);
            $question->setCategory($assignedCategory);

            for ($i = 0; $i < sizeof($data['answers']); $i++) {

                $answerText = (string) $data['answers'][$i]['answerText'];
                $newAnswer = new Answer();
                $newAnswer->setQuestion($question);
                $newAnswer->setAnswerText($answerText);
                $newAnswer->setIsCorrect($data['answers'][$i]['isCorrect']);
                $question->addAnswer($newAnswer);
                $entityManager->persist($newAnswer);
            }

            $entityManager->persist($question);
            $entityManager->flush();
        }

        return $this->render('teacher/createQuestion.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories
                ));

    }

    public function editQuestion(Request $request, $questionId)
    {
        $activeUser = $this->getUser();

        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        error_log($data['questionText']);
        error_log($data['category']);
        error_log($data['answers'][0]['answerText']);

        if($data != null) {
            $entityManager = $this->getDoctrine()->getManager();

            $categoryId = intval($data['category']);
            $questionText = (string) $data['questionText'];

            $assignedCategory = $this->getDoctrine()->getRepository(Category::class)->find($categoryId);

            $question->setQuestionText($questionText);
            $question->setCategory($assignedCategory);
            $entityManager->persist($question);

            for ($i = 0; $i < sizeof($data['answers']); $i++) {

                $answerText = (string) $data['answers'][$i]['answerText'];
                $newAnswer = new Answer();
                $newAnswer->setQuestion($question);
                $newAnswer->setAnswerText($answerText);
                $newAnswer->setIsCorrect($data['answers'][$i]['isCorrect']);
                $entityManager->persist($newAnswer);

            }
            $entityManager->flush();
        }


        return $this->render('teacher/editQuestion.html.twig',
            array(
                'activeUser' => $activeUser,
                'question' => $question,
                'categories' => $categories
            ));
    }

    public function deleteAnswer($answerId)
    {
        $answer = $this->getDoctrine()->getRepository(Answer::class)->find($answerId);
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($answer);
        $entityManager->flush();

        return new Response();

    }

    public function listExams()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        $exams = $this->getDoctrine()
            ->getRepository(Exam::class)->findBy(array('owner' => $activeUser));


        return $this->render('teacher/listExams.html.twig',
            array('activeUser' => $activeUser, 'categories' => $categories, 'exams' => $exams));
    }

    public function createExam(Request $request)
    {
        $activeUser = $this->getUser();

        $exam = new Exam();
        $exam->setOwner($activeUser);

        $form = $this->createFormBuilder($exam)
            ->add('name', TextType::class)
            ->add('category', EntityType::class, array(
                'class' => Category::class,
                'choice_label' => 'name'))
            ->add('save', SubmitType::class, array(
                'label' => 'Save and Continue'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exam = $form->getData();
            $exam->setOwner($activeUser);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exam);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('addQuestionsToExam', array('examId' => $exam->getId())));
        }

        return $this->render('teacher/createExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'form' => $form->createView()));
    }

    public function addQuestionsToExam(Request $request, $examId)
    {
        $activeUser = $this->getUser();
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(array('category' => $exam->getCategory()));

        $form = $this->createFormBuilder($exam)
            ->add('questions', EntityType::class, array(
                'class' => Question::class,
                'choices' => $questions,
                'choice_label' => 'questionText',
                'multiple' => true,
                'by_reference' => false,
            ))
            ->add('save', SubmitType::class, array(
                'label' => 'Save and Continue'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exam = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exam);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('addStudentsToExam', array('examId' => $exam->getId())));
        }

        return $this->render('teacher/addQuestionsToExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'questions' => $questions,
                'exam' => $exam,
                'form' => $form->createView()));
    }

    public function addStudentsToExam(Request $request, $examId)
    {
        $activeUser = $this->getUser();
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);
        $students = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));

        $entityManager = $this->getDoctrine()->getManager();

        error_log("HELLO MEIKE");

        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        $entityManager = $this->getDoctrine()->getManager();

        for ($i = 0; $i < sizeof($data['students']); $i++) {
            $student = $this->getDoctrine()->getRepository(User::class)->find($data['students'][$i]);
            $userExam = new UserExam();
            $userExam->setExam($exam);
            $userExam->setUser($student);
            $entityManager->persist($userExam);
        }
        $entityManager->flush();


        return $this->render('teacher/addStudentsToExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'students' => $students,
                'examId' => $examId,
            ));
    }

    public function editExam(Request $request, $examId)
    {
        $activeUser = $this->getUser();
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(array('category' => $exam->getCategory()));
        $students = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));

        $form = $this->createFormBuilder($exam)
            ->add('name', TextType::class)
            ->add('category', EntityType::class, array(
                'class' => Category::class,
                'choice_label' => 'name'))
            ->add('questions', EntityType::class, array(
                'class' => Question::class,
                'choices' => $questions,
                'choice_label' => 'questionText',
                'multiple' => true,
                'by_reference' => false,
            ))
            ->add('save', SubmitType::class, array('label' => 'Save Exam'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exam = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exam);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('editExam', array('examId' => $exam->getId())));
        }

        return $this->render('teacher/editExam.html.twig', array(
            'activeUser' => $activeUser,
            'exam' => $exam,
            'students' => $students,
            'form' => $form->createView()
        ));

    }

    public function deleteExam($examId)
    {
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);
        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($exam);
        $entityManager->flush();

        return new Response();

    }

    public function listAvailableExams()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        $userExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array(
            'user' => $activeUser,
            'result' => null
        ));

        $availableExams = [];

        foreach ($userExams as $userExam) {
            array_push($availableExams, $userExam->getExam());
        }

        return $this->render('student/listAvailableExams.html.twig',
            array('activeUser' => $activeUser,
                'categories' => $categories,
                'userExams' => $userExams));
    }

    public function takeExam(Request $request, $userExamId)
    {
        $activeUser = $this->getUser();

        $userExam = $this->getDoctrine()->getRepository(UserExam::class)->find($userExamId);

        return $this->render('student/takeExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'userExam' => $userExam,
            ));
    }

    public function showStudentResults()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $userExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('user' => $activeUser));

        return $this->render('student/showResults.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories,
                'userExams' => $userExams
            ));
    }

    public function showTeacherResultsList()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $exams = $this->getDoctrine()->getRepository(Exam::class)->findBy(array('owner' => $activeUser));

        $userExams = $this->getDoctrine()->getRepository(UserExam::class)->findAll();

        return $this->render('teacher/showResultsOverview.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories,
                'exams' => $exams,
                'userExams' => $userExams
            ));
    }
}
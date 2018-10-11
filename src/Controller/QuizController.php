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
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
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

    public function createQuestion(Request $request)
    {
        $activeUser = $this->getUser();

        $question = new Question();
        $question->setOwner($activeUser);

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $question = $form->getData();
            $entityManager->persist($question);

            foreach ($question->getAnswers() as $answer) {
                error_log($answer->getId());
                $answer->setQuestion($question);
                $entityManager->persist($answer);
            }
            $entityManager->flush();

            return $this->redirectToRoute('listQuestions');
        }

        return $this->render('teacher/createQuestion.html.twig',
            array(
                'activeUser' => $activeUser,
                'form' => $form->createView()));

    }

    public function editQuestion(Request $request, $questionId)
    {
        $activeUser = $this->getUser();

        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $answers = $question->getAnswers();

        $form = $this->createForm(QuestionType::class, $question);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $question = $form->getData();
            $entityManager->persist($question);

            foreach ($question->getAnswers() as $answer) {
                $answer->setQuestion($question);
                $entityManager->persist($answer);
            }
            $entityManager->flush();

            return $this->redirect($this->generateUrl('editQuestion', array('questionId' => $question->getId())));
        }

        return $this->render('teacher/editQuestion.html.twig',
            array(
                'activeUser' => $activeUser,
                'question' => $question,
                'answers' => $answers,
                'form' => $form->createView()));
    }

    public function deleteQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($question);
        $entityManager->flush();

        return new Response();
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
            ->add('isRandomExam', ChoiceType::class, array(
                'choices' => array(
                    'Choose Questions manually' => false,
                    'Choose Questions randomly' => true),
                'multiple' => false,
                'expanded' => true,
                'label' => 'Exam Type'
            ))
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

            if($exam->getIsRandomExam()) {
                return $this->redirect($this->generateUrl('addStudentsToExam', array('examId' => $exam->getId())));
            } else {
                return $this->redirect($this->generateUrl('addQuestionsToExam', array('examId' => $exam->getId())));
            }

            /*


            return $this->redirect($this->generateUrl('addQuestionsToExam', array('examId' => $exam->getId())));
            */
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
            ->add('random', ButtonType::class, array(
                'label' => 'Choose randomly',
                'attr' => array('class' => 'chooseRandomQuestionsButton')
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

        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

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

    public function editExamQuestions(Request $request, $examId)
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
                'by_reference' => false))
            ->add('random', ButtonType::class, array(
                'label' => 'Choose randomly',
                'attr' => array('class' => 'chooseRandomQuestionsButton')))
            ->add('save', SubmitType::class, array('label' => 'Save Exam'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $exam = $form->getData();
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($exam);

            $entityManager->flush();

            return $this->redirect($this->generateUrl('editExamQuestions', array('examId' => $exam->getId())));
        }

        return $this->render('teacher/editExamQuestions.html.twig', array(
            'activeUser' => $activeUser,
            'exam' => $exam,
            'students' => $students,
            'form' => $form->createView()
        ));

    }

    /**
     * Update the information on which students shall be assigned to a certain exam.
     *
     * @param Request $request
     * @param $examId
     * @return Response
     */
    public function editExamStudents(Request $request, $examId)
    {
        $activeUser = $this->getUser();
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);
        $allStudents = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));
        $existingUserExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('exam' => $exam));


        // Filter those students who already took the exam and should thus not be unassigned from exam
        $studentsWhoTookExam = [];
        foreach ($existingUserExams as $ue) {
            if ($ue->getResult() !== null) {
                array_push($studentsWhoTookExam, $ue->getUser());
            }
        }

        // Define an array which contains all Students already assigned to the exam in question
        $assignedStudents = [];
        foreach ($existingUserExams as $ue) {
            array_push($assignedStudents, $ue->getUser());
        }

        $entityManager = $this->getDoctrine()->getManager();

        // Converting the received JSON object into a PHP object
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        if ($data != null) {

            // Array for new UserExams (those which need to be created, not updated)
            $newUserExams = [];

            for ($i = 0; $i < sizeof($data['students']); $i++) {
                $student = $this->getDoctrine()->getRepository(User::class)->find($data['students'][$i]);

                // Check if there already is a UserExam for this student-exam-combination
                $correspondingUserExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('exam' => $exam, 'user' => $student));

                // Convert the received array into an object
                foreach ($correspondingUserExams as $item) {
                    $correspondingUserExam = $item;
                }

                // Create a new UserExam entry if there hasn't been one yet
                if (!$correspondingUserExams) {
                    $correspondingUserExam = new UserExam();
                    $correspondingUserExam->setUser($student);
                    $correspondingUserExam->setExam($exam);
                    $entityManager->persist($correspondingUserExam);
                }

                // Put either the "old" or newly created UserExam into the newUserExams-Array
                array_push($newUserExams, $correspondingUserExam);
            }

            // Delete the UserExams that are no longer required
            for ($i = 0; $i < sizeof($existingUserExams); $i++) {
                for ($j = 0; $j < sizeof($newUserExams); $j++) {
                    if ($existingUserExams[$i] == $newUserExams[$j] || $existingUserExams[$i]->getResult() != null) {
                        break;
                    }
                    if (sizeof($newUserExams) - 1 == $j) {
                        $entityManager->remove($existingUserExams[$i]);
                    }
                }

            }

            $entityManager->flush();
        }

        return $this->render('teacher/editExamStudents.html.twig', array(
            'activeUser' => $activeUser,
            'exam' => $exam,
            'allStudents' => $allStudents,
            'assignedStudents' => $assignedStudents,
            'studentsWhoTookExam' => $studentsWhoTookExam,
            'userExams' => $existingUserExams
        ));
    }

    function compare_values($input1, $input2)
    {
        return $input1->getId() - $input2->getId();
    }

    public function compare_userExams($obj_a, $obj_b)
    {
        $a = $obj_a->getId();
        $b = $obj_b->getId();
        return $a - $b;
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

        $chosenQuestions = [];

        if($userExam->getExam()->getIsRandomExam()) {
            $allQuestions = $this->getDoctrine()->getRepository(Question::class)->findBy(array('category' => $userExam->getExam()->getCategory()));

            $max = mt_rand(1, sizeOf($allQuestions));

            shuffle($allQuestions);
            $chosenQuestions = array_slice($allQuestions, 0, $max);
        }


        $entityManager = $this->getDoctrine()->getManager();

        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        if ($data != null) {
            $allCorrectAnswers = 0;
            $answerCounter = 0;

            for ($i = 0; $i < sizeof($data['givenAnswers']); $i++) {
                $answer = $this->getDoctrine()->getRepository(Answer::class)->find($data['givenAnswers'][$i]['answerId']);

                if ($answer->getIsCorrect()) {
                    $allCorrectAnswers = $allCorrectAnswers + 2;
                    if ($data['givenAnswers'][$i]['isChecked']) {
                        $answerCounter = $answerCounter + 2;
                        $userExam->addGivenAnswer($answer);
                    }
                } else {
                    if ($data['givenAnswers'][$i]['isChecked']) {
                        $answerCounter--;
                        $userExam->addGivenAnswer($answer);
                    }
                }
            }

            foreach($userExam->getGivenAnswers() as $answer) {
                $answer->addUserExam($userExam);
                $entityManager->persist($answer);
            }

            if ($answerCounter >= 0) {
                $result = 100 / $allCorrectAnswers * $answerCounter;
            } else {
                $result = 0;
            }

            $userExam->setResult($result);

            $entityManager->flush();
        }

        return $this->render('student/takeExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'randomQuestions' => $chosenQuestions,
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

    public function showTeacherDetailedResult($userExamId)
    {
        $activeUser = $this->getUser();

        $userExam = $this->getDoctrine()->getRepository(UserExam::class)->find($userExamId);

        $questions = [];

        if($userExam->getExam()->getIsRandomExam()) {
            $givenAnswers = $userExam->getGivenAnswers();
            foreach($givenAnswers as $answer) {
                if (!in_array($answer->getQuestion(), $questions)) {
                    array_push($questions, $answer->getQuestion());
                }
            }
        }

        return $this->render('teacher/showDetailedResult.html.twig',
            array(
                'activeUser' => $activeUser,
                'userExam' => $userExam,
                'questions' => $questions
            ));
    }
}

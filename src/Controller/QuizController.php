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
use App\Form\Type\ExamType;
use App\Form\Type\QuestionType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * Controller used to manage Questions, Exams and Exam-Results for Teachers and Students
 * @author Meike Krohn
 * @package App\Controller
 */
class QuizController extends AbstractController
{

    /**
     * Welcome-Page displayed if the user logs in
     * @return Response
     */
    public function home()
    {
        $activeUser = $this->getUser();

        return $this->render('home.html.twig', array('activeUser' => $activeUser));
    }

    /**
     * Display list of available/created Questions in the Teacher-View
     * @return Response
     */
    public function listQuestions()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        // Select Questions that belong to the currently activeUser from the database
        $questions = $this->getDoctrine()
            ->getRepository(Question::class)->findBy(array('owner' => $activeUser));

        return $this->render('teacher/listQuestions.html.twig',
            array('activeUser' => $activeUser, 'categories' => $categories, 'questions' => $questions));
    }

    /**
     * Handle form for the creation of a Question in the Teacher-View
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createQuestion(Request $request)
    {
        $activeUser = $this->getUser();

        $question = new Question();
        $question->setOwner($activeUser);

        $form = $this->createForm(QuestionType::class, $question);

        // Inspecting the given Request object
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager = $this->getDoctrine()->getManager();
            $question = $form->getData();
            $entityManager->persist($question);

            // Mark the relation between each newly created Answer
            // and the new Question as persistent in the database
            foreach ($question->getAnswers() as $answer) {
                $answer->setQuestion($question);
                $entityManager->persist($answer);
            }

            $entityManager->flush();

            return $this->redirectToRoute('listQuestions');
        }

        return $this->render('teacher/createQuestion.html.twig',
            array(
                'activeUser' => $activeUser,
                'form' => $form->createView()
            ));

    }

    /**
     * Handle form for updating a Question in the Teacher-View
     * @param Request $request
     * @param $questionId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
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

            // Mark the relation between each new/updated Answer
            // and the updated Question as persistent in the database
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

    /**
     * Handle the deletion of a Question in the list of available/created Questions in the Teacher-View
     * @param $questionId
     * @return Response
     */
    public function deleteQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($question);

        $entityManager->flush();

        return new Response();
    }

    /**
     * Handle the deletion of an Answer attached to a Question in the Edit-Question-Form (Teacher-View)
     * @param $answerId
     * @return Response
     */
    public function deleteAnswer($answerId)
    {
        $answer = $this->getDoctrine()->getRepository(Answer::class)->find($answerId);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($answer);

        $entityManager->flush();

        return new Response();
    }

    /**
     * Display list of available/created Exams in the Teacher-View
     * @return Response
     */
    public function listExams()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        // Select all Exams belonging to the currently activeUser from the database
        $exams = $this->getDoctrine()
            ->getRepository(Exam::class)->findBy(array('owner' => $activeUser));

        $availableCategories = [];

        // Make a selection of those Categories for which the activeUser has Questions
        // to display a warning if the activeUser has a random Exam in a Category
        // for which he does not have any Questions
        foreach ($activeUser->getQuestions() as $question) {
            array_push($availableCategories, $question->getCategory());
        }

        return $this->render('teacher/listExams.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories,
                'exams' => $exams,
                'availableCategories' => $availableCategories
            ));
    }

    /**
     * Handle form for the creation of an Exam in the Teacher-View. Teacher is able to add a name,
     * choose a Category and choose whether Questions for the Exam shall be selected manually or randomly
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function createExam(Request $request)
    {
        $activeUser = $this->getUser();

        $exam = new Exam();
        $exam->setOwner($activeUser);

        $form = $this->createFormBuilder($exam)
            ->add('name', TextType::class, array(
                'trim' => true
            ))
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

            // If the activeUser chose to create a random Exam he is directly redirected
            // to the page where he can add Students to the created Exam, else he is redirected
            // to the page where he can choose Questions for his new Exam
            if ($exam->getIsRandomExam()) {
                return $this->redirect($this->generateUrl('addStudentsToExam', array('examId' => $exam->getId())));
            } else {
                return $this->redirect($this->generateUrl('addQuestionsToExam', array('examId' => $exam->getId())));
            }
        }

        return $this->render('teacher/createExam.html.twig',
            array(
                'activeUser' => $activeUser,
                'form' => $form->createView()));
    }

    /**
     * Handle Form for the attachment of Questions to a newly created Exam in the Teacher-View.
     * Is only available if the Exam is not a random Exam.
     * @param Request $request
     * @param $examId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function addQuestionsToExam(Request $request, $examId)
    {
        $activeUser = $this->getUser();

        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);

        // Select those Questions that belong to the activeUser
        // and which have the same Category as the new Exam from the database
        $questions = $this->getDoctrine()->getRepository(Question::class)->findBy(array('category' => $exam->getCategory(), 'owner' => $activeUser));

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

    /**
     * Handle Form for the assignment of Students to a newly created Exam in the Teacher-View.
     * @param Request $request
     * @param $examId
     * @return Response
     */
    public function addStudentsToExam(Request $request, $examId)
    {
        $activeUser = $this->getUser();

        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);

        $students = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));

        $entityManager = $this->getDoctrine()->getManager();

        // Transform the received Request, which is a JSON-String, into a PHP object
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        // Iterate over the array of Students
        // (containing those User-Ids that the activeUser chose to be assigned to the new Exam)
        // and create a new UserExam-Object for each selected Student
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
                'exam' => $exam
            ));
    }

    /**
     * Handle Form for updating a the Questions attached to an Exam in the Teacher-View
     * @param Request $request
     * @param $examId
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function editExamQuestions(Request $request, $examId)
    {
        $activeUser = $this->getUser();

        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);

        $students = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));

        $form = $this->createForm(ExamType::class, $exam);

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
     * Handle Form for updating the Students assigned to an Exam in the Teacher-View
     * @param Request $request
     * @param $examId
     * @return Response
     */
    public function editExamStudents(Request $request, $examId)
    {
        $activeUser = $this->getUser();

        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);

        $allStudents = $this->getDoctrine()->getRepository(User::class)->findBy(array('role' => 'ROLE_STUDENT'));

        // Select the UserExams to the $exam already existing in the database
        // to be displayed in the Student-Selection-List
        $existingUserExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('exam' => $exam));

        // Filter those students who already took the Exam
        // to display a warning to the activeUser
        $studentsWhoTookExam = [];
        foreach ($existingUserExams as $ue) {
            if ($ue->getResult() !== null) {
                array_push($studentsWhoTookExam, $ue->getUser());
            }
        }

        // Define an array which contains all Students already assigned to the $exam
        $assignedStudents = [];
        foreach ($existingUserExams as $ue) {
            array_push($assignedStudents, $ue->getUser());
        }

        $entityManager = $this->getDoctrine()->getManager();

        // Converting the received JSON object into a PHP object
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        if ($data != null) {

            // Array for new UserExams (which need to be created, not updated)
            $newUserExams = [];

            // Iterate over the array of Students (contains User-Ids of Students
            // that have been selected by the activeUser
            for ($i = 0; $i < sizeof($data['students']); $i++) {

                $student = $this->getDoctrine()->getRepository(User::class)->find($data['students'][$i]);

                // Check if there already is a UserExam for this Student-Exam-combination
                $correspondingUserExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('exam' => $exam, 'user' => $student));

                // Convert the received array into an object
                foreach ($correspondingUserExams as $item) {
                    $correspondingUserExam = $item;
                }

                // If there is no existing UserExam-Object yet, create a new UserExam entry
                if (!$correspondingUserExams) {
                    $correspondingUserExam = new UserExam();
                    $correspondingUserExam->setUser($student);
                    $correspondingUserExam->setExam($exam);
                    $entityManager->persist($correspondingUserExam);
                }

                // Put either the already existing ("old") or newly created UserExam into an Array
                array_push($newUserExams, $correspondingUserExam);

            }

            // Delete those UserExams from the database
            // which have been de-selected by the activeUser
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

    /**
     * Handle the deletion of an Exam in the list of available/created Exams in the Teacher-View
     * @param $examId
     * @return Response
     */
    public function deleteExam($examId)
    {
        $exam = $this->getDoctrine()->getRepository(Exam::class)->find($examId);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($exam);

        $entityManager->flush();

        return new Response();

    }

    /**
     * Display list of all students' results in created exams (Teacher-View)
     * @return Response
     */
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

    /**
     * Display detailed result of a student in an Exam (Teacher-View)
     * @param $userExamId
     * @return Response
     */
    public function showTeacherDetailedResult($userExamId)
    {
        $activeUser = $this->getUser();

        $userExam = $this->getDoctrine()->getRepository(UserExam::class)->find($userExamId);

        // In case the selected Exam is random, defined an array containing
        // the questions that have been part of the selected Student's exam
        $questions = [];
        if ($userExam->getExam()->getIsRandomExam()) {
            $givenAnswers = $userExam->getGivenAnswers();
            foreach ($givenAnswers as $answer) {
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

    /**
     * Display list of available (due) Exams in the Student-View
     * @return Response
     */
    public function listAvailableExams()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()
            ->getRepository(Category::class)->findAll();

        // Select the not yet taken userExams for activeUser from database
        $userExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array(
            'user' => $activeUser,
            'result' => null
        ));

        // Filter those userExams for the Student, that the user can actually do
        // (don't add Exams which are non-random and contain no questions or are
        // random but the owner does not have questions for the exam's category)
        $availableExams = [];
        foreach ($userExams as $userExam) {

            // Define and array containing all the categories for which the Owner of an Exam (and UserExam)
            // has Questions available
            $availableCategories = [];
            foreach ($userExam->getExam()->getOwner()->getQuestions() as $question) {
                array_push($availableCategories, $question->getCategory());
            }

            if (!$userExam->getExam()->getIsRandomExam() && sizeof($userExam->getExam()->getQuestions()) == 0) {
                break;
            } elseif (!in_array($userExam->getExam()->getCategory(), $availableCategories)) {
                break;
            } else {
                array_push($availableExams, $userExam);
            }
        }

        return $this->render('student/listAvailableExams.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories,
                'userExams' => $availableExams));
    }

    /**
     * Handle the taking of an exam of a student (Student-View)
     * @param Request $request
     * @param $userExamId
     * @return Response
     */
    public function takeExam(Request $request, $userExamId)
    {
        $activeUser = $this->getUser();

        $userExam = $this->getDoctrine()->getRepository(UserExam::class)->find($userExamId);

        $chosenQuestions = [];

        // Select a random number of questions with the corresponding category
        // if the taken Exam is a random one
        if ($userExam->getExam()->getIsRandomExam()) {
            $allQuestions = $this->getDoctrine()->getRepository(Question::class)->findBy(array('category' => $userExam->getExam()->getCategory(), 'owner' => $userExam->getExam()->getOwner()));

            $max = mt_rand(1, sizeOf($allQuestions));

            shuffle($allQuestions);

            $chosenQuestions = array_slice($allQuestions, 0, $max);
        }

        $entityManager = $this->getDoctrine()->getManager();

        // Transform the received JSON-String into a PHP object
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());

        if ($data != null) {
            $allCorrectAnswers = 0;
            $answerCounter = 0;

            // Evaluate the given answers
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

            // Create the relation between each given Answer and the userExam
            foreach ($userExam->getGivenAnswers() as $answer) {
                $answer->addUserExam($userExam);
                $entityManager->persist($answer);
            }

            // Calculate the result for the userExam
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

    /**
     * Display detailed result of a student in an Exam (Student-View)
     * @param $userExamId
     * @return Response
     */
    public function detailedExamResult($userExamId)
    {
        $activeUser = $this->getUser();

        $userExam = $this->getDoctrine()->getRepository(UserExam::class)->find($userExamId);

        // Define an array containing all Questions the Student answered during his Exam
        // if the Exam is a random one
        $questions = [];
        if ($userExam->getExam()->getIsRandomExam()) {
            $givenAnswers = $userExam->getGivenAnswers();
            foreach ($givenAnswers as $answer) {
                if (!in_array($answer->getQuestion(), $questions)) {
                    array_push($questions, $answer->getQuestion());
                }
            }
        }

        return $this->render('student/detailedExamResult.html.twig',
            array(
                'activeUser' => $activeUser,
                'userExam' => $userExam,
                'questions' => $questions
            ));
    }

    /**
     * Display list of the student's results in all assigned exams (Student-View)
     * @return Response
     */
    public function listStudentsResults()
    {
        $activeUser = $this->getUser();

        $categories = $this->getDoctrine()->getRepository(Category::class)->findAll();

        $userExams = $this->getDoctrine()->getRepository(UserExam::class)->findBy(array('user' => $activeUser));

        return $this->render('student/listResults.html.twig',
            array(
                'activeUser' => $activeUser,
                'categories' => $categories,
                'userExams' => $userExams
            ));
    }

}

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// var $ = require('jquery');

import axios from 'axios';

// Select all Delete-Buttons on the listExams page and add EventListeners to them
let deleteExamButton = document.querySelectorAll('.deleteExamButton');
deleteExamButton.forEach(button => button.addEventListener('click', deleteExamButtonClicked));

function deleteExamButtonClicked(event) {
    const examId = event.target.getAttribute('data-id');

    var choice = confirm(this.getAttribute('data-confirm'));

    if (choice) {
        axios.delete('/teacher/exam/delete/' + examId)
            .then(response => location.reload());
    }
}

// Add an EventListener to the SubmitButton on the AddStudentsToExam page (during Exam creation and editing)
let addStudentsToExamButton = document.querySelectorAll('.addStudentsToExamButton');
addStudentsToExamButton.forEach(button => button.addEventListener('click', addStudentsToExamButtonClicked));

function addStudentsToExamButtonClicked(event) {
    const examId = document.getElementById('exam').getAttribute('data-id');

    // Define array with all CheckBoxes on the page (one for each Student in the database)
    const allCheckBoxes = document.getElementsByClassName('selectStudentCheckBox');

    // Create an Array that contains the Students that have been checked by the activeUser/Teacher
    // and shall be assigned to the Exam
    const checkedCheckBoxes = [];
    if (allCheckBoxes.length > 0) {
        for (let i = 0; i < allCheckBoxes.length; i++) {
            if (allCheckBoxes[i].checked) {
                checkedCheckBoxes.push(parseInt(allCheckBoxes[i].getAttribute('data-id')));
            }

            // Warn if the activeUser/Teacher selected no student,
            // or call the Method to create a new Request
            if (allCheckBoxes.length - 1 === i) {
                if (checkedCheckBoxes.length == 0) {
                    event.preventDefault();
                    alert("Select at least one student");
                } else if (event.target.getAttribute('name') == 'create') {
                    sendCreateRequestToServer(examId, checkedCheckBoxes);
                } else if (event.target.getAttribute('name') == 'edit') {
                    sendEditRequestToServer(examId, checkedCheckBoxes);
                }
            }
        }
    }
}

function sendCreateRequestToServer(examId, assignedStudents) {
    axios.post('/teacher/exam/create/students/' + examId, {
        students: assignedStudents
    }).then(response => location.assign('/teacher/exam'))
}

function sendEditRequestToServer(examId, assignedStudents) {
    axios.post('/teacher/exam/edit/students/' + examId, {
        students: assignedStudents
    }).then(response => location.reload())
}

// Add an EventListener to the SubmitExamButton on the page where the Student takes an Exam
let submitExamButton = document.querySelectorAll('.submitExamButton');
submitExamButton.forEach(button => button.addEventListener('click', submitExamButtonClicked));

function submitExamButtonClicked(event) {
    var userExamId = event.target.getAttribute('data-id');

    var allCheckBoxes = document.getElementsByClassName('selectAnswerCheckBoxActive');

    // Define a multidimensional array containing each answers id
    // and the information whether the answer was chosen by the Student
    var givenAnswers = [];
    if (allCheckBoxes.length > 0) {
        for (let i = 0; i < allCheckBoxes.length; i++) {
            var answerId = parseInt(allCheckBoxes[i].getAttribute('data-id'));

            var isChecked = allCheckBoxes[i].checked;

            var answer = {'answerId': answerId, 'isChecked': isChecked};

            givenAnswers.push(answer);

            // Call the method to send the actual Request to the server
            // when the last position in the allCheckBoxes-Array is reached
            if (allCheckBoxes.length - 1 === i) {
                sendSubmitExamRequestToServer(userExamId, givenAnswers);
            }
        }
    }
}

function sendSubmitExamRequestToServer(userExamId, givenAnswers) {
    axios.post('/student/takeExam/' + userExamId, {
        givenAnswers: givenAnswers
    }).then(response => location.assign('/student/takeExam/result/' + userExamId))
}

// Select all Delete-Buttons on the listQuestions page and add EventListeners to them
let deleteQuestionButton = document.querySelectorAll('.deleteQuestionButton');
deleteQuestionButton.forEach(button => button.addEventListener('click', deleteQuestionButtonClicked));

function deleteQuestionButtonClicked(event) {
    const questionId = event.target.getAttribute('data-id');

    var choice = confirm(this.getAttribute('data-confirm'));

    if (choice) {
        axios.delete('/teacher/question/delete/' + questionId)
            .then(response => location.reload());
    }
}

// Select all Delete-Buttons on the EditQuestion-page and add EventListeners to them
let deleteExistingAnswerButton = document.querySelectorAll('.deleteExistingAnswerButton');
deleteExistingAnswerButton.forEach(button => button.addEventListener('click', deleteExistingAnswerButtonClicked));

function deleteExistingAnswerButtonClicked(event) {
    event.preventDefault();
    const answerId = event.target.getAttribute('data-id');

    var choice = confirm(this.getAttribute('data-confirm'));

    if (choice) {
        axios.delete('/teacher/question/answer/delete/' + answerId)
            .then(response => location.reload());
    }

}

var $collectionHolder;
var $addAnswerButton = $('<button type="button" class="editAnswersButton">Add Answer</button>');
var $newLinkLi = $('<li></li>').append($addAnswerButton);

jQuery(document).ready(function () {
    // Get the ul-element that holds the collection of answers
    $collectionHolder = $('ul.answers');

    // Call the addAnswerFormDeleteLink function for each li-element in the ul
    $collectionHolder.find('li').each(function () {
        addAnswerFormDeleteLink($(this));
    });

    // Add the "Add Answer" anchor and li to the ul with the answers
    $collectionHolder.append($newLinkLi);

    // Count the current form inputs and use that as the new
    // index when inserting a new item
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addAnswerButton.on('click', function (e) {
        // Add a new answer form
        addAnswerForm($collectionHolder, $newLinkLi);
    });
});

function addAnswerForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype
    // (which allows to add new answer forms to the template)
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;

    // increase the index by one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add Answer" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    addAnswerFormDeleteLink($newFormLi);
}

function addAnswerFormDeleteLink($answerFormLi) {
    var $removeFormButton = $('<button type="button" class="editAnswersButton">Delete Answer</button>');

    $answerFormLi.append($removeFormButton);

    $removeFormButton.on('click', function (e) {
        // remove the li for the tag form
        $answerFormLi.remove();
    });
}

var $category = $('#exam_category');
var $questions = $('#exam_questions');
var $name = $('#exam_name');

// Define what happens when a category gets selected
$category.change(function () {

    // Retrieve the corresponding form.
    var $form = $(this).closest('form');

    // Simulate form data, but only include the selected sport value.
    var data = {};
    data[$category.attr('name')] = $category.val();
    data[$questions.attr('name')] = $questions.val();
    data[$name.attr('name')] = $name.val();

    // Submit data via AJAX to the form's action path.
    $.ajax({
        url: $form.attr('action'),
        type: $form.attr('method'),
        data: data,
        success: function (html) {
            // Replace current question field ...
            $('#exam_questions').replaceWith(
                // ... with the returned one from the AJAX response.
                $(html).find('#exam_questions')
            );
        }
    });
});

/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/app.css');

// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
// var $ = require('jquery');

import axios from 'axios';

var counter = 0.1;

function deleteQuestionButtonClicked(event) {
    const questionId = event.target.getAttribute('data-id');

    // send the HTTP REQ
    axios.delete('/teacher/question/delete/' + questionId)
        .then(response => location.reload());
}

function sendUpdateQuestionRequestToServer(questionId, questionText, category, answers) {
    axios.post('/teacher/question/edit/' + questionId, {
        questionText: questionText,
        category: category,
        answers: answers
    }).then(response => location.reload())
}

function deleteExamButtonClicked(event) {
    const examId = event.target.getAttribute('data-id');

    axios.delete('/teacher/exam/delete/' + examId)
        .then(response => location.reload());
}


function addStudentsToExamButtonClicked(event) {
    const examId = document.getElementById('exam').getAttribute('data-id');
    const allCheckBoxes = document.getElementsByClassName('selectStudentCheckBox');
    const checkedCheckBoxes = [];

    if (allCheckBoxes.length > 0) {
        for (let i = 0; i < allCheckBoxes.length; i++) {
            if (allCheckBoxes[i].checked) {
                checkedCheckBoxes.push(parseInt(allCheckBoxes[i].getAttribute('data-id')));
            }

            if (allCheckBoxes.length - 1 === i) {
                console.log(checkedCheckBoxes);
                sendRequestToServer(examId, checkedCheckBoxes);
            }
        }
    }
}

function sendRequestToServer(examId, assignedStudents) {
    axios.post('/teacher/exam/create/students/' + examId, {
        students: assignedStudents
    }).then(response => location.assign('/teacher/exam'))
}

function submitExamButtonClicked(event) {
    const userExamId = event.target.getAttribute('data-id');
    const allCheckBoxes = document.getElementsByClassName('selectAnswerCheckBox');

    //ADD CODE to read out checkboxes
}

function deleteExistingAnswerButtonClicked(event) {
    event.preventDefault();
    const answerId = event.target.getAttribute('data-id');

    console.log(answerId);

    axios.delete('/teacher/question/answer/delete/' + answerId)
        .then(response => location.reload());

}

let deleteQuestionButton = document.querySelectorAll('.deleteQuestionButton');
deleteQuestionButton.forEach(button => button.addEventListener('click', deleteQuestionButtonClicked));

let deleteExamButton = document.querySelectorAll('.deleteExamButton');
deleteExamButton.forEach(button => button.addEventListener('click', deleteExamButtonClicked));

let addStudentsToExamButton = document.querySelectorAll('.addStudentsToExamButton');
addStudentsToExamButton.forEach(button => button.addEventListener('click', addStudentsToExamButtonClicked));

let submitExamButton = document.querySelectorAll('.submitExamButton');
submitExamButton.forEach(button => button.addEventListener('click', submitExamButtonClicked));

let deleteExistingAnswerButton = document.querySelectorAll('.deleteExistingAnswerButton');
deleteExistingAnswerButton.forEach(button => button.addEventListener('click', deleteExistingAnswerButtonClicked));


var $collectionHolder;
var $addAnswerButton = $('<button type="button" class="editAnswersButton">Add Answer</button>');
var $newLinkLi = $('<li></li>').append($addAnswerButton);

jQuery(document).ready(function() {
    // Get the ul that holds the collection of tags
    $collectionHolder = $('ul.answers');

    $collectionHolder.find('li').each(function() {
        addAnswerFormDeleteLink($(this));
    });

    // add the "Add Answer" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    $addAnswerButton.on('click', function(e) {
        // add a new tag form (see next code block)
        addAnswerForm($collectionHolder, $newLinkLi);
    });
});

function addAnswerForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;

    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add Answer" link li
    var $newFormLi = $('<li></li>').append(newForm);
    $newLinkLi.before($newFormLi);

    addAnswerFormDeleteLink($newFormLi);
}

function addAnswerFormDeleteLink($answerFormLi)
{
    var $removeFormButton = $('<button type="button" class="editAnswersButton">Delete Answer</button>');

    $answerFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        // remove the li for the tag form
        $answerFormLi.remove();
    });
}
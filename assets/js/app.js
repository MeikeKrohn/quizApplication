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

function createQuestionButtonClicked(event) {
    var questionText = document.getElementById("questionText").value;
    var categoryList = document.getElementById("categoryList");
    var category = parseInt(categoryList.options[categoryList.selectedIndex].value);
    var answerTextBoxes = document.getElementsByClassName("answerText");

    var answers = [];

    if (answerTextBoxes.length > 0) {
        for (let i = 0; i < answerTextBoxes.length; i++) {
            var id = answerTextBoxes[i].getAttribute('id');
            var isCorrect = document.getElementById("correctAnswer-" + id);

            if (isCorrect.checked) {
                var newAnswer = {answerText: answerTextBoxes[i].value, isCorrect: true};
            } else {
                var newAnswer = {answerText: answerTextBoxes[i].value, isCorrect: false};
            }
            answers.push(newAnswer);

            if (answerTextBoxes.length - 1 === i) {
                sendCreateQuestionRequestToServer(questionText, category, answers);
            }
        }
    }
}

function sendCreateQuestionRequestToServer(questionText, category, answers) {
    axios.post('/teacher/question/create', {
        questionText: questionText,
        category: category,
        answers: answers
    }).then(response => location.assign('/teacher/question'))
}

function updateQuestionButtonClicked(event) {
    var questionId = event.target.getAttribute("id");
    var questionText = document.getElementById("questionText").value;
    var categoryList = document.getElementById("categoryList");
    var category = parseInt(categoryList.options[categoryList.selectedIndex].value);
    var answerTextBoxes = document.getElementsByClassName("answerText");

    var answers = [];

    if (answerTextBoxes.length > 0) {
        for (let i = 0; i < answerTextBoxes.length; i++) {
            var id = answerTextBoxes[i].getAttribute('id');
            var isCorrect = document.getElementById('correctAnswer-' + id);

            if(isCorrect.checked) {
                var newAnswer = {answerId: id,answerText: answerTextBoxes[i].value, isCorrect: true};
            } else {
                var newAnswer = {answerId: id, answerText: answerTextBoxes[i].value, isCorrect: false};
            }
            answers.push(newAnswer);

            if (answerTextBoxes.length - 1 === i) {
                sendUpdateQuestionRequestToServer(questionId, questionText, category, answers);
            }
        }
    }
}

function sendUpdateQuestionRequestToServer(questionId, questionText, category, answers) {
    axios.post('/teacher/question/edit/' + questionId, {
        questionText: questionText,
        category: category,
        answers: answers
    }).then(response => location.reload())
}

function addAnswerToQuestionButtonClicked(event) {

    var html = "Answer text: <input type='text' class='answerText' id='" + counter + "'><br>Is correct: <input type='radio' class='isCorrect' id='correctAnswer-" + counter + "' name='isCorrect-" + counter + "' value='true' />yes<input type='radio' class='isCorrect' id='wrongAnswer-" + counter + "' name='isCorrect-" + counter + "' value='false' />no<br>"
    var element = document.getElementById("appendedAnswers");
    var answerForm = document.createElement("p");
    answerForm.setAttribute('id', 'answerForm-' + counter.toString());
    answerForm.innerHTML = html;

    element.appendChild(answerForm);

    var button = document.createElement("button");
    button.setAttribute('class', 'deleteAnswerButton');
    button.setAttribute('data-id', counter.toString());
    button.innerHTML = "Delete Answer";
    button.addEventListener('click', deleteNewAnswerButtonClicked);

    element.append(button);
    counter++;
}

function deleteNewAnswerButtonClicked(event) {
    var id = event.target.getAttribute('data-id');
    var answerForm = document.getElementById('answerForm-' + id);
    var button = event.target;

    answerForm.parentNode.removeChild(answerForm);
    button.parentNode.removeChild(button);

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

let deleteQuestionButton = document.querySelectorAll('.deleteQuestionButton');
deleteQuestionButton.forEach(button => button.addEventListener('click', deleteQuestionButtonClicked));

let createQuestionButton = document.querySelectorAll('.createQuestionButton');
createQuestionButton.forEach(button => button.addEventListener('click', createQuestionButtonClicked));

let updateQuestionButton = document.querySelectorAll('.updateQuestionButton');
updateQuestionButton.forEach(button => button.addEventListener('click', updateQuestionButtonClicked));

let addAnswerToQuestionButton = document.querySelectorAll('.addAnswerToQuestionButton');
addAnswerToQuestionButton.forEach(button => button.addEventListener('click', addAnswerToQuestionButtonClicked));

let deleteExamButton = document.querySelectorAll('.deleteExamButton');
deleteExamButton.forEach(button => button.addEventListener('click', deleteExamButtonClicked));

let addStudentsToExamButton = document.querySelectorAll('.addStudentsToExamButton');
addStudentsToExamButton.forEach(button => button.addEventListener('click', addStudentsToExamButtonClicked));

let submitExamButton = document.querySelectorAll('.submitExamButton');
submitExamButton.forEach(button => button.addEventListener('click', submitExamButtonClicked));
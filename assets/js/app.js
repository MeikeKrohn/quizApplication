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

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

function deleteQuestionButtonClicked(event)
{
    const questionId = event.target.getAttribute('data-id');

    // send the HTTP REQ
    axios.delete('/teacher/question/delete/' + questionId)
        .then(response => location.reload());
}

function deleteAnswerButtonClicked(event)
{
    const answerId = event.target.getAttribute('data-id');

    console.log(answerId);

    axios.delete('/teacher/question/answer/delete/' + answerId)
        .then(response => location.reload());

}

function deleteExamButtonClicked(event)
{
    const examId = event.target.getAttribute('data-id');

    axios.delete('/teacher/exam/delete/' + examId)
        .then(response => location.reload());
}

let deleteQuestionButton = document.querySelectorAll('.deleteQuestionButton');
deleteQuestionButton.forEach(button => button.addEventListener('click', deleteQuestionButtonClicked));

let deleteAnswerButton = document.querySelectorAll('.deleteAnswerButton');
deleteAnswerButton.forEach(button => button.addEventListener('click', deleteAnswerButtonClicked));

let deleteExamButton = document.querySelectorAll('.deleteExamButton');
deleteExamButton.forEach(button => button.addEventListener('click', deleteExamButtonClicked));

var $collectionHolder;
var $addAnswerButton = $('<button type="button" class="editAnswersButton">Add Answer</button>');
var $newLinkLi = $('<li></li>').append($addAnswerButton);

jQuery(document).ready(function() {
    // Get the ul that holds the collection of tags
    $collectionHolder = $('ul.answers');

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
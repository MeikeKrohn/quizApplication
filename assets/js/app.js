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

console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

var radioButtons = [1,2, 3];

function addAnswersClicked() {
    var current = radioButtons.length;

    var html = "<p><label>Answer " + current + ":</label>" +
        "<input type='text' placeholder='Type an answer here...' id='answer" + current + "' name='group" + current + "' />" +
        "<input type='radio' id='isCorrect" + current + "' value='correctAnswer' name='group" + current + "' checked='checked' />" +
        "<label for='isCorrect" + current + "'>Is Correct</label>" +
        "<input type='radio' id='isWrong" + current + "' value='wrongAnswer' name='group" + current + "' />" +
        "<label for='isWrong" + current + "'>Is Wrong</label></p>";

    var newDiv = document.createElement('div');

    newDiv.className = 'appendedAnswer';
    newDiv.innerHTML = html;
    document.getElementById("answers").appendChild(newDiv);

    radioButtons.push(current);
}

let addAnswersButton = document.querySelector('.addAnswersButton');
addAnswersButton.addEventListener('click', addAnswersClicked);
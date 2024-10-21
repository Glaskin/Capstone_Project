// Function to show a specific question and hide the others
function showQuestion(questionNumber) {
    // Hide all questions
    var questions = document.getElementsByClassName('question');
    for (var i = 0; i < questions.length; i++) {
        questions[i].style.display = 'none';
    }
    
    // Show the current question
    document.getElementById('question' + questionNumber).style.display = 'block';
}

<?php
session_start();

// Initialize quizzes if not set
if (!isset($_SESSION['quizzes'])) {
    $_SESSION['quizzes'] = [];
}

// Handle form submission to save a new quiz
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_title = $_POST['quiz_title'];
    $questions = $_POST['questions'];

    $_SESSION['quizzes'][$quiz_title] = $questions;

    echo "Quiz '$quiz_title' saved successfully!";
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Maker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/QuizMaker.css"/>
    
</head>
<body>
<nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
    <div class="container-fluid">
        <a class="navbar-brand navbar-brand-logo" href="FacultyDashboard.php">
            <img src="Images/abeLogo.png" alt="PAES Logo" height="40">
            <span>PAES - Faculty</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="DashboardTeacher.php" class="nav-link active">Home</a>
                </li>
                <li class="nav-item">
                    <a href="Profile.php" class="nav-link">Profile</a>
                </li>
                <li class="nav-item">
                    <a href="LandingPage.php" class="nav-link">Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1>Quiz Maker</h1>
    <form method="POST">
        <label for="quiz_title">Quiz Title:</label>
        <input type="text" id="quiz_title" name="quiz_title" required>
            
        <div id="questions"></div>

        <button type="button" class="add-question" onclick="addQuestion()">Add Question</button>
        <button type="submit">Save Quiz</button>
    </form>
</div>


<footer class="bg-warning text-center py-3 mt-5">
    <p>&copy; 2024 LMS. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
        let questionCount = 0;

        function addQuestion() {
            questionCount++;
            const questionDiv = document.createElement('div');
            questionDiv.className = 'question';
            questionDiv.innerHTML = `
                <label>Question ${questionCount}:</label>
                <input type="text" name="questions[${questionCount}][text]" placeholder="Enter question text" required>
                
                <label>Type:</label>
                <select name="questions[${questionCount}][type]" onchange="changeType(this, ${questionCount})">
                    <option value="multiple">Multiple Choice</option>
                    <option value="truefalse">True/False</option>
                </select>
                
                <div id="options-${questionCount}">
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 1" required>
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 2" required>
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 3">
                    <input type="text" name="questions[${questionCount}][options][]" placeholder="Option 4">
                </div>
                
                <label>Correct Answer:</label>
                <input type="text" name="questions[${questionCount}][answer]" placeholder="Enter correct answer" required>
                <hr>
            `;
            document.getElementById('questions').appendChild(questionDiv);
        }

        function changeType(select, id) {
            const optionsDiv = document.getElementById(`options-${id}`);
            if (select.value === 'truefalse') {
                optionsDiv.innerHTML = `
                    <input type="text" value="True" readonly>
                    <input type="text" value="False" readonly>
                `;
            } else {
                optionsDiv.innerHTML = `
                    <input type="text" name="questions[${id}][options][]" placeholder="Option 1" required>
                    <input type="text" name="questions[${id}][options][]" placeholder="Option 2" required>
                    <input type="text" name="questions[${id}][options][]" placeholder="Option 3">
                    <input type="text" name="questions[${id}][options][]" placeholder="Option 4">
                `;
            }
        }
    </script>
</body>
</html>

<?php
session_start();
$host = 'localhost';
$dbname = 'lms_database';
$username = 'root';
$password = 'pass';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch the quiz ID and module ID from the URL
$quiz_id = isset($_GET['quiz_id']) ? intval($_GET['quiz_id']) : 0;
$module_id = isset($_GET['module_id']) ? intval($_GET['module_id']) : 0; // Get module ID

// Initialize variables
$quizTitle = '';
$questions = [];
$score = null;
$submittedAnswers = [];
$correctAnswers = [];

// Fetch quiz title and questions
if ($quiz_id > 0) {
    // Fetch quiz title
    $quizQuery = $pdo->prepare("SELECT title FROM Quizzes WHERE quiz_id = :quiz_id");
    $quizQuery->execute(['quiz_id' => $quiz_id]);
    $quizTitle = $quizQuery->fetchColumn();

    // Fetch questions for the quiz
    $questionsQuery = $pdo->prepare("SELECT question_id, question_text FROM Questions WHERE quiz_id = :quiz_id");
    $questionsQuery->execute(['quiz_id' => $quiz_id]);
    $questions = $questionsQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedAnswers = $_POST['answers'];
    $score = 0;

    foreach ($submittedAnswers as $question_id => $answer) {
        // Check if the submitted answer is correct
        $answerQuery = $pdo->prepare("SELECT is_correct FROM Answers WHERE question_id = :question_id AND answer_text = :answer");
        $answerQuery->execute(['question_id' => $question_id, 'answer' => $answer]);
        $is_correct = $answerQuery->fetchColumn();

        if ($is_correct) {
            $score++;
        }
    }

    // Fetch correct answers for highlighting after submission
    if ($quiz_id > 0) {
        $correctAnswersQuery = $pdo->prepare("SELECT question_id, answer_text FROM Answers WHERE is_correct = TRUE AND question_id IN (SELECT question_id FROM Questions WHERE quiz_id = :quiz_id)");
        $correctAnswersQuery->execute(['quiz_id' => $quiz_id]);
        $correctAnswers = $correctAnswersQuery->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/Dashboard.css" />
    <link rel="stylesheet" href="css/QuizMaker.css" />
</head>
<body>
<!-- Navbar -->
<nav class="navbar fixed-top navbar-expand-sm navbar-light bg-warning">
    <div class="container-fluid">
        <a class="navbar-brand navbar-brand-logo" href="Dashboard.php">
            <span>PAES REVIEW</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a href="Dashboard.php" class="nav-link active">Home</a>
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


<!-- Main Content -->
<div class="content" style="margin-bottom: 100px;">
<div class="container mt-5">
<div class="d-flex justify-content-between align-items-center">
        <h1><?php echo htmlspecialchars($quizTitle); ?></h1>
        <a href="Module.php?module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Back to Module</a> <!-- Back to specific module -->
    </div>
    <?php if ($score !== null): ?>
    <h3>Your Score: <?php echo $score; ?> / <?php echo count($questions); ?></h3>
    <a href="Quiz.php?quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>" class="btn btn-secondary">Retake Quiz</a>
    
    <h4>Results:</h4>
    <ul style="margin: 10px">
        <?php foreach ($questions as $question): ?>
            <li>
                <strong><?php echo ($question['question_text']); ?></strong><br>
                <?php 
                // Get the user's answer
                $userAnswer = isset($submittedAnswers[$question['question_id']]) ? $submittedAnswers[$question['question_id']] : 'No answer submitted';
                
                // Display user's answer
                echo "<span>Your Answer: " . htmlspecialchars($userAnswer) . "</span><br>";
                
                // Check if there is a correct answer
                if (isset($correctAnswers[$question['question_id']])): ?>
                    <span class="text-success">Correct Answer: <?php echo htmlspecialchars($correctAnswers[$question['question_id']]); ?></span>
                <?php else: ?>
                    <span class="text-danger">No Correct Answer Found</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else: ?>
    <form action="Quiz.php?quiz_id=<?php echo $quiz_id; ?>&module_id=<?php echo $module_id; ?>" method="POST">
        <?php foreach ($questions as $question): ?>
            <div class="mb-3">
                <label><strong><?php echo($question['question_text']); ?></strong></label><br>
                <?php
                // Fetch answers for the current question
                $answersQuery = $pdo->prepare("SELECT answer_text FROM Answers WHERE question_id = :question_id");
                $answersQuery->execute(['question_id' => $question['question_id']]);
                $answers = $answersQuery->fetchAll(PDO::FETCH_ASSOC);
                foreach ($answers as $answer): ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="answers[<?php echo $question['question_id']; ?>]" value="<?php echo htmlspecialchars($answer['answer_text']); ?>" required>
                        <label class="form-check-label">
                            <?php echo htmlspecialchars($answer['answer_text']); ?>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn btn-primary">Submit Answers</button>
    </form>
<?php endif; ?>
</div>
</div>

<!-- Footer -->
<footer class="bg-warning text-center py-3 mt-5">
    <p>&copy; 2024 PAES Review.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>
</html>


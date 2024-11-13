<?php
// Start session to keep track of quiz progress and answers
session_start();

// Initialize quiz questions and answers if not already set
if (!isset($_SESSION['quiz'])) {
    $_SESSION['quiz'] = [
        'questions' => [
            1 => [
                'question' => 'What is the capital of France?',
                'options' => ['Paris', 'London', 'Berlin', 'Madrid'],
                'answer' => 'Paris'
            ],
            2 => [
                'question' => 'Which is the largest planet in our solar system?',
                'options' => ['Jupiter', 'Mars', 'Earth', 'Venus'],
                'answer' => 'Jupiter'
            ],
            3 => [
                'question' => 'What is the boiling point of water?',
                'options' => ['100°C', '50°C', '0°C', '200°C'],
                'answer' => '100°C'
            ]
        ],
        'currentQuestion' => 1,
        'userAnswers' => []
    ];
}

// Handle form submissions to navigate between questions and submit answers
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['next'])) {
        $_SESSION['quiz']['userAnswers'][$_SESSION['quiz']['currentQuestion']] = $_POST['answer'] ?? '';
        $_SESSION['quiz']['currentQuestion']++;
    } elseif (isset($_POST['prev'])) {
        $_SESSION['quiz']['userAnswers'][$_SESSION['quiz']['currentQuestion']] = $_POST['answer'] ?? '';
        $_SESSION['quiz']['currentQuestion']--;
    } elseif (isset($_POST['submit'])) {
        $_SESSION['quiz']['userAnswers'][$_SESSION['quiz']['currentQuestion']] = $_POST['answer'] ?? '';
        header('Location: QuizResult.php');
        exit;
    }

    // Ensure the current question is within bounds
    $totalQuestions = count($_SESSION['quiz']['questions']);
    $_SESSION['quiz']['currentQuestion'] = max(1, min($_SESSION['quiz']['currentQuestion'], $totalQuestions));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Quiz Module</title>
    <link rel="stylesheet" href="Module.css">
    <link rel="stylesheet" href="Qmodule.css">
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

<div class="container mt-5 pt-5">
    <div class="Qmodule">
        <h2>Simple Quiz</h2>
        <form method="POST" action="QuizModule.php">
            <?php
            $currentQuestion = $_SESSION['quiz']['currentQuestion'];
            $questionData = $_SESSION['quiz']['questions'][$currentQuestion];
            ?>

            <!-- Display Current Question -->
            <div class="question">
                <p><?php echo $currentQuestion . '. ' . htmlspecialchars($questionData['question']); ?></p>
                <div class="answers">
                    <?php foreach ($questionData['options'] as $option): ?>
                        <label>
                            <input type="radio" name="answer" value="<?php echo htmlspecialchars($option); ?>"
                                <?php echo (isset($_SESSION['quiz']['userAnswers'][$currentQuestion]) && $_SESSION['quiz']['userAnswers'][$currentQuestion] == $option) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($option); ?>
                        </label><br>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="mt-4">
                <?php if ($currentQuestion > 1): ?>
                    <button type="submit" name="prev" class="btn btn-secondary">Previous</button>
                <?php endif; ?>
                <?php if ($currentQuestion < count($_SESSION['quiz']['questions'])): ?>
                    <button type="submit" name="next" class="btn btn-primary">Next</button>
                <?php else: ?>
                    <button type="submit" name="submit" class="btn btn-success">Submit Quiz</button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<footer class="bg-warning text-center py-3 mt-5">
    <p>&copy; 2024 LMS. All rights reserved.</p>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
